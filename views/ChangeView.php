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

use yii\base\InvalidConfigException;
use yii\bootstrap\BootstrapPluginAsset;
use yii\bootstrap\Dropdown;
use yii\helpers\Html;

/**
 * Class ChangeView
 * @package lispa\amos\core\views
 */
class ChangeView extends Dropdown
{
    public $views;

    public $encodeLabels = false;

    public $dropdownContainerOptions = [
        'class' => 'btn-group'
    ];

    public $dropdown;
    public $dropdownLabel;
    public $dropdownTag = 'div';
    public $dropdownOptions = [
        "class" => "btn btn-tools-primary dropdown-toggle",
        "role" => "button", "id" => "bk-btnChangeView",
        "data-toggle" => "dropdown", "aria-expanded" => "true"
    ];

    /**
     * @var array $options
     */
    public $options = [
        "class" => "dropdown-menu dropdown-menu-change-view dropdown-menu-left", "aria-labelledby" => "bk-btnChangeView", "role" => "menu"
    ];

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!isset($this->views)) {
            throw new InvalidConfigException("'views' option is required.");
        }
        foreach ($this->views as $view) {
            $this->items[] = $view;
        }
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run()
    {
        if (count($this->views) <= 1) {
            return '';
        }

        BootstrapPluginAsset::register($this->getView());
        $this->registerClientEvents();

        $buttonDropdown = $this->renderDropdown();

        //Parse Urls
        $this->parseItemsUrl();
        foreach ($this->items as &$item) {
            if ($item['name'] == 'calendar') {
                $item['url'] = str_replace('&download=1', '', $item['url']);
            }
        }

        $items = $this->renderItems($this->items, $this->options);
        $content = $buttonDropdown . $items;

        return Html::tag('div', $content, $this->dropdownContainerOptions);
    }

    /**
     * @return string
     */
    public function renderDropdown()
    {
        return Html::tag($this->dropdownTag, $this->dropdownLabel ?: $this->dropdown['label'], $this->dropdownOptions);
    }

    /**
     * Rebuild all urls with right query params
     */
    protected function parseItemsUrl()
    {
        foreach ($this->items as $k => $item) {
            if (isset($item['visible']) && !$item['visible']) {
                continue;
            }

            if (is_string($item)) {
                $lines[] = $item;
                continue;
            }

            $url = array_key_exists('url', $item) ? $this->parseUrlAndExtend($item['url']) : null;
            $item['url'] = array_key_exists('url', $item) ? $url : null;

            $this->items[$k] = $item;
        }
    }

    /**
     * Reformat url with merged query params
     * @param $url
     * @return mixed
     */
    protected function parseUrlAndExtend($url)
    {
        $parsedUrl = parse_url($url);
        $baseQuery = \Yii::$app->request->getQueryParams();
        $extraQuery = [];

        //Set new value
        if (!empty($parsedUrl['query']))
            parse_str($parsedUrl['query'], $extraQuery);

        $query = array_merge($baseQuery, $extraQuery);

        $url = preg_replace('/\?.*/', '?' . http_build_query($query), $url);

        return $url;
    }
}
