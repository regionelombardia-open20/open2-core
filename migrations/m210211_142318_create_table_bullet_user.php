<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `m210211_142318_create_table_bullet_user`.
 */
class m210211_142318_create_table_bullet_user extends Migration
{
    const TABLE = "user_bullet";

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema(self::TABLE, true) === null) {
            $this->createTable(self::TABLE,
                [
                'id' => Schema::TYPE_PK,
                'user_id' => $this->integer()->comment('User'),
                'type' => $this->integer()->defaultValue(1)->comment('Type'),
                'module' => $this->string()->comment('Module'),
                'community_id' => $this->integer()->defaultValue(0)->comment('Community'),
                'tag_match' => $this->integer()->defaultValue(0)->comment('Tag match'),
                'created_at' => $this->dateTime()->null()->comment('Created at'),
                'updated_at' => $this->dateTime()->null()->comment('Updated at'),
                'deleted_at' => $this->dateTime()->null()->comment('Deleted at'),
                'created_by' => $this->integer()->null()->comment('Created by'),
                'updated_by' => $this->integer()->null()->comment('Updated at'),
                'deleted_by' => $this->integer()->null()->comment('Deleted at'),
                ],
                $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB AUTO_INCREMENT=1'
                        : null);
        }
        $this->addForeignKey('fk_bullet_user_user_id', self::TABLE, 'user_id', 'user', 'id');
        $this->createIndex('keyUserBullet', self::TABLE, ['user_id', 'type', 'module', 'community_id', 'tag_match'], true);
        $this->createIndex('updated_atIdx', self::TABLE, 'updated_at');
        $this->createIndex('typeIdx', self::TABLE, 'type');
        $this->createIndex('moduleIdx', self::TABLE, 'module');
        $this->createIndex('tag_matchIdx', self::TABLE, 'tag_match');
        $this->createIndex('community_idIdx', self::TABLE, 'community_id');
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