<?php

namespace open20\amos\core\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
* This is the model class for table "custom_order_fields".
*/
class CustomOrderFields extends \open20\amos\core\models\base\CustomOrderFields{
public function representingColumn()
{
return [
//inserire il campo o i campi rappresentativi del modulo
];
}

public function attributeHints(){
return [
];
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

public function rules()
{
return ArrayHelper::merge(parent::rules(), [
]);
}

public function attributeLabels()
{
return
ArrayHelper::merge(
parent::attributeLabels(),
[
]);
}


public function getEditFields() {
$labels = $this->attributeLabels();

return [
        [
        'slug' => 'modulo',
        'label' => $labels['modulo'],
        'type' => 'string'
        ],
                [
        'slug' => 'colonna',
        'label' => $labels['colonna'],
        'type' => 'string'
        ],
                [
        'slug' => 'visibile',
        'label' => $labels['visibile'],
        'type' => 'integer'
        ],
        ];
}

/**
* @return string marker path
*/
public function getIconMarker(){
return null; //TODO
}

/**
* If events are more than one, set 'array' => true in the calendarView in the index.
* @return array events
*/
public function getEvents() {
return NULL; //TODO
}

/**
* @return url event (calendar of activities)
*/
public function getUrlEvent() {
return NULL; //TODO e.g. Yii::$app->urlManager->createUrl([]);
}

/**
* @return color event 
*/
public function getColorEvent() {
return NULL; //TODO
}

/**
* @return title event
*/
public function getTitleEvent() {
return NULL; //TODO
}




}
