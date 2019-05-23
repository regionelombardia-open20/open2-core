<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\utilities
 * @category   CategoryName
 */

namespace lispa\amos\core\utilities;

use lispa\amos\core\module\BaseAmosModule;
use yii\base\BaseObject;
use Yii;
use yii\helpers\Json;

/**
 * Class MapsUtility
 * @package lispa\amos\core\utilities
 */
class MapsUtility extends BaseObject
{

    /**
     * Get latitude and longitude of a place.
     * @param string $position - Place to search coordinates
     * @return array $origin - empty array if coordinates not found, otherwise array with structure
     * $origin = [
     *      'lat' => '41.1234',  // the Latitude of $position
     *      'lng' => '9.657'    // the longitude of $position
     * ]
     */
    public static function getMapPosition($position = '')
    {
        if (!$position) {
            $position = 'Roma';
        }
        if (!is_null(Yii::$app->params['googleMapsApiKey'])){
            $googleMapsKey = Yii::$app->params['googleMapsApiKey'];
        } elseif (Yii::$app->params['google_places_api_key']){
            $googleMapsKey = Yii::$app->params['google_places_api_key'];
        } elseif(!is_null(Yii::$app->params['google-maps']) && !is_null(Yii::$app->params['google-maps']['key'])){
            $googleMapsKey = Yii::$app->params['google-maps']['key'];
        } else {
            Yii::$app->session->addFlash('warning', BaseAmosModule::t('amoscore', 'Errore di comunicazione con google: impossibile trovare la posizione nella mappa.'));
            return [];
        }

        $GeoCoderParams = urlencode($position);
        $UrlGeocoder = "https://maps.googleapis.com/maps/api/geocode/json?address=$GeoCoderParams&key=$googleMapsKey";
        $origin = [];
        try {
            $ResulGeocoding = Json::decode(file_get_contents($UrlGeocoder));
        } catch (\Exception $exception) {
            return $origin;
        }

        if ($ResulGeocoding['status'] == 'OK') {
            if (isset($ResulGeocoding['results']) && isset($ResulGeocoding['results'][0])) {
                $Indirizzo = $ResulGeocoding['results'][0];

                if (isset($Indirizzo['geometry'])) {
                    $Location = $Indirizzo['geometry']['location'];

                    if (isset($Location['lat'])) {
                        $origin['lat'] = $Location['lat'];
                    }
                    if (isset($Location['lng'])) {
                        $origin['lng'] = $Location['lng'];
                    }

                }
            }
        }

        if (empty($origin)) {
            $pos = strpos($position, ',');
            if ($pos) {
                $position = substr($position, $pos);
                return self::getMapPosition($position);
            } else {
                return self::getMapPosition();
            }
        }
        return $origin;
    }

}