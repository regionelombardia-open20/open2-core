<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\migrations
 * @category   CategoryName
 */

use yii\db\Migration;

class m161219_100040_alter_table_user_drop_email_unique extends Migration
{
    public function safeUp()
    {
        try {
            $this->dropIndex('email','user');
            //$this->db->createCommand()->setSql("ALTER TABLE user DROP INDEX IF EXISTS email")->execute();
        } catch (Exception $exception) {
            echo "Rimozione indice unique su campo email tabella user fallita\n";
            echo $exception->getMessage();
            echo "\n";
        }
        return true;
    }

    public function safeDown()
    {
        try {
            $this->createIndex('email', 'user', 'email',ture);
            //$this->db->createCommand()->setSql("ALTER TABLE user ADD UNIQUE IF NOT EXISTS(email)")->execute();
        } catch (Exception $exception) {
            echo "Aggiunta indice unique su campo email tabella user fallita\n";
            echo $exception->getMessage();
            echo "\n";
        }
        return true;
    }
}
