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

use open20\amos\core\controllers\CrudController;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

/**
 * Class DataProviderView
 * decorator for every view in amos
 * @package backend\components\views
 */
class DataProviderView extends Widget
{
    public $view;
    public $currentView;
    public $viewListClass = 'open20\amos\core\views\ListView';
    public $viewGridClass = 'open20\amos\core\views\AmosGridView';
    public $viewIconClass = 'open20\amos\core\views\IconView';
    public $viewMapClass = 'open20\amos\core\views\MapView';
    public $viewCalendarClass = 'open20\amos\core\views\CalendarView';
    public $viewGanttClass = 'open20\amos\core\views\GanttView';
    public $dataProvider;
    public $gridView;
    public $listView;
    public $iconView;
    public $mapView;
    public $calendarView;
    public $ganttView = [];
    public $availableViews = [];
    public $createNewBtnParams;
    public $forceCreateNewButtonWidget = false;

    /**
     * @var array $exportConfig Configurations to export data. DON'T SET IN Yii::$app->request->queryParams!
     */
    public $exportConfig;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $controller = \Yii::$app->controller;
        $appControllerIsCrudController = ($controller instanceof CrudController);

        // STANDARD EXPORT - It takes the columns configured in the grid view.
        if (isset($this->gridView) && $appControllerIsCrudController) {
            /** @var CrudController $controller */
            $columns = $this->gridView['columns'];
            if (isset($this->gridView['exportColumns'])) {
                $columns = $this->gridView['exportColumns'];
            }
            $controller->setGridViewColumns($columns);
        }

        // ADVANCED EXPORT - With this options the developer can configure the export for specific columns instead of the grid view columns.
        if (
            $appControllerIsCrudController &&
            isset($this->exportConfig) &&
            is_array($this->exportConfig) &&
            isset($this->exportConfig['exportEnabled']) &&
            is_bool($this->exportConfig['exportEnabled']) &&
            ($this->exportConfig['exportEnabled'] === true)
        ) {
            /** @var CrudController $controller */
            $columns = [];
            if (isset($this->exportConfig['exportColumns']) && is_array($this->exportConfig['exportColumns'])) {
                $columns = $this->exportConfig['exportColumns'];
            } elseif (isset($this->gridView['columns']) && is_array($this->gridView['columns'])) {
                $columns = $this->gridView['columns'];
            }
            $controller->setGridViewColumns($columns);
            $controller->exportConfig = $this->exportConfig;
            $queryParams = Yii::$app->request->getQueryParams();
            $queryParams['download'] = true;
            Yii::$app->request->setQueryParams($queryParams);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $viewClass = $this->{'view' . ucfirst(strtolower($this->currentView['name'])) . 'Class'};
        $viewParams = $this->{strtolower($this->currentView['name']) . 'View'};

        if(!empty($this->availableViews)){
            $params = [
                'availableViews' => $this->availableViews,
                'forceCreateNewButtonWidget' => $this->forceCreateNewButtonWidget,
                'currentView' => $this->currentView
            ];
            if(!is_null($this->createNewBtnParams)){
                $params = ArrayHelper::merge($params, ['createNewBtnParams' => $this->createNewBtnParams]);
            }
            echo $this->render('@vendor/open20/amos-layout/src/views/layouts/parts/list_toolbar', $params);
        }

        $view = ArrayHelper::merge([
            'class' => $viewClass,
            'dataProvider' => $this->getDataProvider(),
        ], $viewParams);

        $this->view = \Yii::createObject($view);

        return $this->view->run();
    }

    /**
     * @return mixed
     */
    public function getCurrentView()
    {
        return $this->currentView;
    }

    /**
     * @param mixed $currentView
     */
    public function setCurrentView($currentView)
    {
        $this->currentView = $currentView;
    }

    /**
     * @return string
     */
    public function getViewListClass()
    {
        return $this->viewListClass;
    }

    /**
     * @param string $viewListClass
     */
    public function setViewListClass($viewListClass)
    {
        $this->viewListClass = $viewListClass;
    }

    /**
     * @return string
     */
    public function getViewGridClass()
    {
        return $this->viewGridClass;
    }

    /**
     * @param string $viewGridClass
     */
    public function setViewGridClass($viewGridClass)
    {
        $this->viewGridClass = $viewGridClass;
    }

    /**
     * @return string
     */
    public function getViewIconClass()
    {
        return $this->viewIconClass;
    }

    /**
     * @param string $viewIconClass
     */
    public function setViewIconClass($viewIconClass)
    {
        $this->viewIconClass = $viewIconClass;
    }

    /**
     * @return mixed
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * @param mixed $dataProvider
     */
    public function setDataProvider($dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @return mixed
     */
    public function getGridView()
    {
        return $this->gridView;
    }

    /**
     * @param mixed $gridView
     */
    public function setGridView($gridView)
    {
        $this->gridView = $gridView;
    }

    /**
     * @return mixed
     */
    public function getListView()
    {
        return $this->listView;
    }

    /**
     * @param mixed $listView
     */
    public function setListView($listView)
    {
        $this->listView = $listView;
    }

    /**
     * @return mixed
     */
    public function getIconView()
    {
        return $this->iconView;
    }

    /**
     * @param mixed $iconView
     */
    public function setIconView($iconView)
    {
        $this->iconView = $iconView;
    }
}
