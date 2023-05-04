<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\news\migrations
 * @category   CategoryName
 */

use open20\amos\core\migration\AmosMigrationTableCreation;

/**
 * Class m160912_084648_create_news_categorie
 */
class m220516_124548_create_custom_order_fields extends AmosMigrationTableCreation
{
    /**
     * @inheritdoc
     */
    protected function setTableName()
    {
        $this->tableName = '{{%custom_order_fields}}';
    }

    /**
     * @inheritdoc
     */
    protected function setTableFields()
    {
        $this->tableFields = [
            'id' => $this->primaryKey(),
            'modulo' => $this->string(255)->null()->defaultValue(null)->comment('Nome del modulo'),
            'colonna' => $this->string(255)->null()->defaultValue(null)->comment('Colonna'),
            'visibile' => $this->integer()->null()->defaultValue(null)->comment('Visibile'),        
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
        $this->addCommentOnTable($this->tableName, 'attributi ordinamento dinamico');
    }
}
