<?php

namespace open20\amos\core\models\base;

use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\user\User;
use Yii;

/**
 * This is the base-model class for table "tag_notifications".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $context_model_class_name
 * @property integer $context_model_id
 * @property boolean $read
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @property User;
 */
class TagNotification extends \open20\amos\core\record\Record
{
    public $isSearch = false;

  /**
   * @inheritdoc
   */
    public static function tableName()
    {
        return 'tag_notifications';
    }

  /**
   * @inheritdoc
   */
    public function rules()
    {
        return [
            [['user_id', 'context_model_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['context_model_class_name'], 'string'],
            [['read'], 'boolean'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

  /**
   * @inheritdoc
   */
    public function attributeLabels()
    {
        return [
            'id' => BaseAmosModule::t('amoscore', 'ID'),
            'user_id' => BaseAmosModule::t('amoscore', 'User ID'),
            'context_model_id' => BaseAmosModule::t('amoscore', 'Content Model ID'),
            'context_model_class_name' => BaseAmosModule::t('amoscore', 'Context Model Class Name'),
            'read' => BaseAmosModule::t('amoscore', 'Read'),
            'created_at' => BaseAmosModule::t('amoscore', 'Created at'),
            'updated_at' => BaseAmosModule::t('amoscore', 'Updated at'),
            'deleted_at' => BaseAmosModule::t('amoscore', 'Deleted at'),
            'created_by' => BaseAmosModule::t('amoscore', 'Created by'),
            'updated_by' => BaseAmosModule::t('amoscore', 'Updated by'),
            'deleted_by' => BaseAmosModule::t('amoscore', 'Deleted by'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
