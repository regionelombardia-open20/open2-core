<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\behaviors
 * @category   CategoryName
 */

namespace open20\amos\core\behaviors;

use Yii;

/**
 * Class BlameableBehavior
 * @package open20\amos\core\behaviors
 */
class BlameableBehavior extends \yii\behaviors\BlameableBehavior
{
    /**
     * @inheritdoc
     *
     * In case, when the [[value]] property is `null`, the value of `Yii::$app->user->id` will be used as the value.
     */
    protected function getValue($event)
    {
        if (!empty($this->attributes[$event->name]) && is_array($this->attributes[$event->name]) && in_array($this->createdByAttribute, $this->attributes[$event->name])) {
            if ($this->owner->{$this->createdByAttribute}) {
                return $this->owner->{$this->createdByAttribute};
            }
        }
        if ($this->value === null) {
            if(Yii::$app instanceof \yii\web\Application){
                $user = Yii::$app->get('user', false);
                return $user && !$user->isGuest ? $user->id : null;
            } elseif (Yii::$app instanceof \yii\console\Application) {
                return 0;
            }
        }

        return parent::getValue($event);
    }
}
