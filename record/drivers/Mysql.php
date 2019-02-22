<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\record
 * @category   CategoryName
 */

namespace lispa\amos\core\record\drivers;

use yii\helpers\Inflector;
use yii\log\Logger;

class Mysql
{
    const UPDATE_INCREMENTAL  = 1;
    const UPDATE_DIFFERENTIAL = 2;
    const UPDATE_OVERRIDE     = 3;

    /**
     *
     * @var integer $typeUpdate
     */
    private $typeUpdate = 1;

    /**
     * Name of the table
     * @var type $table
     */
    private $table;

    /**
     * Name of the database
     * @var string $db
     */
    private $db = 'db';

    /**
     *
     * @var array $data
     */
    private $data;

    /**
     *
     * @var array $columns
     */
    private $columns = [];

    /**
     *
     * @param array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 
     * @param string $db
     */
    public function setDb($db)
    {
        $this->db = $db;
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
     * Set the table
     * @param type $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * Get table name
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     *
     * @return boolean
     */
    public function createTable()
    {
        try {
            if (count($this->columns)) {

                $connection = \Yii::$app->{$this->db};

                $connection->enableQueryCache = 0;

                $transaction = $connection->beginTransaction();
                try {
                    $sql = "";

                    //se esiste già la tabella la cancella
                    if ($connection->schema->getTableSchema($this->table, true) !== null) {
                        $sql .= "drop table {{".$this->table."}};";
                    }

                    //script di creazione della tabella
                    $sql .= "create table {{".$this->table."}} (id INT AUTO_INCREMENT, ";
                    foreach ($this->columns as $col) {
                        $column = self::getSlug($col);
                        $sql    .= "[[$column]] TEXT, ";
                    }
                    $sql .= " PRIMARY KEY (id)) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB AUTO_INCREMENT=1;";

                    //eseguo la query
                    $command = $connection->createCommand($sql);
                    $result  = $command->execute();
                    $transaction->commit();
                    $connection->close();
                    return TRUE;
                } catch (\Exception $er) {
                    $transaction->rollBack();
                    $connection->close();
                    \Yii::getLogger()->log($er->getTrace(), Logger::LEVEL_ERROR);
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        } catch (\Exception $e) {
            \Yii::getLogger()->log($e->getTrace(), Logger::LEVEL_ERROR);
            return FALSE;
        }
    }

    /**
     * Inserisce i dati dell'importazione dentro la tabella.
     * @return boolean
     */
    public function saveData()
    {
        $sql       = "";
        $sqlParams = [];
        try {
            if (count($this->columns) && !empty($this->data)) {
                $connection                   = \Yii::$app->{$this->db};
                $connection->enableQueryCache = 0;

                $transaction = $connection->beginTransaction();
                try {
                    //creo la query di inserimento
                    $sql   .= "INSERT INTO {{".$this->table."}} (";
                    $indx1 = 0;
                    foreach ($this->columns as $col) {
                        $column = self::getSlug($col);
                        $sql    .= ($indx1 > 0 ? ", [[$column]]" : "[[$column]]");
                        $indx1++;
                    }
                    $sql   .= ") VALUES (";
                    $indx2 = 0;
                    foreach ($this->data as $k => $v) {
                        $column                  = self::getSlug($k);
                        $sql                     .= ($indx2 > 0 ? ", :{$column}" : ":{$column}");
                        $sqlParams[":{$column}"] = $v;
                        $indx2++;
                    }
                    $sql .= ")";

                    $command = $connection->createCommand($sql, $sqlParams);
                    $result  = $command->execute();
                    $transaction->commit();
                    $connection->close();
                    return $result;
                } catch (\Exception $er) {
                    $transaction->rollBack();
                    $connection->close();
                    \Yii::getLogger()->log($er->getTrace(), Logger::LEVEL_ERROR);
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            \Yii::getLogger()->log($e->getTrace(), Logger::LEVEL_ERROR);
            return false;
        }
    }

    /**
     *
     * @return boolean
     */
    public function save()
    {
        $connection                   = \Yii::$app->{$this->db};
        $connection->enableQueryCache = 0;
        $continue                     = true;
        if ($connection->schema->getTableSchema($this->table, true) === null) {
            $continue = $this->createTable();
        }
        if ($continue) {
            return $this->saveData();
        }
        return false;
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public static function getSlug($name)
    {
        return Inflector::slug($name, '_', true);
    }
}