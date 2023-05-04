<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    intranet-aria-platform\console\migrations
 * @category   CategoryName
 */

use yii\db\Migration;

class m220928_102256_tuning_v1 extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->execute("
            ALTER TABLE `notification_update`
ADD INDEX `module` (`module`);

ALTER TABLE `notification_update`
ADD INDEX `updated_at` (`updated_at`),
ADD INDEX `community_id` (`community_id`),
ADD INDEX `publication_rule` (`publication_rule`),
ADD INDEX `tags` (`tags`(100)),
ADD INDEX `deleted_at` (`deleted_at`);

ALTER TABLE `notification_user`
ADD INDEX `user_id` (`user_id`),
ADD INDEX `module` (`module`),
ADD INDEX `updated_at` (`updated_at`);

ALTER TABLE `notification_user`
ADD INDEX `community_id` (`community_id`),
ADD INDEX `publication_rule` (`publication_rule`);

ALTER TABLE `notification_update`
ADD INDEX `deleted_at_module_community_id` (`deleted_at`, `module`, `community_id`);

ALTER TABLE `notification_update`
ADD INDEX `publication_rule_deleted_at_module_community_id` (`publication_rule`, `deleted_at`, `module`, `community_id`);

ALTER TABLE `notification_user`
ADD INDEX `module_user_id_updated_at_community_id` (`module`, `user_id`, `updated_at`, `community_id`);

ALTER TABLE `notification_update`
ADD INDEX `module_updated_at_community_id` (`module`, `updated_at`, `community_id`);
            ");
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo 'No revert available.';

        return true;
    }
}