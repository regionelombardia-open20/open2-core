<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\editors\maps
 * @category   CategoryName
 */

namespace open20\amos\core\forms\editors\maps;

use open20\amos\core\components\PlacesComponents;
use open20\amos\core\module\BaseAmosModule;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\View;

/**
 * Class PlaceWidget
 * @package open20\amos\core\forms\editors\maps
 */
class PlaceWidget extends Widget
{
    /**
     * @var Model the data model that this widget is associated with.
     */
    public $model;
    
    /**
     * @var string the model attribute that this widget is associated with.
     */
    public $attribute;
    
    /**
     * @var string the input name. This must be set if [[model]] and [[attribute]] are not set.
     */
    public $name;
    
    /**
     * @var string the hasOne relation name in [[model]] for place
     */
    public $placeAlias;
    
    /**
     * @var array Options of jQuery plugin.
     */
    public $pluginOptions = [];
    
    /**
     * @var string[] $options
     */
    public $options = [
        'id' => 'autocomplete'
    ];
    
    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        //init name
        $this->name = !isset($this->options['name']) ? Html::getInputName($this->model, $this->attribute) : $this->options['name'];
        
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : false;
        }
        parent::init();
        
        PlaceAssets::register($this->view);
        
        $this->options = array_merge([
            'onFocus' => 'geolocate()'
        ], $this->options);
        
        $this->pluginOptions = array_merge([
        
        ], $this->pluginOptions);
        
        $gpJsLink = 'https://maps.googleapis.com/maps/api/js?' . http_build_query(array(
                'key' => Yii::$app->params['google-maps']['key'],
                'libraries' => 'places',
                'callback' => 'initMaps'
            ));
        
        $js = <<<JS
var map_{$this->attribute} = null;
var input_{$this->attribute} = null;
var autocomplete_{$this->attribute} = null;
var infowindow_{$this->attribute} = null;
var marker_{$this->attribute} = null;
var geocoder_{$this->attribute} = null;

function initMap_{$this->attribute}() {
    //init map
    map_{$this->attribute} = new google.maps.Map(document.getElementById('map-{$this->attribute}'), {
        center: {lat: 41.909986, lng: 12.3959155},
        zoom: 10
    });

    //init the geocoder
    geocoder_{$this->attribute} = new google.maps.Geocoder;
    
    //bind the input for the autocomplete and put it in the map
    input_{$this->attribute} = document.getElementById('pac-input-{$this->attribute}');
    autocomplete_{$this->attribute} = new google.maps.places.Autocomplete(input_{$this->attribute});
    autocomplete_{$this->attribute}.bindTo('bounds', map_{$this->attribute});
    map_{$this->attribute}.controls[google.maps.ControlPosition.TOP_LEFT].push(input_{$this->attribute});

    infowindow_{$this->attribute} = new google.maps.InfoWindow();
    marker_{$this->attribute} = new google.maps.Marker({
        map: map_{$this->attribute}
    });
    marker_{$this->attribute}.addListener('click', function() {
        infowindow_{$this->attribute}.open(map_{$this->attribute}, marker_{$this->attribute});
    });

    if(document.getElementById('place-id-{$this->attribute}').value){
        var placeId = document.getElementById('place-id-{$this->attribute}').value;
        updateMarkerByPlaceId(placeId, '$this->attribute');
    }

    google.maps.event.addListenerOnce(map_{$this->attribute}, 'idle', function(){
        document.getElementById('map-loader-{$this->attribute}').classList.add("hidden");
        document.getElementById('pac-input-{$this->attribute}').classList.remove("hidden");
    });
    
    autocomplete_{$this->attribute}.addListener('place_changed', function() {
        infowindow_{$this->attribute}.close();
        var place = autocomplete_{$this->attribute}.getPlace();
        if (!place.geometry) {
            return;
        }

        //save the new place value
        document.getElementById('place-id-{$this->attribute}').value = place.place_id;
        updateMarkerInfo('{$this->attribute}', place.place_id, place.geometry.location);

        //update the marker
        marker_{$this->attribute}.setVisible(true);
        updateInfoWindow('{$this->attribute}', place.formatted_address);
    });
    
    addCurrentLocationButton('$this->attribute');
}
JS;
        
        $this->view->registerJs($js, View::POS_HEAD);
        
        $this->view->registerJsFile($gpJsLink, [
            'depends' => PlaceAssets::className(),
            'async' => 'async',
            'defe' => 'defe'
        ]);
        
    }
    
    /**
     * @return boolean whether this widget is associated with a data model.
     */
    protected function hasModel()
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        //get the place data
        $placeDataObj = $this->model->{$this->placeAlias};
        $place_fake = false;
        $place_id = !empty($this->model->{$this->attribute}) ? $this->model->{$this->attribute} : '';
        if ($placeDataObj) {
            $place_fake = ($placeDataObj && $placeDataObj->place_type == 'fake' ? true : false);
            $place_id = $placeDataObj->place_id;
        }
        
        if ($place_fake) {
            $geocodeString = PlacesComponents::getGeocodeString($placeDataObj);
            if ($geocodeString) {
                $tmp_place_id = PlacesComponents::getGoogleResponseByGeocodeString($geocodeString, true);
                if ($tmp_place_id) {
                    $place_id = $tmp_place_id;
                }
            }
        }
        
        return
            Html::hiddenInput($this->name, $place_id, [
                'id' => 'place-id-' . $this->attribute,
            ]) .
            Html::textInput('tmp-pac-input_' . $this->attribute, "", [
                'id' => 'pac-input-' . $this->attribute,
                'class' => 'controls hidden'
            ]) .
            Html::tag('div', BaseAmosModule::t('amoscore', 'Caricamento mappa in corso') . "<br /><div id=\"progress-" . $this->attribute . "\" class=\"progress\"><div class=\"progress-bar progress-bar-striped active\" role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:100%\"></div></div>", [
                'id' => 'map-loader-' . $this->attribute,
                'class' => 'map-loader'
            ]) .
            Html::tag('div', null, [
                'id' => 'map-' . $this->attribute,
                'class' => 'map',
                'data' => [
                    'attribute' => $this->attribute
                ]
            ]) .
            ($place_fake
                ?
                "<div class='place-alert'>" . BaseAmosModule::t('amoscore', '#place_widget_address_identification_error') . "</div>"
                :
                ""
            );
    }
}
