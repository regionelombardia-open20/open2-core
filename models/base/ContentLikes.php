<?php

namespace open20\amos\core\models\base;

use Yii;
use open20\amos\core\module\BaseAmosModule;

/**
 * This is the base-model class for table "content_likes".
 *
 * @property integer $id
 * @property integer $models_classname_id
 * @property integer $content_id
 * @property integer $user_id
 * @property string $user_ip
 * @property integer $likes
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @property \open20\amos\core\models\base\ModelsClassname $modelsClassname
 * @property \open20\amos\core\models\base\User $user
 */
class ContentLikes extends \open20\amos\core\record\Record {

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return 'content_likes';
  }

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['models_classname_id', 'content_id', 'user_id', 'likes', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
     [['created_at', 'updated_at', 'deleted_at'], 'safe'],
      [['user_ip'], 'string', 'max' => 39],
      [['models_classname_id'], 'exist', 'skipOnError' => true, 'targetClass' => ModelsClassname::className(), 'targetAttribute' => ['models_classname_id' => 'id']],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \open20\amos\core\user\User::className(), 'targetAttribute' => ['user_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => BaseAmosModule::t('app', 'ID'),
      'models_classname_id' => BaseAmosModule::t('app', 'Models Classname ID'),
      'content_id' => BaseAmosModule::t('app', 'Content ID'),
      'user_id' => BaseAmosModule::t('app', 'User ID'),
      'user_ip' => BaseAmosModule::t('app', 'User Ip'),
      'likes' => BaseAmosModule::t('app', 'Likes'),
      'created_at' => BaseAmosModule::t('app', 'Created At'),
      'updated_at' => BaseAmosModule::t('app', 'Updated At'),
      'deleted_at' => BaseAmosModule::t('app', 'Deleted At'),
      'created_by' => BaseAmosModule::t('app', 'Created By'),
      'updated_by' => BaseAmosModule::t('app', 'Updated By'),
      'deleted_by' => BaseAmosModule::t('app', 'Deleted By'),
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getModelsClassname() {
    return $this->hasOne(\open20\amos\core\models\ModelsClassname::className(), ['id' => 'models_classname_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser() {
    return $this->hasOne(\open20\amos\core\user\User::className(), ['id' => 'user_id']);
  }

}
