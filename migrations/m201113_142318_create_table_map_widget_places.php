<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migrations
 * @category   CategoryName
 */

use open20\amos\core\migration\AmosMigrationTableCreation;

/**
 * Class m201113_142318_create_table_map_widget_places
 */
class m201113_142318_create_table_map_widget_places extends AmosMigrationTableCreation
{
    /**
     * @inheritdoc
     */
    protected function setTableName()
    {
        $this->tableName = '{{%map_widget_places}}';
    }
    
    /**
     * @inheritdoc
     */
    protected function setTableFields()
    {
        $this->tableFields = [
            'place_id' => $this->string(255)->null()->comment('Codice recupero place'),
            'place_response' => $this->text()->null()->comment('Risposta'),
            'place_type' => $this->string(255)->null()->comment('Tipologia di recupero dati'),
            'country' => $this->string(255)->null()->comment('Paese'),
            'region' => $this->string(255)->null()->comment('Regione'),
            'province' => $this->string(255)->null()->comment('Provincia'),
            'postal_code' => $this->string(255)->null()->comment('CAP'),
            'city' => $this->string(255)->null()->comment('Città'),
            'address' => $this->string(255)->null()->comment('Via/Piazza'),
            'street_number' => $this->string(255)->null()->comment('Numero civico'),
            'latitude' => $this->string(255)->null()->comment('Latitudine'),
            'longitude' => $this->string(255)->null()->comment('Longitudine'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    protected function afterTableCreation()
    {
        $this->addPrimaryKey('pk_map_widget_places_place_id', 'map_widget_places', 'place_id');
    }
}
