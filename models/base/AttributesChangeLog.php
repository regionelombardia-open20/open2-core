<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\models\base
 * @category   CategoryName
 */

namespace open20\amos\core\models\base;

use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;

/**
 * Class AttributesChangeLog
 *
 * This is the base-model class for table "attributes_change_log".
 *
 * @property integer $id
 * @property string $model_classname
 * @property string $model_id
 * @property string $model_attribute
 * @property string $user_activity_log_id
 * @property string $old_value
 * @property string $new_value
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @package open20\amos\core\models\base
 */
abstract class AttributesChangeLog extends Record
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'attributes_change_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_classname', 'model_attribute', 'old_value', 'new_value'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['user_activity_log_id', 'model_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => BaseAmosModule::t('amoscore', 'ID'),
            'model_classname' => BaseAmosModule::t('amoscore', 'Model Classname'),
            'model_id' => BaseAmosModule::t('amoscore', 'Model ID'),
            'model_attribute' => BaseAmosModule::t('amoscore', 'Model Attribute'),
            'old_value' => BaseAmosModule::t('amoscore', 'Old Value'),
            'new_value' => BaseAmosModule::t('amoscore', 'New Value'),
            'created_at' => BaseAmosModule::t('amoscore', 'Created At'),
            'updated_at' => BaseAmosModule::t('amoscore', 'Updated At'),
            'deleted_at' => BaseAmosModule::t('amoscore', 'Deleted At'),
            'created_by' => BaseAmosModule::t('amoscore', 'Created By'),
            'updated_by' => BaseAmosModule::t('amoscore', 'Updated By'),
            'deleted_by' => BaseAmosModule::t('amoscore', 'Deleted By'),
        ];
    }
}
