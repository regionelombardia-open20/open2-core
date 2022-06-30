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

use open20\amos\core\icons\AmosIcons;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;
use yii\base\Widget;

/**
 * Class InteractionMenuWidget
 *
 * Widget to display the interaction menu.
 * It has three default buttons: favorite, signal and share.
 * If you want to enable only a certain group of buttons you must set interactionMenuButtons array with only the enabled buttons.
 * If you want to disable only a certain group of buttons you must set interactionMenuButtonsHide array with only the disabled buttons.
 *
 * @package open20\amos\core\forms
 */
class InteractionMenuWidget extends Widget
{
    /**
     * @var string $layout Widget view
     */
    public $layout = "@vendor/open20/amos-core/forms/views/widgets/interaction_menu_widget.php";

    /**
     * @var array $_defaultInteractionMenuButtons List of the default interaction menu buttons (favorite, signal and share).
     */
    private $_defaultInteractionMenuButtons = [
//        'favorite', // TODO Uncomment share when the functionality has been implemented
        'signal',
//        'share'   // TODO Uncomment share when the functionality has been implemented
    ];

    /**
     * @var Record $model - the current model
     */
    private $model = null;

    /**
     * @var bool $_hideInteractionMenu If true hide all interaction menu. Default to false.
     */
    private $_hideInteractionMenu = false;

    /**
     * @var array $_interactionMenuButtons List of the enabled buttons in the interaction menu. If not set, the default buttons will be displayed.
     */
    private $_interactionMenuButtons = [];

    /**
     * @var array $_interactionMenuButtonsHide List of the disabled buttons in the interaction menu. If not set, the default buttons will be displayed.
     */
    private $_interactionMenuButtonsHide = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->renderFile($this->getLayout(), [
            'hideInteractionMenu' => $this->isHideInteractionMenu(),
            'interactionMenuButtons' => $this->makeInteractionMenuButtonsHtml(),
        ]);
    }

    /**
     * @return Record
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Record $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return bool
     */
    public function isHideInteractionMenu()
    {
        return $this->_hideInteractionMenu;
    }

    /**
     * @param bool $hideInteractionMenu
     */
    public function setHideInteractionMenu($hideInteractionMenu)
    {
        $this->_hideInteractionMenu = $hideInteractionMenu;
    }

    /**
     * This method create the HTML that contains all the buttons to view in interaction menu.
     * @return string
     */
    private function makeInteractionMenuButtonsHtml()
    {
        $html = '';

        $interactionMenuButtons = $this->getInteractionMenuButtons();
        $interactionMenuButtonsHide = $this->getInteractionMenuButtonsHide();

        if (empty($interactionMenuButtons) && empty($interactionMenuButtonsHide)) {
            $html .= $this->makeInteractionMenuButtonsFromUserConf($this->_defaultInteractionMenuButtons);
        } elseif (!empty($interactionMenuButtons)) {
            $html .= $this->makeInteractionMenuButtonsFromUserConf($interactionMenuButtons);
        } elseif (empty($interactionMenuButtons) && !empty($interactionMenuButtonsHide)) {
            $interactionMenuButtonsToView = array_diff($this->_defaultInteractionMenuButtons,
                $interactionMenuButtonsHide);
            $html .= $this->makeInteractionMenuButtonsFromUserConf($interactionMenuButtonsToView);
        }

        return $html;
    }

    /**
     * @return array
     */
    public function getInteractionMenuButtons()
    {
        return $this->_interactionMenuButtons;
    }

    /**
     * @param array $interactionMenuButtons
     */
    public function setInteractionMenuButtons($interactionMenuButtons)
    {
        $this->_interactionMenuButtons = $interactionMenuButtons;
    }

    /**
     * @return array
     */
    public function getInteractionMenuButtonsHide()
    {
        return $this->_interactionMenuButtonsHide;
    }

    /**
     * @param array $interactionMenuButtonsHide
     */
    public function setInteractionMenuButtonsHide($interactionMenuButtonsHide)
    {
        $this->_interactionMenuButtonsHide = $interactionMenuButtonsHide;
    }

    /**
     * This method concat the HTML of each button set in the param array.
     * @param array $interactionMenuButtonsToView
     * @return string
     */
    private function makeInteractionMenuButtonsFromUserConf($interactionMenuButtonsToView)
    {
        $html = '';
        foreach ($interactionMenuButtonsToView as $interactionMenuButtonToView) {
            switch ($interactionMenuButtonToView) {
                case 'favorite':
                    $html .= $this->makeFavoriteButton();
                    break;
                case 'signal':
                    $html .= $this->makeSignalButton();
                    break;
                case 'share':
                    $html .= $this->makeShareButton();
                    break;

                default:
                    $html .= '';
                    break;
            }
        }
        return $html;
    }

    /**
     * This method make the HTML for the "favorite" interaction menu button.
     * @return string
     */
    private function makeFavoriteButton()
    {
        $button = '';
        $module = \Yii::$app->getModule('favorites');
        if (isset($module)) {
            $button = \open20\amos\favorites\widgets\FavoriteWidget::widget([
                'model' => $this->model
            ]);
        }
        return $button;
    }

    /**
     * This method make the HTML for the "signal" interaction menu button.
     * @return string
     */
    private function makeSignalButton()
    {
        $reportModule = \Yii::$app->getModule('report');
        if (isset($reportModule) && in_array($this->model->className(), $reportModule->modelsEnabled)) {
            if ($this->model->hasMethod('getTitle')) {
                $title = $this->model->getTitle();
            } else {
                $title = $this->model->__toString();
            }
            $button = \open20\amos\report\widgets\ReportWidget::widget([
                'modelClassName' => $this->model->className(),
                'context_id' => $this->model->id,
                'title' => $title
            ]);
        } else {
            $button = '';
        }
        return $button;
    }

    /**
     * This method make the HTML for the "share" interaction menu button.
     * @return string
     */
    private function makeShareButton()
    {
        $href = '#';
        $button = '<a href="' . $href . '" title="' . BaseAmosModule::t('amoscore',
                'Condividi') . '">' . AmosIcons::show("share", ["class" => "am-2"]) . '</a>';
        return $button;
    }
}
