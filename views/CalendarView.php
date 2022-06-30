<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views
 * @category   CategoryName
 */

namespace open20\amos\core\views;

use open20\amos\core\views\common\BaseListView;
use Yii;
use yii\helpers\Html;

class CalendarView extends BaseListView
{
    public $events               = [];

    /**
     * E.g.: \yii\helpers\Url::to(['/events/event/jsoncalendar'])
     * In the controller the action will be for example:
     * ```php
     * public function actionJsoncalendar($start = NULL, $end = NULL, $_ = NULL)
     * {
     *         \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
     *
     *         $startF = date('Y-m-d', strtotime($start));
     *         $endF   = date('Y-m-d', strtotime($end));
     *         $agenda = Agenda::find()->andWhere(new \yii\db\Expression("DATE(data_ora_inizio) >= '$startF'
     *          AND DATE(data_ora_fine) <= '$endF'"))->all();
     *
     *         $events = [];
     *
     *         foreach ($agenda AS $time) {
     *
     *             $Event        = new \yii2fullcalendar\models\Event();
     *             $Event->id    = $time->id;
     *             $Event->title = $time->titolo;
     *             $Event->start = $time->data_ora_inizio;
     *             $Event->end   = $time->data_ora_fine;
     *             $Event->url   = '/events/event/view?id='.$time->id;
     *             $events[]     = $Event;
     *         }
     *
     *         return $events;
     *     }
     * ```
     * @var string $ajaxEvents
     */
    public $ajaxEvents;
    public $titolo               = null;
    public $intestazione         = null; //contenuti html caricati ad inizio pagina prima del calendario - intestazioni o altro
    public $replace              = [];
    public $array                = false; //se array è settato verrà ignorato $eventConfig e si userà solo $getEventi
    public $getEventi            = 'getEvents';
    public $layout               = "{summary}\n{items}\n{pager}";
    public $disablePagination    = true;
    public $defaultClientOptions = [
    ];
    public $clientOptions        = [
    ];
    public $eventConfig          = [
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
        if ($this->disablePagination == true) {
            $this->dataProvider->setPagination(false);
        }
        $models = $this->dataProvider->getModels();
        $keys   = $this->dataProvider->getKeys();

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
        if (empty($this->ajaxEvents)) {
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
        }
        return $events;
    }

    public function run()
    {
        $intestazione = $this->intestazione; //contenuti html caricati ad inizio pagina prima del calendario
        $content      = $this->renderItems(); //contenuti caricati in fondo alla pagina legati al model come la legenda per esempio
        $options      = $this->itemOptions;
        return Html::tag('div', $intestazione).AmosFullCalendar::widget([
                'options' => $this->getClientOptions(),
                'clientOptions' => $this->getClientOptions(),
                'events' => (empty($this->ajaxEvents) ? $this->getEvents() : $this->ajaxEvents),
            ]).Html::tag('div', $content, $options);
    }

    /**
     * Renders all data models.
     * @return string the rendering result
     */
    public function renderItems()
    {
        if ($this->disablePagination == true) {
            $this->dataProvider->setPagination(false);
        }
        $models  = $this->dataProvider->getModels();
        $keys    = $this->dataProvider->getKeys();
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