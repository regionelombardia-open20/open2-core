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

class m170905_154147_add_index_language_source extends Migration
{
    const TABLE = '{{%language_source}}';


    public function safeUp()
    {
        if ($this->db->schema->getTableSchema(self::TABLE, true))
        {
            $this->createIndex("category_message", self::TABLE, ["category","message(100)"]);
        }
        else
        {
            echo "Nessuna creazione eseguita in quanto la tabella esiste gia'";
        }

        return true;
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema(self::TABLE, true) !== null)
        {
            $this->dropIndex('category_message',self::TABLE );
        }
        else
        {
            echo "Nessuna cancellazione eseguita in quanto la tabella non esiste";
        }

        return true;
    }
}
