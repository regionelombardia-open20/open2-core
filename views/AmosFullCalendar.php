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

use yii\web\View;

class AmosFullCalendar extends \yii2fullcalendar\yii2fullcalendar
{

    /**
     * Registers the FullCalendar javascript assets and builds the requiered js  for the widget and the related events
     */
    protected function registerPlugin()
    {
        $id   = $this->options['id'];
        $view = $this->getView();

        $moduleL = \Yii::$app->getModule('layout');
        if (!empty($moduleL)) {
            /** @var \yii\web\AssetBundle $assetClass */
            $assets = \open20\amos\layout\assets\AmosCalendarCoreAsset::register($view);
        } else {
            /** @var \yii\web\AssetBundle $assetClass */
            $assets = \open20\amos\core\views\assets\AmosCalendarCoreAsset::register($view);
        }

        //by default we load the jui theme, but if you like you can set the theme to false and nothing gets loaded....
        if ($this->theme == true) {
            \yii2fullcalendar\ThemeAsset::register($view);
        }

        if (isset($this->options['lang'])) {
            $assets->language = $this->options['lang'];
        }

        if ($this->googleCalendar) {
            $assets->googleCalendar = $this->googleCalendar;
        }

        $js = array();

        if ($this->ajaxEvents != NULL) {
            $this->clientOptions['events'] = $this->ajaxEvents;
        }

        if (is_array($this->header) && isset($this->clientOptions['header'])) {
            $this->clientOptions['header'] = array_merge($this->header, $this->clientOptions['header']);
        } else {
            $this->clientOptions['header'] = $this->header;
        }

        $cleanOptions = $this->getClientOptions();
        $js[]         = "jQuery('#$id').fullCalendar($cleanOptions);";

        ///////////////////////////
        // TODO Remove this section when WEEK view will be fixed
        // $js[] = "jQuery('.fc-agendaWeek-button').hide();";
        // $js[] = "jQuery('.fc-month-button').addClass('fc-corner-right');";
        ///////////////////////////

        /**
         * Loads events separately from the calendar creation. Uncomment if you need this functionality.
         *
         * lets check if we have an event for the calendar...
         * if(count($this->events)>0)
         * {
         *    foreach($this->events AS $event)
         *    {
         *        $jsonEvent = Json::encode($event);
         *        $isSticky = $this->stickyEvents;
         *        $js[] = "jQuery('#$id').fullCalendar('renderEvent',$jsonEvent,$isSticky);";
         *    }
         * }
         */
        $view->registerJs(implode("\n", $js), View::POS_READY);
    }
}