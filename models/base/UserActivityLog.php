<?php

namespace open20\amos\core\models\base;

use open20\amos\core\user\User;
use Yii;
use open20\amos\core\module\BaseAmosModule;

/**
 * This is the base-model class for table "user_activity_log".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $type
 * @property string $name
 * @property string $description
 * @property integer $models_classname_id
 * @property integer $record_id
 * @property string $attribute_before
 * @property string $attribute_after
 * @property string $exacuted_at
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @property \open20\amos\core\models\ModelsClassname $modelsClassname
 * @property \open20\amos\admin\models\User $user
 */
class  UserActivityLog extends \open20\amos\core\record\Record
{
    public $isSearch = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_activity_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'models_classname_id', 'record_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['description', 'attribute_before', 'attribute_after'], 'string'],
            [['exacuted_at', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['type', 'name'], 'string', 'max' => 255],
            [['models_classname_id'], 'exist', 'skipOnError' => true, 'targetClass' => ModelsClassname::className(), 'targetAttribute' => ['models_classname_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => BaseAmosModule::t('amosadmin', 'ID'),
            'user_id' => BaseAmosModule::t('amosadmin', 'User'),
            'type' => BaseAmosModule::t('amosadmin', 'Type'),
            'name' => BaseAmosModule::t('amosadmin', 'Activity'),
            'description' => BaseAmosModule::t('amosadmin', 'Activity description'),
            'models_classname_id' => BaseAmosModule::t('amosadmin', 'Object'),
            'record_id' => BaseAmosModule::t('amosadmin', 'Record id'),
            'attribute_before' => BaseAmosModule::t('amosadmin', 'Attribute before'),
            'attribute_after' => BaseAmosModule::t('amosadmin', 'Attribute after'),
            'exacuted_at' => BaseAmosModule::t('amosadmin', 'Executed at'),
            'created_at' => BaseAmosModule::t('amosadmin', 'Created at'),
            'updated_at' => BaseAmosModule::t('amosadmin', 'Updated at'),
            'deleted_at' => BaseAmosModule::t('amosadmin', 'Deleted at'),
            'created_by' => BaseAmosModule::t('amosadmin', 'Created by'),
            'updated_by' => BaseAmosModule::t('amosadmin', 'Updated at'),
            'deleted_by' => BaseAmosModule::t('amosadmin', 'Deleted at'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModelsClassname()
    {
        return $this->hasOne(\open20\amos\core\models\ModelsClassname::className(), ['id' => 'models_classname_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributesChangeLogs()
    {
        return $this->hasMany(\open20\amos\core\models\AttributesChangeLog::className(), ['user_activity_log_id' => 'id']);
    }
}
