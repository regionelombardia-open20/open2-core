<?php

namespace lispa\amos\core\models\base;

use Yii;

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
 * @property \lispa\amos\core\models\base\ModelsClassname $modelsClassname
 * @property \lispa\amos\core\models\base\User $user
 */
class ContentLikes extends \lispa\amos\core\record\Record {

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
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \lispa\amos\core\user\User::className(), 'targetAttribute' => ['user_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels() {
    return [
      'id' => Yii::t('app', 'ID'),
      'models_classname_id' => Yii::t('app', 'Models Classname ID'),
      'content_id' => Yii::t('app', 'Content ID'),
      'user_id' => Yii::t('app', 'User ID'),
      'user_ip' => Yii::t('app', 'User Ip'),
      'likes' => Yii::t('app', 'Likes'),
      'created_at' => Yii::t('app', 'Created At'),
      'updated_at' => Yii::t('app', 'Updated At'),
      'deleted_at' => Yii::t('app', 'Deleted At'),
      'created_by' => Yii::t('app', 'Created By'),
      'updated_by' => Yii::t('app', 'Updated By'),
      'deleted_by' => Yii::t('app', 'Deleted By'),
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getModelsClassname() {
    return $this->hasOne(\lispa\amos\core\models\ModelsClassname::className(), ['id' => 'models_classname_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser() {
    return $this->hasOne(\lispa\amos\core\user\User::className(), ['id' => 'user_id']);
  }

}
