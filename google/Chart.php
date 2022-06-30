<?php

namespace open20\amos\core\google;

use yii\base\Widget;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\View;

/**
 * An widget to wrap google chart for Yii Framework 2
 * by Scott Huang
 *
 * @since 0.2
 * @Xiamen China
 */
class Chart extends Widget
{
    public $message;

    /**
     * @var string $containerId the container Id to render the visualization to
     */
    public $containerId;

    /**
     * @var string $visualization the type of visualization -ie PieChart
     */
    public $visualization;

    /**
     * @var string $packages the type of packages, default is corechart
     */
    public $packages    = 'corechart, gauge';  // such as 'orgchart' and so on.
    public $loadVersion = "1"; //such as 1 or 1.1  Calendar chart use 1.1.  Add at Sep 16

    /**
     * @var array $data the data to configure visualization
     */
    public $data = array();

    /**
     * @var array $options additional configuration options
     */
    public $options = array();

    /**
     * @var string $scriptAfterArrayToDataTable additional javascript to execute after arrayToDataTable is called
     */
    public $scriptAfterArrayToDataTable = '';

    /**
     * @var string $scriptAfterArrayToDataTable additional javascript to execute after chart is istantiated
     */
    public $scriptAfterChartInstantiate = '';

    /**
     * @var array $htmlOption the HTML tag attributes configuration
     */
    public $htmlOptions = array();

    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = 'Hello World';
        }
    }

    public function run()
    {

        $id = $this->getId();
        if (isset($this->options['id']) and ! empty($this->options['id'])) $id = $this->options['id'];
        // if no container is set, it will create one
        if ($this->containerId == null) {
            $this->htmlOptions['id'] = 'div-chart'.$id;
            $this->containerId       = $this->htmlOptions['id'];
            echo '<div '.Html::renderTagAttributes($this->htmlOptions).'></div>';
        }
        $this->registerClientScript($id);
        //return Html::encode($this->message);
    }

    /**
     * Registers required scripts
     */
    public function registerClientScript($id)
    {

        $jsData    = Json::encode($this->data);
        $jsOptions = Json::encode($this->options);

        $script = '
			google.setOnLoadCallback(drawChart'.$id.');
			var '.$id.'=null;
			function drawChart'.$id.'() {
				var data = google.visualization.arrayToDataTable('.$jsData.');

				'.$this->scriptAfterArrayToDataTable.'

				var options = '.$jsOptions.';

				'.$id.' = new google.visualization.'.$this->visualization.'(document.getElementById("'.$this->containerId.'"));
				'.$id.'.draw(data, options);
                                    
                                '.$this->scriptAfterChartInstantiate.'
			}';

        $view = $this->getView();
        $view->registerJsFile('https://www.google.com/jsapi', ['position' => View::POS_HEAD]);
        $view->registerJs('google.load("visualization", "'.$this->loadVersion.'", {packages:["'.$this->packages.'"]});',
            View::POS_HEAD, __CLASS__.'#'.$id);
        $view->registerJs($script, View::POS_HEAD, $id);
    }
}