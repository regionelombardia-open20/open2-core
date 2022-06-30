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
class m191216_163838_fix_table_attributes_change_log extends Migration
{
    private $tableName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->tableName = '{{%attributes_change_log}}';
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'model_attribute', $this->string(255)->null()->defaultValue(null)->after('model_id'));
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'model_attribute');
        return true;
    }
}
