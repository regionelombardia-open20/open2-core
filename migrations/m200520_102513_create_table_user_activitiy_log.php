<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `een_partnership_proposal`.
 */
class m200520_102513_create_table_user_activitiy_log extends Migration
{
    const TABLE = "user_activity_log";

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema(self::TABLE, true) === null) {
            $this->createTable(self::TABLE, [
                'id' => Schema::TYPE_PK,
                'user_id' => $this->integer()->comment('User'),
                'type' => $this->string()->comment('Type'),
                'name' => $this->string()->comment('Activity'),
                'description' => $this->text()->comment('Activity description'),
                'models_classname_id' => $this->integer()->comment('Object'),
                'record_id' => $this->integer()->comment('Record id'),
                'attribute_before' => $this->text()->comment('Attribute before'),
                'attribute_after' => $this->text()->comment('Attribute after'),
                'exacuted_at' => $this->dateTime()->comment('Executed at'),
                'created_at' => $this->dateTime()->comment('Created at'),
                'updated_at' => $this->dateTime()->comment('Updated at'),
                'deleted_at' => $this->dateTime()->comment('Deleted at'),
                'created_by' => $this->integer()->comment('Created by'),
                'updated_by' => $this->integer()->comment('Updated at'),
                'deleted_by' => $this->integer()->comment('Deleted at'),
            ], $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB AUTO_INCREMENT=1' : null);
        }
        $this->addForeignKey('fk_user_activity_log_user_id',self::TABLE, 'user_id', 'user', 'id');
        $this->addForeignKey('fk_user_activity_log_models_classname_id',self::TABLE, 'models_classname_id', 'models_classname', 'id');
    }



    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0;');
        $this->dropTable(self::TABLE);
        $this->execute('SET FOREIGN_KEY_CHECKS = 1;');

    }
}
