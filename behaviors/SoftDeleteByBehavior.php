<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\behaviors
 * @category   CategoryName
 */

namespace lispa\amos\core\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;

/**
 * Class SoftDeleteByBehavior
 * @package lispa\amos\core\behaviors
 */
class SoftDeleteByBehavior extends Behavior
{
    public $deletedAtAttribute = 'deleted_at';
    public $deletedByAttribute = 'deleted_by';

    /**
     * @inheritdoc
     */
    public $timestamp;

    /**
     * @var bool If true, this behavior will process '$model->delete()' as a soft-delete. Thus, the
     *           only way to truely delete a record is to call '$model->forceDelete()'
     */
    public $safeMode = true;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [ActiveRecord::EVENT_BEFORE_DELETE => 'doDeleteTimestamp'];
    }

    /**
     * Set the attribute with the current timestamp to mark as deleted
     *
     * @param Event $event
     * @return bool|void
     */
    public function doDeleteTimestamp($event)
    {
        // do nothing if safeMode is disabled. this will result in a normal deletion
        if (!$this->safeMode) {
            return;
        }
        // remove and mark as invalid to prevent real deletion
        $this->remove();

        $event->isValid = false;
        return false;
    }

    /**
     * Remove (aka soft-delete) record
     */
    public function remove()
    {
        $userId = 1;
        if (Yii::$app instanceof \yii\web\Application) {
            $user = Yii::$app->get('user', false);
            $userId = $user && !$user->isGuest ? $user->id : null;
        } elseif (Yii::$app instanceof \yii\console\Application) {
            $userId = 1;
        }
        $timestamp = date('Y-m-d H:i:s');

        $deletedAtAttribute = $this->deletedAtAttribute;
        $deletedByAttribute = $this->deletedByAttribute;

        $this->owner->$deletedAtAttribute = $timestamp;
        $this->owner->$deletedByAttribute = $userId;

        // save record
        $this->owner->save(false, [$deletedAtAttribute, $deletedByAttribute]);
    }

    /**
     * Restore soft-deleted record
     */
    public function restore()
    {
        // mark attribute as null
        $attribute = $this->attribute;
        $this->owner->$attribute = null;
        // save record
        $this->owner->save(false, [$attribute]);
    }

    /**
     * Delete record from database regardless of the $safeMode attribute
     */
    public function forceDelete()
    {
        // store model so that we can detach the behavior and delete as normal
        $model = $this->owner;
        $this->detach();
        $model->delete();
    }
}