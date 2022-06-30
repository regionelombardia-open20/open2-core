<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms
 * @category   CategoryName
 */

namespace open20\amos\core\forms;

use open20\amos\core\interfaces\ContentSettingsMenuInterface;
use Yii;
use yii\base\Widget;

/**
 * Class ContentSettingsMenuWidget
 * @package open20\amos\core\forms
 */
class ContentSettingsMenuWidget extends Widget
{
    /**
     * @var string $view Widget view
     */
    public $layout = "{highlights}";

    /**
     * @var string $widgetView Widget view
     */
    public $widgetView = "@vendor/open20/amos-core/forms/views/widgets/content_settings_menu_widget";

    /**
     * @var string $mainDivClasses Standard widget classes. If is set in widget options, this variable is overwritten
     */
    public $mainDivClasses = 'btn btn-tools-primary dropdown-toggle';

    /**
     * @var boolean $atLeastOneElement If true there's at least one element to render
     */
    private $atLeastOneElement = false;

    /**
     * @var array $buttons All buttons to be rendered.
     */
    private $buttons = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->composeContextMenuButtons();

        if (!$this->atLeastOneElement) {
            return '';
        }

        return $this->render($this->widgetView, [
            'buttons' => $this->buttons,
            'mainDivClasses' => $this->mainDivClasses
        ]);
    }

    /**
     * This method create the buttons array. It contains the strings of html "a" tag ready to print in view.
     */
    private function composeContextMenuButtons()
    {
        preg_replace_callback("/{\\w+}/", function ($matches) {
            $content = $this->renderSection($matches[0]);
            return $content === false ? $matches[0] : $content;
        }, $this->layout);
    }

    /**
     * Renders a section of the specified name.
     * If the named section is not supported, false will be returned.
     * @param string $name the section name, e.g., `{summary}`, `{items}`.
     */
    public function renderSection($name)
    {
        switch ($name) {
            case '{highlights}':
                $this->highlightsMenuEntry();
                break;
        }
    }

    /**
     * Make entry for highlights module
     */
    private function highlightsMenuEntry()
    {
        $highlightsModule = Yii::$app->getModule('highlights');
        if (!is_null($highlightsModule) && ($highlightsModule instanceof ContentSettingsMenuInterface)) {
            /** @var \amos\highlights\Module $highlightsModule */
            $highlightsEntry = $highlightsModule->getContentSettingsMenuEntry();
            if (strlen($highlightsEntry) > 0) {
                $this->buttons[] = $highlightsEntry;
                $this->atLeastOneElement = true;
            }
        }
    }
}
