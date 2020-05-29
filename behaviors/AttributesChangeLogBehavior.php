<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\behaviors
 * @category   CategoryName
 */

namespace open20\amos\core\behaviors;

use open20\amos\core\models\AttributesChangeLog;
use open20\amos\core\record\Record;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AttributesChangeLogBehavior
 * @package open20\amos\core\behaviors
 */
class AttributesChangeLogBehavior extends Behavior
{
    /**
     * @var array $attributesToLog
     */
    public $attributesToLog = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        $events = [
            ActiveRecord::EVENT_AFTER_INSERT => 'saveChangedValues',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'saveChangedValues',
        ];

        return $events;
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function saveChangedValues($event)
    {
        /** @var Record $owner */
        $owner = $event->sender->owner;
        if (!empty($owner)) {
            $oldAttributes = $owner->getOldAttributes();
            foreach ($this->attributesToLog as $attributeName) {
                $oldValue = (($event->name == ActiveRecord::EVENT_AFTER_INSERT) ? null : $oldAttributes[$attributeName]);
                $newValue = $owner->{$attributeName};
                if ($oldValue != $newValue) {
                    $fieldsLog = new AttributesChangeLog();
                    $fieldsLog->model_classname = $owner->className();
                    $fieldsLog->model_id = $owner->getPrimaryKey();
                    $fieldsLog->model_attribute = $attributeName;
                    $fieldsLog->old_value = $oldValue;
                    $fieldsLog->new_value = $newValue;
                    $fieldsLog->save(false);
                }
            }
        }
        return true;
    }

    /**
     * This method returns the update record list base query for the specified attribute and new value.
     * @param string $attribute
     * @param null|mixed $newValue
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    protected function getAttributeUpdateBaseQuery($attribute, $newValue = null)
    {
        /** @var Record $owner */
        $owner = $this->owner;
        /** @var ActiveQuery $query */
        $query = AttributesChangeLog::find();
        $query->andWhere([
            'model_classname' => $this->owner->className(),
            'model_id' => $owner->primaryKey,
            'model_attribute' => $attribute
        ]);
        if (!is_null($newValue)) {
            $query->andWhere(['new_value' => $newValue]);
        }
        return $query;
    }

    /**
     * This method returns the last update record list for the specified attribute and new value.
     * @param string $attribute
     * @param mixed $newValue
     * @return AttributesChangeLog[]
     * @throws \yii\base\InvalidConfigException
     */
    protected function getAttributeLastUpdate($attribute, $newValue = null)
    {
        $query = $this->getAttributeUpdateBaseQuery($attribute, $newValue);
        $logList = $query->orderBy(['created_at' => SORT_DESC])->all();
        return $logList;
    }

    /**
     * This method returns the last update time for the specified attribute and new value.
     * @param string $attribute
     * @param mixed $newValue
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getAttributeLastUpdateTime($attribute, $newValue)
    {
        $logList = $this->getAttributeLastUpdate($attribute, $newValue);
        if (count($logList) > 0) {
            $acl = reset($logList);
            return $acl->created_at;
        }
        return null;
    }

    /**
     * This method returns the last update user for the specified attribute and new value.
     * @param string $attribute
     * @param mixed $newValue
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getAttributeLastUpdateUser($attribute, $newValue)
    {
        $logList = $this->getAttributeLastUpdate($attribute, $newValue);
        if (count($logList) > 0) {
            $acl = reset($logList);
            return $acl->created_by;
        }
        return null;
    }

    /**
     * This method returns the first update record list for the specified attribute and new value.
     * @param string $attribute
     * @param mixed $newValue
     * @return AttributesChangeLog[]
     * @throws \yii\base\InvalidConfigException
     */
    protected function getAttributeFirstUpdate($attribute, $newValue = null)
    {
        $query = $this->getAttributeUpdateBaseQuery($attribute, $newValue);
        $logList = $query->orderBy(['created_at' => SORT_ASC])->all();
        return $logList;
    }

    /**
     * This method returns the first update time for the specified attribute and new value.
     * @param string $attribute
     * @param mixed $newValue
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getAttributeFirstUpdateTime($attribute, $newValue)
    {
        $logList = $this->getAttributeFirstUpdate($attribute, $newValue);
        if (count($logList) > 0) {
            $acl = reset($logList);
            return $acl->created_at;
        }
        return null;
    }

    /**
     * This method returns the first update user for the specified attribute and new value.
     * @param string $attribute
     * @param mixed $newValue
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getAttributeFirstUpdateUser($attribute, $newValue)
    {
        $logList = $this->getAttributeFirstUpdate($attribute, $newValue);
        if (count($logList) > 0) {
            $acl = reset($logList);
            return $acl->created_by;
        }
        return null;
    }

    /**
     * This method returns the attribute update history.
     * @param string $attribute
     * @return array|AttributesChangeLog[]
     * @throws \yii\base\InvalidConfigException
     */
    public function getAttributeUpdateHistory($attribute)
    {
        $logList = $this->getAttributeFirstUpdate($attribute);
        return ((is_array($logList) && count($logList) > 0) ? $logList : []);
    }
}
