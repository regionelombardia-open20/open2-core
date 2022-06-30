<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `m210330_142318_create_table_notification_content`.
 */
class m210330_142318_create_table_notification_content extends Migration
{
    const TABLE = "notification_update";
    const TABLE2 = "notification_user";

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema(self::TABLE, true) === null) {
            $this->createTable(self::TABLE,
                [                
                'module' => $this->string()->comment('Module'),
                'content_id' => $this->integer()->notNull()->comment('Content'),
                'publication_rule' => $this->integer()->defaultValue(1)->comment('Publication Rule'),
                'tags' => $this->text()->null()->comment('Tags'),
                'community_id' => $this->integer()->defaultValue(0)->comment('Community'),
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
        if ($this->db->schema->getTableSchema(self::TABLE2, true) === null) {
            $this->createTable(self::TABLE2,
                [              
                'user_id' => $this->integer()->comment('User'),
                'module' => $this->string()->comment('Module'),
                'publication_rule' => $this->integer()->defaultValue(1)->comment('Publication Rule'),
                'community_id' => $this->integer()->defaultValue(0)->comment('Community'),
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
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {      
        $this->dropTable(self::TABLE);
        $this->dropTable(self::TABLE2);
    }
}