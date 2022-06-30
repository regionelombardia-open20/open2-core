<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migrations
 * @category   CategoryName
 */

use open20\amos\core\migration\AmosMigrationTableCreation;

/**
 * Class m191206_110806_create_table_attributes_change_log
 */
class m191206_110806_create_table_attributes_change_log extends AmosMigrationTableCreation
{
    /**
     * @inheritdoc
     */
    protected function setTableName()
    {
        $this->tableName = '{{%attributes_change_log}}';
    }

    /**
     * @inheritdoc
     */
    protected function setTableFields()
    {
        $this->tableFields = [
            'id' => $this->primaryKey(),
            'model_classname' => $this->string(255)->notNull(),
            'model_id' => $this->integer()->notNull(),
            'old_value' => $this->string(255)->null()->defaultValue(null),
            'new_value' => $this->string(255)->null()->defaultValue(null),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function beforeTableCreation()
    {
        parent::beforeTableCreation();
        $this->setAddCreatedUpdatedFields(true);
    }

    /**
     * @inheritdoc
     */
    protected function afterTableCreation()
    {
        $this->createIndex('attributes_change_log_index', $this->tableName, ['model_classname', 'model_id']);
    }
}
