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
        $table = Yii::$app->db->schema->getTableSchema($this->tableName);
        if (!isset($table->columns['model_attribute'])) {
            // do something
            $this->addColumn($this->tableName, 'model_attribute',
                $this->string(255)->null()->defaultValue(null)->after('model_id'));
        }
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