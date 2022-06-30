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
 * Class m210202_093332_alter_counters
 */
class m210202_093332_alter_counters extends Migration
{
    const TABLE = '{{%update_contents}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE, 'tag_id', $this->integer()->defaultValue(0)->after('module'));
        $this->addColumn(self::TABLE, 'network', $this->integer()->defaultValue(0)->after('tag_id'));
        $this->addColumn(self::TABLE, 'community_id', $this->integer()->defaultValue(0)->after('network'));
        $this->dropIndex('module', self::TABLE);
        $this->createIndex('keyBullet', self::TABLE, ['module', 'tag_id', 'network', 'community_id'], true);
        $this->createIndex('tagIdx', self::TABLE, 'tag_id');
        $this->createIndex('moduleIdx', self::TABLE, 'module');
        $this->createIndex('networkIdx', self::TABLE, 'network');
        $this->createIndex('communityIdx', self::TABLE, 'community_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('keyBullet', self::TABLE);
        $this->dropIndex('tagIdx', self::TABLE);
        $this->dropIndex('moduleIdx', self::TABLE);
        $this->dropIndex('networkIdx', self::TABLE);
        $this->dropIndex('communityIdx', self::TABLE);
        $this->dropColumn(self::TABLE, 'tag_id');
        $this->dropColumn(self::TABLE, 'network');
        $this->dropColumn(self::TABLE, 'community_id');
    }
}