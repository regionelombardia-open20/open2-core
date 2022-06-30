<?php

namespace open20\amos\core\record;

use yii\base\Model;
use open20\amos\core\module\BaseAmosModule;

/**
 * Class CmsField
 *
 * This is the model class for fields to use in cms  *
 * @package open20\amos\core\record
 */
class CmsField extends Model {

    /**
     * @var string field's name (model's attribute)
     */
    public $name;

    /**
     * @var string field's label
     */
    public $label;

    /**
     * @var string  field's type. Possible values: "TEXT" , "DATE" , "IMAGE"
     */
    public $type;

    /**
     * @param string $name the field name
     * @param string $type the field type
     * @param string $category the translation category
     * @param string $label the translation message for the field label
     */
    public function __construct($name, $type, $category = null, $label = null) {

        $this->name = $name;
        if (!$category || !$label) {
            $this->label = null;
        } else {
            $this->label = $this->t($category, $label);
        }

        $this->type = $type;
    }

    public function rules() {
        return [
        ];
    }

    public function attributeLabels() {
        return [
        ];
    }

    public function representingColumn() {
        return [
                //inserire il campo o i campi rappresentativi del modulo
        ];
    }

    public function attributeHints() {
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

    public function __toString() {
        return "";
    }

    private function t($category, $message, $params = [], $lang = null) {
        $l = $lang ? $lang : \Yii::$app->language;

        if (isset(\Yii::$app->locales[$l])) {
            $language = \Yii::$app->locales[$l];
        } else {
            $language = \Yii::$app->language;
        }

        return BaseAmosModule::t($category, $message, $params, $language);
    }

}
