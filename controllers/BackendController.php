<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\controllers
 * @category   CategoryName
 */

namespace lispa\amos\core\controllers;

use lispa\amos\admin\utility\UserProfileUtility;
use lispa\amos\core\models\ContentShared;
use lispa\amos\core\models\ModelsClassname;
use lispa\amos\core\models\ContentLikes;
use lispa\amos\admin\models\UserProfile;
use lispa\amos\core\user\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class BackendController
 * @package lispa\amos\core\controllers
 */
abstract class BackendController extends AmosController {

  public $layout = 'main';

  /**
   * @inheritdoc
   */
  public function behaviors() {
    $behaviors = ArrayHelper::merge(
        parent::behaviors(),
        [
          'access' => [
            'class' => AccessControl::className(),
            'rules' => [
              [
                'actions' => ['public'],
                'allow' => true,
              ],
              [
                'actions' => ['like', 'like-to', 'get-counter', 'share-ajax'],
                'allow' => true,
                'roles' => ['@']  // for all log in users
              ],
            ],
          ],
          'verbs' => [
            'class' => VerbFilter::className(),
            'actions' => [
              'public' => ['post', 'get']
            ]
          ]
        ]
    );

    return $behaviors;
  }

  /**
   * @param null $layout
   * @return bool
   */
  public function setUpLayout($layout = null) {
    if ($layout === false) {
      $this->layout = false;
      return true;
    }

    $this->layout = (!empty($layout)) ? $layout : $this->layout;
    $module = \Yii::$app->getModule('layout');
    if (empty($module)) {
      if (strpos($this->layout, '@') === false) {
        $this->layout = '@vendor/lispa/amos-core/views/layouts/' . (!empty($layout) ? $layout : $this->layout);
      }

      return true;
    }

    return true;
  }

  /**
   * If not present, add flash message to session
   *
   * @param string $key - 'danger', 'warning', 'success'
   * @param string $message
   */
  public function addFlash($key, $message) {
    $flashes = Yii::$app->session->getFlash($key);
    if (!Yii::$app->session->hasFlash($key) || !in_array($message, $flashes)) {
      Yii::$app->getSession()->addFlash($key, $message);
    }
  }

  /**
   * @param $id
   * @return mixed
   * @throws NotFoundHttpException
   */
  public function actionPublic($id) {
    $isShared = $this->isContentShared($id);
    if ($isShared) {
      return $this->actionView($id);
    }

    throw new NotFoundHttpException(\Yii::t('app', 'Page not found'));
  }

  /**
   * @param $id
   * @return bool
   */
  public function isContentShared($id) {
    $obj = $this->getModelObj();
    if ($obj) {
      $classname = get_class($obj);
      $contentShared = ContentShared::find()
          ->innerJoinWith('modelsClassname')
          ->andWhere(['classname' => $classname, 'content_id' => $id])->one();

      if ($contentShared) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param $id
   * @return bool
   */
  public function actionShareAjax($id) {
    $obj = $this->getModelObj();
    \Yii::$app->response->format = Response::FORMAT_JSON;

    if ($obj) {
      $classname = get_class($obj);
      $contentShared = ContentShared::find()
          ->innerJoinWith('modelsClassname')
          ->andWhere(['classname' => $classname, 'content_id' => $id])->one();

      if (empty($contentShared)) {
        $modelClassname = ModelsClassname::findOne(['classname' => $classname]);
        if ($modelClassname) {
          $contentShared = new ContentShared();
          $contentShared->models_classname_id = $modelClassname->id;
          $contentShared->content_id = $id;
          $contentShared->save();
          return true;
        }
      } else {
        return true;
      }
    }
    return false;
  }

  /**
   *
   * @param type $uid user_id
   * @param type $cid content_id
   * @param type $mid model id from models_classname: News, Documenti, Discussione
   */
  public function actionLike($uid = null, $cid = null, $mid = null) {
    \Yii::$app->response->format = Response::FORMAT_JSON;

    $obj = $this->getModelObj();
    if (Yii::$app->request->isAjax) {
      $classname = get_class($obj);
      $rs = ContentLikes::find()
        ->andWhere(
          [
            'content_id' => $cid,
            'models_classname_id' => $mid,
            'user_id' => $uid
          ]
        )
        ->one();

      if (empty($rs)) {
        $rs = new ContentLikes();
        $rs->user_id = $uid;
        $rs->content_id = $cid;
        $rs->models_classname_id = $mid;
        $rs->user_ip = \Yii::$app->request->getUserIP();
      }
      $rs->likes = -1 * ($rs->likes - 1);
      $rs->save();
    }

    $out = [
      'tot' => $this->getCounter($uid, $cid, $mid),
      'class' => ($rs->likes == 1) ? 'likeme' : 'notlikeme'
    ];

    return $out;
  }

  /**
   *
   * @param type $uid
   * @param type $cid
   * @param type $mid
   * @return boolean
   */
  public function actionLikeTo($uid = null, $cid = null, $mid = null) {
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $obj = $this->getModelObj();
    if (($cid != null) && ($mid != null)) {
      $classname = get_class($obj);
      $rs = ContentLikes::find()
        ->andWhere(
          [
            'content_id' => $cid,
            'models_classname_id' => $mid,
            'likes' => 1
          ]
        )
        ->all();

      $users = [];
      foreach ($rs as $k => $v) {
        $users[] = $v['user_id'];
      }

      $query = UserProfile::find()->innerJoinWith(['user']);
      $query->andWhere(
        [
          UserProfile::tableName() . '.id' => $users,
          UserProfile::tableName() . '.attivo' => 1,
        ]
      );

      $dataProvider = new ActiveDataProvider(
        [
        'query' => $query
        ]
      );

      if (count($rs) == 0) {
        return false;
      }
    }

    // Return all get user contact INVITED and ACCEPTED
    $dpContacts = UserProfileUtility::getQueryContacts($uid);

    $rsContacts = $dpContacts->all();
    $userNetwork = [];
    foreach ($rsContacts as $k => $v) {
      $userNetwork[] = $v->id;
    }

    return $this->renderPartial(
      '@vendor/lispa/amos-core/forms/editors/likeWidget/views/_users',
      [
        'model' => $obj,
        'userId' => $uid,
        'userNetwork' => $userNetwork,
        'dataProvider' => $dataProvider
      ]
    );
  }

  /**
   *
   * @param type $cid
   * @param type $mid
   * @return type
   */
  public function getCounter($uid = null, $cid = null, $mid = null) {
    return ContentLikes::getLikesToCounter(null, $cid, $mid);
  }

  /**
   *
   * @param type $cid
   * @param type $mid
   * @return type
   */
  public function getLikeMe($uid = null, $cid = null, $mid = null) {
    return ContentLikes::getLikeMe($uid, $cid, $mid);
  }

}