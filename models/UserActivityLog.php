<?php

namespace open20\amos\core\models;

use open20\amos\core\models\ModelsClassname;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "user_activity_log".
 */
class UserActivityLog extends \open20\amos\core\models\base\UserActivityLog
{
    public function representingColumn()
    {
        return [
//inserire il campo o i campi rappresentativi del modulo
        ];
    }

    public function attributeHints()
    {
        return [
        ];
    }

    /**
     * Returns the text hint for the specified attribute.
     * @param string $attribute the attribute name
     * @return string the attribute hint
     */
    public function getAttributeHint($attribute)
    {
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


    public static function getEditFields()
    {
        $labels = self::attributeLabels();

        return [
            [
                'slug' => 'user_id',
                'label' => $labels['user_id'],
                'type' => 'integer'
            ],
            [
                'slug' => 'type',
                'label' => $labels['type'],
                'type' => 'string'
            ],
            [
                'slug' => 'name',
                'label' => $labels['name'],
                'type' => 'string'
            ],
            [
                'slug' => 'description',
                'label' => $labels['description'],
                'type' => 'text'
            ],
            [
                'slug' => 'models_classname_id',
                'label' => $labels['models_classname_id'],
                'type' => 'integer'
            ],
            [
                'slug' => 'record_id',
                'label' => $labels['record_id'],
                'type' => 'integer'
            ],
            [
                'slug' => 'attribute_before',
                'label' => $labels['attribute_before'],
                'type' => 'text'
            ],
            [
                'slug' => 'attribute_after',
                'label' => $labels['attribute_after'],
                'type' => 'text'
            ],
            [
                'slug' => 'exacuted_at',
                'label' => $labels['exacuted_at'],
                'type' => 'datetime'
            ],
        ];
    }

    /**
     * @return string marker path
     */
    public function getIconMarker()
    {
        return null; //TODO
    }

    /**
     * If events are more than one, set 'array' => true in the calendarView in the index.
     * @return array events
     */
    public function getEvents()
    {
        return NULL; //TODO
    }

    /**
     * @return url event (calendar of activities)
     */
    public function getUrlEvent()
    {
        return NULL; //TODO e.g. Yii::$app->urlManager->createUrl([]);
    }

    /**
     * @return color event
     */
    public function getColorEvent()
    {
        return NULL; //TODO
    }

    /**
     * @return title event
     */
    public function getTitleEvent()
    {
        return NULL; //TODO
    }

    /**
     * @param $name
     * @param null $description
     * @param null $model
     * @param null $user_id
     * @param bool $save_attribute_changes
     */
    public static function registerLog($name,  $model = null, $type=null, $description = null, $user_id = null){
        $userIdRelatedToActivity = $user_id ? $user_id : \Yii::$app->user->id;

        $log = new UserActivityLog();
        $log->name = $name;
        $log->user_id = $userIdRelatedToActivity;
        $log->exacuted_at = date('Y-m-d H:i:s');
        $log->description = $description;
        $log->type = $type;


        if($model){
            $modelClass = get_class($model);
            $modelsClassname = ModelsClassname::find()->andWhere(['classname' => $modelClass])->one();
            if($modelsClassname){
                $log->models_classname_id = $modelsClassname->id;
                $log->record_id = $model->id;
            }
        }

        $log->save(false);
        return $log;
    }

}
