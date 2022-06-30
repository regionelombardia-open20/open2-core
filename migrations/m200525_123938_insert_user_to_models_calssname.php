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
class m200525_123938_insert_user_to_models_calssname extends Migration
{
    const TABLE = '{{%attributes_change_log}}';


    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('models_classname', [
            'classname' => \open20\amos\core\user\User::className(),
            'module' => 'core',
            'label' => 'User',
        ]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0;');
        $this->delete('models_classname', [
            'classname' => \open20\amos\core\user\User::className(),
            'module' => 'core',
            'label' => 'User',
        ]);
        $this->execute('SET FOREIGN_KEY_CHECKS = 1;');

        return true;
    }
}
