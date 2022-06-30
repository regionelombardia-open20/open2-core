<?php

use yii\db\Migration;

class m190211_123906_create_table_models_classname extends Migration
{
    public $tablename = '{{%models_classname}}';

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->schema->getTableSchema($this->tablename, true) === null) {
            $this->createTable($this->tablename, [
                'id' => $this->primaryKey(),
                'classname' => $this->string(),
                'module' => $this->string(),
                'label' => $this->string(),
                'description' => $this->string(),
                'created_at' => $this->dateTime(),
                'created_by' => $this->integer(),
                'updated_at' => $this->dateTime(),
                'updated_by' => $this->integer(),
                'deleted_at' => $this->dateTime(),
                'deleted_by' => $this->integer(),
            ], $tableOptions);

            $this->insert( $this->tablename, ['classname' => 'open20\amos\news\models\News', 'module' => 'news', 'label' => 'News']);
            $this->insert( $this->tablename, ['classname' => 'open20\amos\events\models\Event', 'module' => 'events', 'label' => 'Event']);
            $this->insert( $this->tablename, ['classname' => 'open20\amos\documenti\models\Documenti', 'module' => 'documenti',  'label' => 'Documenti']);
            $this->insert( $this->tablename, ['classname' => 'open20\amos\discussioni\models\DiscussioniTopic', 'module' => 'discussioni', 'label' => 'Discussioni']);
            $this->insert( $this->tablename, ['classname' => 'open20\amos\partnershipprofiles\models\PartnershipProfiles', 'module' => 'partnershipprofiles',  'label' => 'PartnershipProfile']);
            $this->insert( $this->tablename, ['classname' => 'open20\amos\een\models\EenPartnershipProposal', 'module' => 'een',  'label' => 'EenPartnershipProposal']);
            $this->insert( $this->tablename, ['classname' => 'open20\amos\showcaseprojects\models\ShowcaseProject', 'module' => 'showcaseprojects',  'label' => 'ShowcaseProjects']);
            $this->insert( $this->tablename, ['classname' => 'open20\amos\sondaggi\models\Sondaggi', 'module' => 'sondaggi',  'label' => 'Sondaggi']);
            $this->insert( $this->tablename, ['classname' => 'open20\amos\showcaseprojects\models\Initiative', 'module' => 'showcaseprojects', 'label' => 'Initiative']);
            $this->insert( $this->tablename, ['classname' => 'amos\results\models\Result', 'module' => 'results','label' => 'Result']);

            $this->createTable('content_shared', [
                'id' => $this->primaryKey(),
                'models_classname_id' => $this->integer(),
                'content_id' => $this->integer(),
                'created_at' => $this->dateTime(),
                'created_by' => $this->integer(),
                'updated_at' => $this->dateTime(),
                'updated_by' => $this->integer(),
                'deleted_at' => $this->dateTime(),
                'deleted_by' => $this->integer(),
            ], $tableOptions);

            $this->addForeignKey('fk_content_shared_models_classname_id1','content_shared', 'models_classname_id', 'models_classname', 'id' );
        }
    }

    public function down()
    {
        $this->dropTable('{{%models_classname}}');
        $this->dropTable('{{%content_shared}}');
    }
}
