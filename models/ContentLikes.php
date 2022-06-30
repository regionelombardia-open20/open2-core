<?php

namespace open20\amos\core\models;

use open20\amos\admin\models\UserProfile;
use open20\amos\core\user\User;

use Yii;
use yii\helpers\ArrayHelper;
use open20\amos\admin\utility\UserProfileUtility;

/**
 * This is the model class for table "content_likes".
 */
class ContentLikes extends \open20\amos\core\models\base\ContentLikes {

    /**
     *
     * @param type $uid
     * @param type $cid
     * @param type $mid
     * @return type
     */
    public static function getLikesToCounter($uid = null, $cid = null, $mid = null) {
        $classname = \open20\amos\core\models\ContentLikes::className();

        $tblContentLikes = ContentLikes::tableName();
        $tblUserProfile = UserProfile::tableName();
        $tblUser = User::tableName();

        $query = $classname::find()
                ->join(
                        'INNER JOIN',
                        $tblUserProfile,
                        $tblUserProfile . '.user_id = ' . $tblContentLikes . '.user_id'
                )
                ->join(
                        'INNER JOIN',
                        $tblUser,
                        $tblUser . '.id = ' . $tblUserProfile . '.user_id'
                )
                ->andWhere([
                    $tblContentLikes . '.content_id' => $cid,
                    $tblContentLikes . '.models_classname_id' => $mid,
                    $tblContentLikes . '.likes' => 1,
                    $tblUserProfile . '.attivo' => UserProfile::STATUS_ACTIVE,
                    $tblUser . '.status' => User::STATUS_ACTIVE,
                    $tblUser . '.deleted_at' => null
                ])
                ->andWhere(['<>', $tblUserProfile . '.nome', UserProfileUtility::DELETED_ACCOUNT_NAME]);

        return count($query->all());
    }

  /**
   * 
   * @param type $uid
   * @param type $cid
   * @param type $mid
   */
  public static function getLikeMe($uid = null, $cid = null, $mid = null) {
    $classname =  \open20\amos\core\models\ContentLikes::className();
    
    $rs = $classname::find()
      ->andWhere([
        'content_id' => $cid,
        'models_classname_id' => $mid,
        'user_id' => $uid
      ])
      ->one();
    
    return (empty($rs)) ? 0 : $rs->likes;
  }
    
  /**
   * 
   * @return type
   */
  public function representingColumn() {
    return [
//inserire il campo o i campi rappresentativi del modulo
    ];
  }

  /**
   * 
   * @return type
   */
  public function attributeHints() {
    return [];
  }

  /**
   * Returns the text hint for the specified attribute.
   * @param string $attribute the attribute name
   * @return string the attribute hint
   */
  public function getAttributeHint($attribute) {
    $hints = $this->attributeHints();
    return isset($hints[$attribute]) ? $hints[$attribute] : null;
  }

  /**
   * 
   * @return type
   */
  public function rules() {
    return ArrayHelper::merge(parent::rules(), []);
  }

  /**
   * 
   * @return type
   */
  public function attributeLabels() {
    return ArrayHelper::merge(
      parent::attributeLabels(),
      []
    );
  }

  /**
   * 
   * @return type
   */
  public static function getEditFields() {
    $labels = self::attributeLabels();

    return [
      [
        'slug' => 'models_classname_id',
        'label' => $labels['models_classname_id'],
        'type' => 'integer'
      ],
      [
        'slug' => 'content_id',
        'label' => $labels['content_id'],
        'type' => 'integer'
      ],
      [
        'slug' => 'user_id',
        'label' => $labels['user_id'],
        'type' => 'integer'
      ],
      [
        'slug' => 'user_ip',
        'label' => $labels['user_ip'],
        'type' => 'string'
      ],
      [
        'slug' => 'likes',
        'label' => $labels['likes'],
        'type' => 'smallint'
      ],
    ];
  }

  /**
   * @return string marker path
   */
  public function getIconMarker() {
    return null; //TODO
  }

  /**
   * If events are more than one, set 'array' => true in the calendarView in the index.
   * @return array events
   */
  public function getEvents() {
    return null; //TODO
  }

  /**
   * @return url event (calendar of activities)
   */
  public function getUrlEvent() {
    return null; //TODO e.g. Yii::$app->urlManager->createUrl([]);
  }

  /**
   * @return color event 
   */
  public function getColorEvent() {
    return null; //TODO
  }

  /**
   * @return title event
   */
  public function getTitleEvent() {
    return null; //TODO
  }
  
}