<?php

use yii\db\Migration;

/**
 * 
 */
class m190710_101000_alter_translation extends Migration
{
    protected
        $tableName    = '{{%language_source}}',
        $tableOptions = null;

    /**
     * Create tableName
     *
     */
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            $this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->schema->getTableSchema($this->tableName, true) !== null) {
            $this->addColumn($this->tableName, 'urls', $this->text()->null());
        }
    }

    /**
     * Remove tableName 
     * 
     */
    public function down()
    {
        $this->dropColumn($this->tableName, 'urls');
    }
}