<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\record
 * @category   CategoryName
 */

namespace lispa\amos\core\record;

class RecordDynamicModel extends \yii\base\DynamicModel
{
    const UPDATE_INCREMENTAL = 1;
    const UPDATE_DIFFERENTIAL = 2;
    const UPDATE_OVERRIDE    = 3;

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
    private $pathDriver = '@vendor/lispa/amos-core/src/record/drivers';

    /**
     * Driver of the database, default is 'Mysql'. The driver are in drivers
     * @var string $driver
     */
    private $driver = 'lispa\\amos\\core\\record\\drivers\\Mysql';

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

    public function save()
    {
        $driver = new $this->driver;
        $driver->setDb($this->db);
        $driver->setColumns(array_keys($this->attributes));
        $driver->setData($this->attributes);
        $driver->setTable($this->tableName);
        $driver->save();
    }
}