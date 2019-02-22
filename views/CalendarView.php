<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views
 * @category   CategoryName
 */

namespace lispa\amos\core\views;

use lispa\amos\core\views\common\BaseListView;
use Yii;
use yii\helpers\Html;

class CalendarView extends BaseListView
{

    public $events = [];
    public $titolo = null;
    public $intestazione = null; //contenuti html caricati ad inizio pagina prima del calendario - intestazioni o altro
    public $replace = [];
    public $array = false;//se array è settato verrà ignorato $eventConfig e si userà solo $getEventi
    public $getEventi = 'getEvents';
    public $layout = "{summary}\n{items}\n{pager}";

    public $defaultClientOptions = [

    ];

    public $clientOptions = [

    ];

    public $eventConfig = [
        'id' => 'id',
        'title' => 'titolo',
        'start' => 'data_inizio',
        'end' => 'data_fine',
        'color' => 'colore',
        'url' => 'url',
    ];

    public function init()
    {
        parent::init();

        $this->defaultClientOptions = [
            'lang' => Yii::$app->language == 'it-IT' ? 'it' : Yii::$app->language,
        ];

        $this->setClientOptions(\yii\helpers\ArrayHelper::merge($this->defaultClientOptions, $this->getClientOptions()));

        $models = $this->dataProvider->getModels();
        $keys = $this->dataProvider->getKeys();

        $this->setEvents($this->initEvents($models));
    }

    public function getClientOptions()
    {
        return $this->clientOptions;
    }

    public function setClientOptions(array $clientOptions)
    {
        $this->clientOptions = $clientOptions;
    }

    public function initEvents($models)
    {

        $events = [];
        if ($this->array) {
            foreach ($models as $model) {
                foreach ($model->{$this->getEventi}() as $Event) {
                    $events[] = $Event;
                }
            }
        } else {
            foreach ($models as $model) {
                $Event = new \yii2fullcalendar\models\Event();
                foreach ($this->eventConfig as $kEvent => $vEvent) {
                    $Event->{$kEvent} = $model[$vEvent];
                }
                $events[] = $Event;
            }
        }
        return $events;
    }

    public function run()
    {
        $intestazione = $this->intestazione; //contenuti html caricati ad inizio pagina prima del calendario
        $content = $this->renderItems(); //contenuti caricati in fondo alla pagina legati al model come la legenda per esempio
        $options = $this->itemOptions;
        return Html::tag('div', $intestazione) . AmosFullCalendar::widget([
                'options' => $this->getClientOptions(),
                'clientOptions' => $this->getClientOptions(),
                'events' => $this->getEvents(),
            ]) . Html::tag('div', $content, $options);
    }

    /**
     * Renders all data models.
     * @return string the rendering result
     */
    public function renderItems()
    {
        $models = $this->dataProvider->getModels();
        $keys = $this->dataProvider->getKeys();
        $content = [];
        foreach (array_values($models) as $index => $model) {
            $content[] = $this->renderItem($model, $keys[$index], $index);
        }
        $itemsHtml = Html::tag($this->itemsContainerTag, implode("\n", $content), $this->itemsContainerOptions);
        return Html::tag('div', $itemsHtml, $this->containerOptions);
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function setEvents(array $events)
    {
        $this->events = $events;
    }

}
