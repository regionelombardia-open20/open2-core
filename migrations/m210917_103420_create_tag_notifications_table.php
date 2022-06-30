<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\comments\migrations
 * @category   CategoryName
 */

use open20\amos\core\migration\AmosMigrationTableCreation;

/**
 * Class m210917_103420_create_tag_notifications_table
 */
class m210917_103420_create_tag_notifications_table extends AmosMigrationTableCreation
{
    /**
     * @inheritdoc
     */
    protected function setTableName()
    {
        $this->tableName = '{{%tag_notifications%}}';
    }

    /**
     * @inheritdoc
     */
    protected function setTableFields()
    {
        $this->tableFields = [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->null()->comment('User ID'),
            'context_model_class_name' => $this->string()->defaultValue(null),
            'context_model_id' => $this->integer()->defaultValue(null),
            'read' => $this->boolean()->defaultValue(null),
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
    protected function addForeignKeys() {
        $this->addForeignKey('fk_tag_notifications_user_id', $this->tableName, 'user_id', 'user', 'id');
    }
}
