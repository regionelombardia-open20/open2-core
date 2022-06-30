<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\models\base
 * @category   CategoryName
 */

namespace open20\amos\core\models\base;

use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;
use yii\helpers\ArrayHelper;

/**
 * Class MapWidgetPlaces
 *
 * This is the base-model class for table "map_widget_places".
 *
 * @property string $place_id
 * @property string $place_response
 * @property string $place_type
 * @property string $country
 * @property string $region
 * @property string $province
 * @property string $postal_code
 * @property string $city
 * @property string $address
 * @property string $street_number
 * @property string $latitude
 * @property string $longitude
 *
 * @package open20\amos\core\models\base
 */
abstract class MapWidgetPlaces extends Record
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'map_widget_places';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['place_id'], 'required'],
            [['place_response'], 'string'],
            [[
                'place_id',
                'place_type',
                'country',
                'region',
                'province',
                'city',
                'address',
                'latitude',
                'longitude',
                'postal_code',
                'street_number'
            ], 'string', 'max' => 255],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'place_id' => BaseAmosModule::t('amoscore', 'Codice recupero place'),
            'place_response' => BaseAmosModule::t('amoscore', 'Risposta'),
            'place_type' => BaseAmosModule::t('amoscore', 'Tipologia di recupero dati'),
            'country' => BaseAmosModule::t('amoscore', 'Paese'),
            'region' => BaseAmosModule::t('amoscore', 'Regione'),
            'province' => BaseAmosModule::t('amoscore', 'Provincia'),
            'postal_code' => BaseAmosModule::t('amoscore', 'CAP'),
            'city' => BaseAmosModule::t('amoscore', 'Città'),
            'address' => BaseAmosModule::t('amoscore', 'Via/Piazza'),
            'street_number' => BaseAmosModule::t('amoscore', 'Numero civico'),
            'latitude' => BaseAmosModule::t('amoscore', 'Latitudine'),
            'longitude' => BaseAmosModule::t('amoscore', 'Longitudine'),
        ]);
    }
}
