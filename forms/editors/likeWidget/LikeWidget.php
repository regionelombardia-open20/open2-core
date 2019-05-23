<?php

namespace lispa\amos\core\forms\editors\likeWidget;

use lispa\amos\core\exceptions\AmosException;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\interfaces\ContentModelInterface;

use Yii;
use yii\base\Exception;
use yii\bootstrap\Modal;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;


class LikeWidget extends Widget {
  
  const
    MODE_NORMAL = 'normal'
  ;

  public
    $model,
    $wrapperTag = 'div',
    $wrapperOptions = ['class' => 'container-like'],
    $linkWrapperTag = 'div',
    $linkWrapperOptions = ['class' => 'like-wrap-button'],
    $enableModalLike = true,
    $mode = self::MODE_NORMAL
  ;

  /**
   * 
   */
  public function init() {
    parent::init();
  }

  /**
   *
   */
  public function run() {
    $uid = Yii::$app->user->id;
    $cid = $this->model->id;
    $mid = \lispa\amos\core\models\ModelsClassname::find()
      ->where(['classname' => $this->model->className()])
      ->one()->id;

    $tot = \lispa\amos\core\models\ContentLikes::getLikesToCounter($uid, $cid, $mid);
    $lme = \lispa\amos\core\models\ContentLikes::getLikeMe($uid, $cid, $mid);
    
    return $this->render(
     '_like', 
     [
       'uid' => $uid,
       'cid' => $cid,
       'mid' => $mid,
       'tot' => $tot,
       'lme' => ($lme == 1) ? 'likeme' : 'notlikeme'
     ]);
  }
  
}