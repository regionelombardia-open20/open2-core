<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\record
 * @category   CategoryName
 */

namespace open20\amos\core\record;

use open20\amos\attachments\behaviors\FileBehavior;
use yii\base\DynamicModel;
use yii\base\ModelEvent;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;

class RecordDynamicModel extends DynamicModel
{
    /**
     * @event Event an event that is triggered when the record is initialized via [[init()]].
     */
    const EVENT_INIT = 'init';

    /**
     * @event Event an event that is triggered after the record is created and populated with query result.
     */
    const EVENT_AFTER_FIND = 'afterFind';

    /**
     * @event ModelEvent an event that is triggered before inserting a record.
     * You may set [[ModelEvent::isValid]] to be `false` to stop the insertion.
     */
    const EVENT_BEFORE_INSERT = 'beforeInsert';

    /**
     * @event AfterSaveEvent an event that is triggered after a record is inserted.
     */
    const EVENT_AFTER_INSERT = 'afterInsert';

    /**
     * @event ModelEvent an event that is triggered before updating a record.
     * You may set [[ModelEvent::isValid]] to be `false` to stop the update.
     */
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';

    /**
     * @event AfterSaveEvent an event that is triggered after a record is updated.
     */
    const EVENT_AFTER_UPDATE = 'afterUpdate';

    /**
     * @event ModelEvent an event that is triggered before deleting a record.
     * You may set [[ModelEvent::isValid]] to be `false` to stop the deletion.
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * @event Event an event that is triggered after a record is deleted.
     */
    const EVENT_AFTER_DELETE = 'afterDelete';

    /**
     * @event Event an event that is triggered after a record is refreshed.
     * @since 2.0.8
     */
    const EVENT_AFTER_REFRESH = 'afterRefresh';
    const UPDATE_INCREMENTAL  = 1;
    const UPDATE_DIFFERENTIAL = 2;
    const UPDATE_OVERRIDE     = 3;

    private $_attributeLabels;
    private $_attributeTypes;
    private $_attributeSubvalues;
    private $_attributeOptions;
    private $_attributeHints;
    private $_isNewRecord;

    private $_attributesObj = [];

    /**
     * Name of the table where will save the record
     * @var string $tableName
     */
    private $tableName;

    /**
     *
     * @var integer $typeUpdate
     */
    private $typeUpdate = 1;

    /**
     * The path of the drivers
     * @var string $pathDriver 
     */
    private $pathDriver = '@vendor/open20/amos-core/src/record/drivers';

    /**
     * Driver of the database, default is 'Mysql'. The driver are in drivers
     * @var string $driver
     */
    private $driver = 'open20\\amos\\core\\record\\drivers\\Mysql';

    /**
     *
     * @var string $db
     */
    private $db = 'db';

    /**
     *
     * @var array $source
     */
    private $source;

    /**
     * Default is 'excel'
     * @var string $typeSource
     */
    public $typeSource = 'excel';

    /**
     *
     * @var string $pathSource
     */
    private $pathSource;

    /**
     * 
     * @param string $path
     */
    public function setPathSource($path)
    {
        $this->pathSource = $path;
    }

    /**
     *
     * @return string
     */
    public function getPathSource()
    {
        return $this->pathSource;
    }

    /**
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     *
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * 
     * @return array
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     *
     * @param array $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     *
     * @return string
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     *
     * @param type $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * Return the class name of the driver
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Set the drive it will use
     * @param type $driver
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     *
     * @return boolean
     */
    public function save()
    {
        if (!$this->beforeSave($this->isNewRecord)) {
            return false;
        }
        $driver   = new $this->driver;
        $driver->setDb($this->db);
        $driver->setColumns(array_keys($this->unsafeAttributes));
        $driver->setData($this->unsafeAttributes);
        $driver->setTable($this->tableName);
        $result   = $driver->save();
        $this->setIsNewRecord(false);
        $this->id = $result['id'];
        $this->afterSave($this->isNewRecord, $this->unsafeAttributes);
    }

