<?php

namespace open20\amos\core\models\base;

use Yii;

/**
* This is the base-model class for table "custom_order_fields".
*
    * @property integer $id
    * @property string $modulo
    * @property string $colonna
    * @property integer $visibile
    * @property string $created_at
    * @property string $updated_at
    * @property string $deleted_at
    * @property integer $created_by
    * @property integer $updated_by
    * @property integer $deleted_by
*/
 class  CustomOrderFields extends \open20\amos\core\record\Record
{
    public $isSearch = false;

/**
* @inheritdoc
*/
public static function tableName()
{
return 'custom_order_fields';
}

/**
* @inheritdoc
*/
public function rules()
{
return [
            [['visibile', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['modulo', 'colonna'], 'string', 'max' => 255],
];
}

/**
* @inheritdoc
*/
public function attributeLabels()
{
return [
    'id' => Yii::t('amoscore', 'ID'),
    'modulo' => Yii::t('amoscore', 'Nome del modulo'),
    'colonna' => Yii::t('amoscore', 'Colonna'),
    'visibile' => Yii::t('amoscore', 'Visibile'),
    'created_at' => Yii::t('amoscore', 'Created at'),
    'updated_at' => Yii::t('amoscore', 'Updated at'),
    'deleted_at' => Yii::t('amoscore', 'Deleted at'),
    'created_by' => Yii::t('amoscore', 'Created by'),
    'updated_by' => Yii::t('amoscore', 'Updated by'),
    'deleted_by' => Yii::t('amoscore', 'Deleted by'),
];
}
}
