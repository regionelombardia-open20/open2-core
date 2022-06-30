<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migration
 * @category   CategoryName
 */

namespace open20\amos\core\migration;

use yii\base\Exception;
use yii\db\Migration;

/**
 * Class AmosMigrationTableCreation. This class is useful to create new table. The basic use provides the
 * override of these methods: protected function setTableName() and protected function setTableFields().
 *
 * @package open20\amos\core\migration
 */
class AmosMigrationTableCreation extends Migration
{
    /**
     * @var array $tableFields An array where define table fields.
     */
    protected $tableFields;
    
    /**
     * @var string $tableName The table name.
     */
    protected $tableName;
    
    /**
     * @var bool $addCreatedUpdatedFields If true, add the created... and updated... table fields.
     */
    protected $addCreatedUpdatedFields;
    
    /**
     * @var string $rawTableName The raw table name.
     */
    private $rawTableName;
    
    /**
     * @var bool $_processInverted If true switch safeUp and safeDown operations. This mean that in up the table is removed and in down the table is created.
     */
    private $_processInverted = false;
    
    /**
     * @var string $extraTableOptions If is set it's added to the default table creation options.
     */
    private $extraTableOptions = '';
    
    /**
     * @return bool
     */
    public function isProcessInverted()
    {
        return $this->_processInverted;
    }
    
    /**
     * @param bool $processInverted
     */
    public function setProcessInverted($processInverted)
    {
        $this->_processInverted = $processInverted;
    }
    
    /**
     * @return string
     */
    public function getExtraTableOptions()
    {
        return $this->extraTableOptions;
    }
    
    /**
     * @param string $extraTableOptions
     */
    public function setExtraTableOptions($extraTableOptions)
    {
        $this->extraTableOptions = $extraTableOptions;
    }
    
    /**
     */
    public function init()
    {
        parent::init();
        $this->db->enableSchemaCache = false;
        $this->setAddCreatedUpdatedFields(false);
        $this->setTableName();
        $this->setRawTableName($this->db->getSchema()->getRawTableName($this->tableName));
        $this->setTableFields();  
    }
    
    /**
     * @param boolean $addCreatedUpdatedFields
     */
    public function setAddCreatedUpdatedFields($addCreatedUpdatedFields)
    {
        $this->addCreatedUpdatedFields = $addCreatedUpdatedFields;
    }
    
    /**
     * In this method you define the table name like this: $this->tableName = '{{%table_name}}';
     */
    protected function setTableName()
    {
        $this->tableName = '{{%new_table}}';
    }
    
    /**
     * In this method you define all table fields. You must insert the definitions in this array: $this->tableFields = [];
     */
    protected function setTableFields()
    {
        $this->tableFields = [];
    }
    
    /**
     * Use this instead of function up().
     */
    public function safeUp()
    {
        if ($this->isProcessInverted()) {
            return $this->removeTable();
        } else {
            return $this->addTable();
        }
    }
    
    /**
     * @return bool
     */
    private function addTable()
    {
        $tableFields = $this->getTableFields();
        if (empty($tableFields)) {
            echo "Nessun campo della tabella definito. Eseguire override del metodo 'protected function setTableFields()'";
            return false;
        }
        
        if ($this->db->schema->getTableSchema($this->tableName, true) === null) {
            try {
                $this->beforeTableCreation();
                $defaultMySQLOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB AUTO_INCREMENT=1';
                $tableOptions = null;
                if ($this->db->driverName === 'mysql') {
                    $tableOptions = $defaultMySQLOptions . ((!is_null($this->extraTableOptions && is_string($this->extraTableOptions)) ? ' ' . $this->extraTableOptions : ''));
                }
                $this->addCreatedUpdatedFields();
                $this->createTable(
                    $this->tableName,
                    $this->getTableFields(),
                    $tableOptions
                );
                $this->afterTableCreation();
                $this->beforeForeignKeysAdd();
                $this->addForeignKeys();
                $this->afterForeignKeysAdd();
            } catch (Exception $e) {
                echo "Errore durante la creazione della tabella " . $this->getRawTableName() . "\n";
                echo $e->getMessage() . "\n";
                return false;
            }
        } else {
            echo "Nessuna creazione eseguita in quanto la tabella " . $this->getRawTableName() . " esiste gia'\n";
        }
        
        return true;
    }
    
    /**
     * Private method that return the configuration table fields array.
     */
    private function getTableFields()
    {
        return $this->tableFields;
    }
    
    /**
     * Override to make operations before the table creation.
     */
    protected function beforeTableCreation()
    {
    
    }
    
    /**
     * This method add the created, updated and deleted fields to the table.
     */
    private function addCreatedUpdatedFields()
    {
        if ($this->isToAddCreatedUpdatedFields()) {
            $this->tableFields = array_merge($this->tableFields, [
                'created_at' => $this->dateTime()->null()->defaultValue(null)->comment('Created at'),
                'updated_at' => $this->dateTime()->null()->defaultValue(null)->comment('Updated at'),
                'deleted_at' => $this->dateTime()->null()->defaultValue(null)->comment('Deleted at'),
                'created_by' => $this->integer()->null()->defaultValue(null)->comment('Created by'),
                'updated_by' => $this->integer()->null()->defaultValue(null)->comment('Updated by'),
                'deleted_by' => $this->integer()->null()->defaultValue(null)->comment('Deleted by')
            ]);
        }
    }
    
    /**
     * @return boolean
     */
    public function isToAddCreatedUpdatedFields()
    {
        return $this->addCreatedUpdatedFields;
    }
    
    /**
     * Override to make operations after the table creation.
     */
    protected function afterTableCreation()
    {
    
    }
    
    /**
     * Override to make operations before adding foreign keys.
     */
    protected function beforeForeignKeysAdd()
    {
    
    }
    
    /**
     * Override to add foreign keys after table creation.
     */
    protected function addForeignKeys()
    {
    
    }
    
    /**
     * Override to make operations after adding foreign keys.
     */
    protected function afterForeignKeysAdd()
    {
    
    }
    
    /**
     * It returns the raw table name.
     *
     * @return string
     */
    public function getRawTableName()
    {
        return $this->rawTableName;
    }
    
    /**
     * This method is useful for set the raw table name.
     *
     * @param string $rawTableName
     */
    public function setRawTableName($rawTableName)
    {
        $this->rawTableName = $rawTableName;
    }
    
    /**
     * Use this instead of function down().
     */
    public function safeDown()
    {
        if ($this->isProcessInverted()) {
            return $this->addTable();
        } else {
            return $this->removeTable();
        }
    }
    
    /**
     * @return bool
     */
    private function removeTable()
    {
        try {
            if ($this->db->driverName === 'mysql') {
                $this->execute("SET FOREIGN_KEY_CHECKS = 0;");
            }
            $this->beforeDropTable();
            $this->dropTable($this->tableName);
            $this->afterDropTable();
            if ($this->db->driverName === 'mysql') {
                $this->execute("SET FOREIGN_KEY_CHECKS = 1;");
            }
        } catch (Exception $e) {
            echo "Errore durante la cancellazione della tabella " . $this->getRawTableName() . "\n";
            echo $e->getMessage() . "\n";
            return false;
        }
        
        return true;
    }
    
    /**
     * Override to make operations before the table drop.
     */
    protected function beforeDropTable()
    {
    
    }
    
    /**
     * Override to make operations after the table drop.
     */
    protected function afterDropTable()
    {
    
    }
}