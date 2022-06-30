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

use open20\amos\admin\models\UserProfile;
use open20\amos\core\migration\libs\common\MigrationCommon;
use open20\amos\core\models\AttributesChangeLog;
use open20\amos\core\models\ModelsClassname;
use open20\amos\core\models\UserActivityLog;
use open20\amos\core\record\Record;
use open20\amos\core\user\User;
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
    CONST LOG_UPDATE_MODEL = 'log_update_model';

    /**
     * @var array $attributesToLog
     */
    public $attributesToLog = [];

    /**
     * @var array
     *
     * 'configUserActivityLog' => [
     *     'enabled' => true,
     *     'userAttribute' => 'user_id',
     *     'type' => 'update_profile',
     *     'name' => 'Aggiornamento profilo',
     *     'description' => 'Aggiornamento profilo'
     * ]
     **/
    public $configUserActivityLog = [
    ];

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

            $userActivityLog = $this->saveUserActivityLog($event, $owner);
            $oldAttributes = $owner->getOldAttributes();
            $this->attributesToLog = array_unique($this->attributesToLog);
            foreach ($this->attributesToLog as $attributeName) {
                $oldValue = ((($event->name != ActiveRecord::EVENT_AFTER_INSERT) && isset($oldAttributes[$attributeName])) ? $oldAttributes[$attributeName] : null);
                $newValue = $owner->{$attributeName};
                if ($oldValue != $newValue) {
                    $fieldsLog = new AttributesChangeLog();
                    $fieldsLog->model_classname = $owner->className();
                    $fieldsLog->model_id = $owner->getPrimaryKey();
                    $fieldsLog->model_attribute = $attributeName;
                    $fieldsLog->old_value = $oldValue;
                    $fieldsLog->new_value = $newValue;
                    if ($userActivityLog) {
                        $fieldsLog->user_activity_log_id = $userActivityLog->id;
                    }
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


    /**
     * @return UserActivityLog|null
     */
    public function saveUserActivityLog($event, $owner)
    {
        $log = null;
        if (!empty($this->configUserActivityLog['enabled'])) {
            if ($this->isChanged($event, $owner)) {
                $class = get_class($owner);
                $modelsClassname = ModelsClassname::find()->andWhere(['classname' => $class])->one();
                if ($modelsClassname) {
                    $name = \Yii::t('app', "Aggiornamento") . ' ' . $modelsClassname->label;
                    $type = self::LOG_UPDATE_MODEL;
                    $user_id = null;

                    /** PERSONALIZATION */
                    if (!empty($this->configUserActivityLog['userAttribute'])) {
                        $userAttr = $this->configUserActivityLog['userAttribute'];
                        $user_id = $owner->$userAttr;
                    }

                    if (!empty($this->configUserActivityLog['name'])) {
                        $name = $this->configUserActivityLog['name'];
                    }
                    if (!empty($this->configUserActivityLog['description'])) {
                        $description = $this->configUserActivityLog['description'];
                    }
                    if (!empty($this->configUserActivityLog['type'])) {
                        $type = $this->configUserActivityLog['type'];
                    }

                    $log = UserActivityLog::registerLog($name, $owner, $type, $description, $user_id);
                }
            }
        }
        return $log;
    }

    /**
     * @param $event
     * @param $owner
     * @return bool
     */
    public function isChanged($event, $owner)
    {
        $oldAttributes = $this->owner->getOldAttributes();
        foreach ($this->attributesToLog as $attributeName) {
            $oldValue = (($event->name == ActiveRecord::EVENT_AFTER_INSERT) ? null : $oldAttributes[$attributeName]);
            $newValue = $owner->{$attributeName};
            if ($oldValue != $newValue) {
                return true;
            }

        }
        return false;
    }

}
