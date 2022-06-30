<?php

namespace open20\amos\core\models\base;

use Yii;
use open20\amos\core\module\BaseAmosModule;

/**
 * This is the base-model class for table "content_shared".
 *
 * @property integer $id
 * @property integer $models_classname_id
 * @property integer $content_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 *
 * @property \open20\amos\core\models\ModelsClassname $modelsClassname
 */
class  ContentShared extends \open20\amos\core\record\Record
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content_shared';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['models_classname_id', 'content_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['models_classname_id'], 'exist', 'skipOnError' => true, 'targetClass' => ModelsClassname::className(), 'targetAttribute' => ['models_classname_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => BaseAmosModule::t('app', 'ID'),
            'models_classname_id' => BaseAmosModule::t('app', 'Models Classname ID'),
            'content_id' => BaseAmosModule::t('app', 'Content ID'),
            'created_at' => BaseAmosModule::t('app', 'Created At'),
            'created_by' => BaseAmosModule::t('app', 'Created By'),
            'updated_at' => BaseAmosModule::t('app', 'Updated At'),
            'updated_by' => BaseAmosModule::t('app', 'Updated By'),
            'deleted_at' => BaseAmosModule::t('app', 'Deleted At'),
            'deleted_by' => BaseAmosModule::t('app', 'Deleted By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModelsClassname()
    {
        return $this->hasOne(\open20\amos\core\models\ModelsClassname::className(), ['id' => 'models_classname_id']);
    }
}
