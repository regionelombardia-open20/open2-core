<?php

namespace open20\amos\core\models\base;

use open20\amos\core\record\Record;
use Yii;

/**
 * This is the base-model class for table "models_classname".
 *
 * @property integer $id
 * @property string $classname
 * @property string $module
 * @property string $label
 * @property string $description
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 */
class  ModelsClassname extends Record
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'models_classname';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['classname', 'module', 'label', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'classname' => 'Classname',
            'module' => 'Module',
            'label' => 'Label',
            'description' => 'Description',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }
}
