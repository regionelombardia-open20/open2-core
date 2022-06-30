<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\components
 * @category   CategoryName
 */

namespace open20\amos\core\components;

use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\views\assets\CheckScopeAsset;
use open20\amos\core\widget\WidgetAbstract;
use Yii;
use yii\base\InvalidCallException;
use yii\base\ViewNotFoundException;
use yii\web\View;
use open20\amos\core\helpers\StringHelper;

/**
 * Class AmosView
 * @package open20\amos\core\components
 */
class AmosView extends View
{
    /**
     * @event Event an event that is triggered by [[beginViewContent()]].
     */
    const BEFORE_RENDER_CONTENT = 'BEFORE_RENDER_CONTENT';

    /**
     * @event Event an event that is triggered by [[endViewContent()]].
     */
    const AFTER_RENDER_CONTENT = 'AFTER_RENDER_CONTENT';

    /**
     * @var string set default pluginIcon
     */
    protected $pluginIcon = 'dash dash-linentita';

    /**
     * @var string pluginName
     */
    protected $pluginName = '';

    /**
     * @var string pluginColor
     */
    protected $pluginClassColor = '';

    /**
     * Marks the beginning of the HTML content section.
     */
    public function beginViewContent()
    {
        $this->trigger(self::BEFORE_RENDER_CONTENT);
    }

    /**
     * Marks the ending of the HTML content section.
     */
    public function endViewContent()
    {
        $this->trigger(self::AFTER_RENDER_CONTENT);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\web\Application) {
            CheckScopeAsset::register($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeRender($viewFile, $params)
    {
        if (Yii::$app instanceof \yii\web\Application) {
            $options = [
                'idScope' => (isset(Yii::$app->session['cwh-scope']['community']) ? Yii::$app->session['cwh-scope']['community']
                    : ''),
            ];
            $this->registerJs(
                "var yiiOptions = " . \yii\helpers\Json::htmlEncode($options) . ";", View::POS_HEAD, 'yiiOptions'
            );
        }

        if (!empty(\Yii::$app->params['dashboardEngine']) && \Yii::$app->params['dashboardEngine'] == WidgetAbstract::ENGINE_ROWS) {
            if (empty($this->pluginName) && !empty(\Yii::$app->controller->module->id)) {
                $moduleClass = Yii::$app->getModule(\Yii::$app->controller->module->id);
                if (!empty($moduleClass)) {

                    if (method_exists($moduleClass, 'getModuleName')) {
                        $this->setPluginName($moduleClass::getModuleName());
                    }
                }
            }
        }

        return parent::beforeRender($viewFile, $params);
    }

    /**
     * @return mixed|string get plugin icon
     */
    public function getPluginIcon()
    {
        return $this->pluginIcon;
    }

    /**
     * @param $icon
     * @return bool - set plugin icon
     */
    public function setPluginIcon($icon)
    {
        $this->pluginIcon = $icon;
        return true;
    }

    /**
     * @return mixed|string get plugin name
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * @param $name
     * @return bool - set plugin name
     */
    public function setPluginName($name)
    {
        $this->pluginName = BaseAmosModule::tHtml('amoscore', $name);
        return true;
    }

    /**
     * @return mixed|string get plugin color
     */
    public function getPluginColor()
    {
        return $this->pluginClassColor;
    }

    /**
     * @param $color
     * @return bool - set plugin color
     */
    public function setPluginColor($color)
    {
        $this->pluginClassColor = $color;
        return true;
    }

    /**
     * override Renders a view.
     * @param string $view the view name.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @param object $context the context to be assigned to the view and can later be accessed via [[context]]
     * in the view. If the context implements [[ViewContextInterface]], it may also be used to locate
     * the view file corresponding to a relative view name.
     * @return string the rendering result
     * @throws ViewNotFoundException if the view file does not exist.
     * @throws InvalidCallException if the view cannot be resolved.
     */
    public function render($view, $params = [], $context = null)
    {
        $view = $this->changeView($view);
        return parent::render($view, $params, $context);
    }

    /**
     * @param string $fileView
     * @return mixed|string
     */
    public function changeView($fileView)
    {
        $fileView = $this->cleanFullsize($fileView);

        if (!empty(\Yii::$app->params['dashboardEngine']) && \Yii::$app->params['dashboardEngine'] == WidgetAbstract::ENGINE_ROWS && strpos($fileView, DIRECTORY_SEPARATOR . 'fullsize' . DIRECTORY_SEPARATOR) === false) {

            if (strpos($fileView, DIRECTORY_SEPARATOR) !== 0 && strpos($fileView, '@') !== 0 && strpos($fileView, 'fullsize') === false) {
                return 'fullsize' . DIRECTORY_SEPARATOR . $fileView;
            }
            $views = explode(DIRECTORY_SEPARATOR, $fileView);
            $count = count($views);
            $end = end($views);
            $prev = array_splice($views, 0, $count - 1);
            array_push($prev, 'fullsize');
            array_push($prev, $end);
            $newViewFile = implode(DIRECTORY_SEPARATOR, $prev);

            if (file_exists($newViewFile)) {
                return $newViewFile;
            }
        }
        if ((strpos($fileView, DIRECTORY_SEPARATOR) === false || strpos($fileView, DIRECTORY_SEPARATOR) > 0) && strpos($fileView, '@') !== 0) {

            if (strpos($fileView, 'fullsize' . DIRECTORY_SEPARATOR . 'fullsize') !== false && !file_exists($fileView)) {
                $fileViewNew = str_replace('fullsize' . DIRECTORY_SEPARATOR . 'fullsize', 'fullsize', $fileView);

                return $fileViewNew;

            } else if (strpos($fileView, 'fullsize' . DIRECTORY_SEPARATOR) !== false && !file_exists($fileView)) {

                $fileViewNew = str_replace('fullsize' . DIRECTORY_SEPARATOR, '', $fileView);

                return $fileViewNew;

            }
        }
        if (strpos($fileView, 'fullsize' . DIRECTORY_SEPARATOR . 'fullsize') !== false && !file_exists($fileView)) {
            $fileViewNew = str_replace('fullsize' . DIRECTORY_SEPARATOR . 'fullsize', 'fullsize', $fileView);

            if (file_exists($fileViewNew)) {
                return $fileViewNew;
            }
        }
        if (strpos($fileView, 'fullsize' . DIRECTORY_SEPARATOR) !== false && !file_exists($fileView)) {

            $fileViewNew = str_replace('fullsize' . DIRECTORY_SEPARATOR, '', $fileView);

            if (file_exists($fileViewNew)) {
                return $fileViewNew;
            }
        }

        return $fileView;
    }

    /**
     * @param string $fileView
     * @param string $viewFile
     * @param array $params
     * @param null $context
     * @return string
     */
    public function renderFile($viewFile, $params = [], $context = null)
    {
        $viewFile = $this->changeView($viewFile);
        return parent::renderFile($viewFile, $params, $context);
    }

    /**
     * @param $path
     * @return string
     */
    private function cleanFullsize($path)
    {
        $array = explode(DIRECTORY_SEPARATOR, $path);
        $count = 0;
        foreach ($array as $k => $item) {
            if ($item == 'fullsize') {
                if ($count > 0) {
                    unset($array[$k]);
                } else {
                    $count++;
                }
            }
        }
        $newPath = implode(DIRECTORY_SEPARATOR, $array);
        return $newPath;
    }
    
    /**
     * Removes redundant whitespaces (>1) and new lines (>1).
     *
     * @param string $content input string
     * @return string compressed string
     */
    public function compress($content)
    {
        return StringHelper::minify($content);
    }

}