    /**
     * 
     * @param array $values
     * @param boolean $safeOnly
     */
    public function setAttributes($values, $safeOnly = true)
    {
        if (is_array($values)) {
            $attributes = array_flip($this->attributes());
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    $this->$name = $value;
                }
            }
        }
    }

    /**
     * 
     * @param string $name
     * @param string $label
     */
    public function defineLabel($name, $label)
    {
        $this->_attributeLabels[$name] = $label;
    }

    /**
     * 
     * @return string
     */
    public function attributeLabels()
    {
        return $this->_attributeLabels;
    }

    /**
     * 
     * @param string $name
     * @param string $type
     */
    public function defineType($name, $type)
    {
        $this->_attributeTypes[$name] = $type;
    }

    /**
     * 
     * @return string
     */
    public function attributeTypes()
    {
        return $this->_attributeTypes;
    }

    /**
     * 
     * @param string $name
     * @param string $type
     */
    public function defineSubvalue($name, $subvalue)
    {
        $this->_attributeSubvalues[$name] = $subvalue;
    }

    /**
     * 
     * @return array
     */
    public function attributeSubvalues()
    {
        return $this->_attributeSubvalues;
    }

    /**
     * 
     * @param string $name
     * @param string $css
     */
    public function defineHtmlCss($name, $css)
    {
        $this->_attributeOptions[$name]['options'] ['class'] = $css;
    }

    /**
     *
     * @return string
     */
    public function attributeHtmlCss($name)
    {
        return $this->_attributeOptions[$name]['options']['class'];
    }

    /**
     *
     * @param string $name
     * @param string $option
     * @param val $value
     */
    public function addOption($name, $option, $value)
    {
        $this->_attributeOptions[$name] [$option] = $value;
    }

    /**
     *
     * @return array
     */
    public function attributeOptions()
    {
        return $this->_attributeOptions;
    }

    /**
     *
     * @param string $name
     * @return array
     */
    public function getAttributeOptions($name)
    {
        $ret = [];
        try {
            $options = $this->_attributeOptions[$name];
            if (!empty($options) && is_array($options)) {
                $ret = $options;
            }
        } catch (\Exception $ex) {
            
        }
        return $ret;
    }


    /**
     *
     * @return array
     */
    public function attributeHints()
    {
        return $this->_attributeHints;
    }

    /**
     *
     * @param string $name
     * @param string $type
     */
    public function defineHint($name, $hint)
    {
        $this->_attributeHints[$name] = $hint;
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public function getAttributeHint($name)
    {
        return $this->_attributeHints[$name];
    }

    /**
     *
     * @return type
     */
    public function getIsNewRecord()
    {
        return $this->_isNewRecord;
    }

    /**
     *
     * @param type $isNewRecord
     */
    public function setIsNewRecord($isNewRecord)
    {
        $this->_isNewRecord = $isNewRecord;
    }

    public function __construct(array $attributes = array(), $config = array())
    {
        parent::__construct($attributes, $config);
        $this->_isNewRecord = true;
        $this->defineAttribute('id');
        $this->defineType('id', 'integer');
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(),
                [
                    'fileBehavior' => [
                        'class' => FileBehavior::className()
                    ]
        ]);
    }

    /**
     * This method is called at the beginning of inserting or updating a record.
     *
     * The default implementation will trigger an [[EVENT_BEFORE_INSERT]] event when `$insert` is `true`,
     * or an [[EVENT_BEFORE_UPDATE]] event if `$insert` is `false`.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeSave($insert)
     * {
     *     if (!parent::beforeSave($insert)) {
     *         return false;
     *     }
     *
     *     // ...custom code here...
     *     return true;
     * }
     * ```
     *
     * @param bool $insert whether this method called while inserting a record.
     * If `false`, it means the method is called while updating a record.
     * @return bool whether the insertion or updating should continue.
     * If `false`, the insertion or updating will be cancelled.
     */
    public function beforeSave($insert)
    {
        $event = new ModelEvent();
        $this->trigger($insert ? self::EVENT_BEFORE_INSERT : self::EVENT_BEFORE_UPDATE,
            $event);

        return $event->isValid;
    }

    /**
     * This method is called at the end of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_AFTER_INSERT]] event when `$insert` is `true`,
     * or an [[EVENT_AFTER_UPDATE]] event if `$insert` is `false`. The event class used is [[AfterSaveEvent]].
     * When overriding this method, make sure you call the parent implementation so that
     * the event is triggered.
     * @param bool $insert whether this method called while inserting a record.
     * If `false`, it means the method is called while updating a record.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     * You can use this parameter to take action based on the changes made for example send an email
     * when the password had changed or implement audit trail that tracks all the changes.
     * `$changedAttributes` gives you the old attribute values while the active record (`$this`) has
     * already the new, updated values.
     *
     * Note that no automatic type conversion performed by default. You may use
     * [[\yii\behaviors\AttributeTypecastBehavior]] to facilitate attribute typecasting.
     * See http://www.yiiframework.com/doc-2.0/guide-db-active-record.html#attributes-typecasting.
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->trigger($insert ? self::EVENT_AFTER_INSERT : self::EVENT_AFTER_UPDATE,
            new AfterSaveEvent([
                'changedAttributes' => $changedAttributes,
        ]));
    }

    /**
     * This method is invoked before deleting a record.
     *
     * The default implementation raises the [[EVENT_BEFORE_DELETE]] event.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeDelete()
     * {
     *     if (!parent::beforeDelete()) {
     *         return false;
     *     }
     *
     *     // ...custom code here...
     *     return true;
     * }
     * ```
     *
     * @return bool whether the record should be deleted. Defaults to `true`.
     */
    public function beforeDelete()
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_DELETE, $event);

        return $event->isValid;
    }

    /**
     * This method is invoked after deleting a record.
     * The default implementation raises the [[EVENT_AFTER_DELETE]] event.
     * You may override this method to do postprocessing after the record is deleted.
     * Make sure you call the parent implementation so that the event is raised properly.
     */
    public function afterDelete()
    {
        $this->trigger(self::EVENT_AFTER_DELETE);
    }

    /**
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return 'id';
    }

    /**
     * 
     */
    public function getUnsafeAttributes()
    {
        $unsafeAttributes = [];

        foreach ($this->attributes as $key => $value) {
            if (!$this->isRuleSafe($key)) {
                $unsafeAttributes[$key] = $value;
            }
        }
        return $unsafeAttributes;
    }

    /**
     *
     * @param type $attribute
     * @return boolean
     */
    private function isRuleSafe($attribute)
    {
        $ret   = false;
        $rules = $this->getActiveValidators($attribute);
        foreach ($rules as $validator) {
            if ($validator instanceof \yii\validators\SafeValidator) {
                $ret = true;
                break;
            }
        }
        return $ret;
    }

    /**
     *
     * @param string $where
     */
    public function findOne($where)
    {
        $ret    = false;
        $driver = new $this->driver;
        $driver->setDb($this->db);
        $driver->setColumns(array_keys($this->unsafeAttributes));
        $driver->setData($this->unsafeAttributes);
        $driver->setTable($this->tableName);
        $data   = $driver->select($where);

        if ($data && count($data)) {
            $this->populate($data[0]);
            $ret = true;
        }
        return $ret;
    }
    /*
     * 
     */

    private function populate($rows)
    {
        foreach ($rows as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     *
     * @param type $attributeNames
     * @param type $clearErrors
     */
    public function checklist($attributeNames = null, $clearErrors = true)
    {
        if(is_array($this->$attributeNames)){
            $this->$attributeNames = implode(",", $this->$attributeNames);
        }
    }
}