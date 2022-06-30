<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migrations
 * @category   CategoryName
 */

use yii\db\Migration;

/**
 * Class m210520_163318_alter_table_bullet_counters
 */
class m210520_163318_alter_table_bullet_counters extends Migration
{
    const TABLE = "bullet_counters";
    
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableSchema = $this->db->getTableSchema(self::TABLE);
        $foreignKeys = $tableSchema->foreignKeys;
        if (isset($foreignKeys['fk_user_profile_idx'])) {
            $this->dropForeignKey('fk_user_profile_idx', self::TABLE);
        }
        if (!isset($foreignKeys['fk_user_profile_idx2'])) {
            $this->addForeignKey('fk_user_profile_idx2', self::TABLE, 'user_id', 'user_profile', 'user_id');
        }
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}
