<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migrations
 * @category   CategoryName
 */

use yii\db\Migration;

/**
 * Class m191216_163838_fix_table_attributes_change_log
 */
class m200525_095838_add_field_attributes_change_log extends Migration
{
    const TABLE = '{{%attributes_change_log}}';


    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE, 'user_activity_log_id', $this->integer()->defaultValue(null)->after('model_attribute'));
        $this->addForeignKey('fk_attributes_change_log_user_activity_log_id1',self::TABLE, 'user_activity_log_id', 'user_activity_log', 'id');
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0;');
        $this->dropForeignKey('fk_attributes_change_log_user_activity_log_id1',self::TABLE);
        $this->dropColumn(self::TABLE, 'user_activity_log_id');
        $this->execute('SET FOREIGN_KEY_CHECKS = 1;');

        return true;
    }
}
