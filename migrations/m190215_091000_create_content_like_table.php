<?php

use yii\db\Migration;

/**
 */
class m190215_091000_create_content_like_table extends Migration {

  protected
    $tableName = '{{%content_likes}}',
    $tableOptions = null
  ;
  
  /**
   * Create tableName
   * 
   */
  public function up() {
    if ($this->db->driverName === 'mysql') {
      $this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
    }
        
    if ($this->db->schema->getTableSchema($this->tableName, true) === null) {
      $this->createTable(
        $this->tableName,
        [
          'id' => $this->primaryKey(),
          'models_classname_id' => $this->integer(11)->notNull()->defaultValue(null)->comment('Tipologia di contenuto: News, Documento, Discussione da mode_classname'),
          'content_id' => $this->integer(11)->notNull()->defaultValue(null),
          'user_id' => $this->integer(11)->notNull()->defaultValue(null),
          'user_ip' => $this->string(39)->notNull()->defaultValue('127.0.0.1'),
          'likes' => $this->smallInteger(1)->notNull()->defaultValue(null)->comment('Vale null|0|1 a seconda se: NON ho mai fatto like (null), 0 ho fatto un unlike, 1 fatto like, la somma mi da il totale per: Piace a <x> utenti'),
          'created_at' => $this->dateTime(),
          'updated_at' => $this->dateTime(),
          'deleted_at' => $this->dateTime()->comment('Cancellato il'),
          'created_by' => $this->integer(11)->defaultValue(null)->comment('Creato da'),
          'updated_by' => $this->integer(11)->defaultValue(null)->comment('Aggiornato da'),
          'deleted_by' => $this->integer(11)->defaultValue(null)->comment('Cancellato da')
        ],
        
        $this->tableOptions
      );      

      $this->createIndex(
        'content_idx',
        $this->tableName,
        ['models_classname_id', 'content_id', 'user_id'],
        false
      );

      $this->addForeignKey(
        'fk_user_idx',
        $this->tableName,
        'user_id',
        '{{%user}}',
        'id'
      );
              
      $this->addForeignKey(
        'fk_models_classname_idx',
        $this->tableName,
        'models_classname_id',
        '{{%models_classname}}',
        'id'
      );
    }
    
  }

  /**
   * Remove tableName 
   * 
   */
  public function down() {
    $this->dropTable($this->tableName);
  }

}