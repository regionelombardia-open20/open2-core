<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\migrations
 * @category   CategoryName
 */

use lispa\amos\core\migration\libs\common\MigrationCommon;
use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\user\User;
use yii\db\Expression;
use yii\db\Migration;
use yii\db\Query;

/**
 * Class m170726_105837_user_add_unique_on_username_field
 */
class m170726_105837_user_add_unique_on_username_field extends Migration
{
    private $dbName = '';
    private $tableName = 'user';
    private $rawTableName = '';
    private $indexName = 'username_unique';
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->tableName = User::tableName();
        $this->rawTableName = $this->db->getSchema()->getRawTableName($this->tableName);
        $query = new Query();
        $this->dbName = $query->select(new Expression('database()'))->scalar();
    }
    
    private function checkIndexPresence()
    {
        $query = new Query();
        $query->from('information_schema.statistics');
        $query->where(['table_name' => $this->rawTableName, 'index_name' => $this->indexName, 'table_schema' => $this->dbName]);
        $count = $query->count();
        return $count;
    }
    
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        try {
            $count = $this->checkIndexPresence();
            if (!$count) {
                $this->createIndex($this->indexName, $this->tableName, 'username', true);
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Indice') . ' "' . $this->indexName . '" ' . BaseAmosModule::t('amoscore', 'non presente. Aggiungo.'));
            } else {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Indice') . ' "' . $this->indexName . '" ' . BaseAmosModule::t('amoscore', 'già presente. Nulla da aggiungere.'));
            }
        } catch (Exception $exception) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Aggiunta indice unique su campo username tabella user fallita'));
            MigrationCommon::printConsoleMessage($exception->getMessage());
            return false;
        }
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        try {
            $count = $this->checkIndexPresence();
            if (!$count) {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Indice') . ' "' . $this->indexName . '" ' . BaseAmosModule::t('amoscore', 'non presente. Nulla da rimuovere.'));
            } else {
                $this->dropIndex('username_unique', $this->tableName);
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Indice') . ' "' . $this->indexName . '" ' . BaseAmosModule::t('amoscore', 'presente. Rimuovo.'));
            }
        } catch (Exception $exception) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Rimozione indice unique su campo username tabella user fallita'));
            MigrationCommon::printConsoleMessage($exception->getMessage());
            return false;
        }
        return true;
    }
}
