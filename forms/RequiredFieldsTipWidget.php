<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms
 * @category   CategoryName
 */

namespace lispa\amos\core\forms;

use lispa\amos\core\helpers\Html;
use lispa\amos\core\module\BaseAmosModule;
use yii\base\Widget;

/**
 * Class RequiredFieldsTipWidget
 * @package lispa\amos\core\forms
 */
class RequiredFieldsTipWidget extends Widget
{
    /**
     * @var string $layout
     */
    public $layout = '{requiredTip}';

    /**
     * @var string $containerClasses
     */
    public $containerClasses = 'col-xs-12 note_asterisk nop';

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = preg_replace_callback("/{\\w+}/", function ($matches) {
            $content = $this->renderSection($matches[0]);
            return $content === false ? $matches[0] : $content;
        }, $this->layout);
        return $content;
    }

    /**
     * Renders a section of the specified name.
     * If the named section is not supported, false will be returned.
     * @param string $name the section name, e.g., `{summary}`, `{items}`.
     * @return string|boolean the rendering result of the section, or false if the named section is not supported.
     */
    public function renderSection($name)
    {
        switch ($name) {
            case '{requiredTip}':
                return $this->renderTip();
            default:
                return false;
        }
    }

    /**
     * Render the tip.
     * @return string
     */
    public function renderTip()
    {
        $tip = Html::beginTag('div', ['class' => $this->containerClasses]);
        $tip .= Html::tag('p', BaseAmosModule::t('amoscore', 'The fields marked with * are required.'));
        $tip .= Html::endTag('div');
        return $tip;
    }
}
