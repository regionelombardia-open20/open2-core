<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views
 * @category   CategoryName
 */

namespace open20\amos\core\views;

use dosamigos\google\maps\Map;
use open20\amos\core\record\Record;
use dosamigos\google\maps\LatLng;
use open20\amos\core\utilities\MapsUtility;
use open20\amos\core\views\MapAmos;
use dosamigos\google\maps\MapAsset;
use dosamigos\google\maps\overlays\InfoWindow;
use dosamigos\google\maps\overlays\Marker;
use yii\helpers\ArrayHelper;
use yii\widgets\ListView as BaseListView;
use dosamigos\google\maps\services\TravelMode;
use dosamigos\google\maps\overlays\PolylineOptions;
use dosamigos\google\maps\services\DirectionsRenderer;
use dosamigos\google\maps\services\DirectionsService;
use dosamigos\google\maps\services\DirectionsRequest;
use dosamigos\google\maps\layers\BicyclingLayer;

class MapView extends BaseListView
{
    const DEFAULT_ZOOM = 8;

    public $name = 'map';

    /**
     * @var Map $map
     */
    public $map;
    public $zoom         = null;
    public $autoCenter   = true;
    public $originPoint;
    public $travelMode;
    public $centerPoint  = [
        'lat' => 44.5072,
        'lng' => 11.3620
    ];
    public $markers;
    public $positionAttribute;
    public $markerConfig = [
        'lat' => 'lat',
        'lng' => 'lng',
        'icon' => 'iconaMarker', //percorso icona
    ];
    public $disablePagination = true;
    public $styles;

    public function init()
    {
        parent::init();

        $LatLngCenter = new LatLng(['lat' => $this->centerPoint['lat'], 'lng' => $this->centerPoint['lng']]);
        if (!$this->zoom) {
            $this->zoom = self::DEFAULT_ZOOM; //default zoom
        }
        $mapConfig = [
            'zoom' => $this->zoom,
            'center' => $LatLngCenter,
            'width' => '100%',
        ];

        if ($this->styles) {
            $mapConfig['styles'] = $this->styles;
        }
//        $this->map    = new MapAmos($mapConfig);
        $this->map = new Map($mapConfig);


        $this->initMarkers();

        $this->directions();

        $this->pushMarkers();

        $this->flushMap();
    }

    public function directions()
    {
        $origin = null;

        if (isset($this->originPoint)) {
            $origin = new LatLng([
                'lat' => $this->originPoint['lat'],
                'lng' => $this->originPoint['lng'],
            ]);

            if (count($this->markers) == 1) {
                $destination = $this->markers[0]->position;

                $travelMode = TravelMode::DRIVING;
                if ($this->travelMode == 'driving') $travelMode = TravelMode::DRIVING;
                if ($this->travelMode == 'transit') $travelMode = TravelMode::TRANSIT;
                if ($this->travelMode == 'walking') $travelMode = TravelMode::WALKING;

                $directionsRequest = new DirectionsRequest([
                    'origin' => $origin,
                    'destination' => $destination,
                    'travelMode' => $travelMode
                ]);

                // Lets configure the polyline that renders the direction
                $polylineOptions = new PolylineOptions([
                    'strokeColor' => '#FFAA00',
                ]);

                // Now the renderer
                $directionsRenderer = new DirectionsRenderer([
                    'map' => $this->map->getName(),
                    'polylineOptions' => $polylineOptions
                ]);

                // Finally the directions service
                $directionsService = new DirectionsService([
                    'directionsRenderer' => $directionsRenderer,
                    'directionsRequest' => $directionsRequest
                ]);

                // Thats it, append the resulting script to the map
                $this->map->appendScript($directionsService->getJs());
            }
        }
    }

    public function initMarkers()
    {
        if ($this->disablePagination == true){
            $this->dataProvider->setPagination(false);
        }
        $models  = $this->dataProvider->getModels();
        $keys    = $this->dataProvider->getKeys();
        $markers = [];
        foreach (array_values($models) as $index => $model) {
            if ($marker = $this->getMarker($model, $keys[$index], $index)) {
                $markers[] = $marker;
            }
        }
        $this->markers = $markers;
    }

    public function flushMap()
    {
        if ($this->autoCenter) {
            if ($LatLonCenter = $this->map->getMarkersCenterCoordinates()) {
                $this->map->setCenter($LatLonCenter);
                if (!$this->zoom || $this->zoom == self::DEFAULT_ZOOM) {
                    $this->map->zoom = $this->map->getMarkersFittingZoom();
                }
            }
        }
    }

    public function getMarker(Record $model, $key, $index)
    {
        $marker = null;

        if ($this->validateMarker($model, $key, $index)) {
            if ($this->positionAttribute) {
                $positionAttribute = $this->positionAttribute;
                $coords            = MapsUtility::getMapPosition($model->$positionAttribute);
                $LatLng            = new LatLng($coords);
            } else {
                $LatLng = new LatLng([
                    'lat' => $model[$this->markerConfig['lat']],
                    'lng' => $model[$this->markerConfig['lng']],
                ]);
            }

            $markerData = [
                'position' => $LatLng,
                'title' => $model->__toString(),
            ];

            if (isset($model[$this->markerConfig['icon']])) {
                $markerData = ArrayHelper::merge($markerData, ['icon' => $model[$this->markerConfig['icon']]]);
            }
            $marker = new Marker($markerData);

            $marker->attachInfoWindow(
                new InfoWindow([
                'content' => $this->renderItem($model, $key, $index)
                ])
            );
        }
        return $marker;
    }

    /**
     * @param Record $model
     * @param $key
     * @param $index
     * @return bool
     */
    public function validateMarker($model, $key, $index)
    {
        if ($this->positionAttribute) {
            if ($model->hasProperty($this->positionAttribute)) {
                return true;
            }
        }
        if (isset($model) && isset($model[$this->markerConfig['lat']]) && isset($model[$this->markerConfig['lng']])) {
            return true;
        }
        return false;
    }

    public function pushMarkers()
    {
        foreach ($this->markers as $marker) {
            $this->map->addOverlay($marker);
        }
    }

    public function run()
    {
        MapAsset::register($this->getView());
        $moduleL = \Yii::$app->getModule('layout');
        if (!empty($moduleL)) {
            \open20\amos\layout\assets\AmosMapAsset::register($this->getView());
        } else {
            \open20\amos\core\views\assets\AmosMapAsset::register($this->getView());
        }
        return $this->map->display();
    }
}