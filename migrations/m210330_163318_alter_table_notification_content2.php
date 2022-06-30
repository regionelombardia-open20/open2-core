<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Handles the creation of table `m210330_163318_alter_table_notification_content2`.
 */
class m210330_163318_alter_table_notification_content2 extends Migration
{
    const TABLE = "notification_update";
    const TABLE2 = "notification_user";

    /**
     * @inheritdoc
     */
    public function safeUp()
    {     
        $this->createIndex('keyIdxUpdates', self::TABLE, ['module', 'content_id', 'publication_rule', 'community_id'], true);
        $this->createIndex('keyIdxBullet', self::TABLE2, ['user_id', 'module', 'publication_rule', 'community_id'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {      
        $this->dropIndex('keyIdxUpdates', self::TABLE);
        $this->dropTable('keyIdxBullet', self::TABLE2);
    }
}