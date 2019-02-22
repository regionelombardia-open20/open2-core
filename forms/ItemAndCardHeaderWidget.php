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

use lispa\amos\admin\models\UserProfile;
use lispa\amos\admin\widgets\UserCardWidget;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\interfaces\OrganizationsModelInterface;
use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\module\Module;
use lispa\amos\core\record\Record;
use lispa\amos\cwh\models\CwhNodi;
use yii\base\Widget;

/**
 * Class ItemAndCardHeaderWidget
 *
 * Widget to display the header in list view, icon view, item view and card view.
 * The interaction menu has three default buttons: favourite, signal and share.
 * If you want to enable only a certain group of buttons you must set interactionMenuButtons array with only the enabled buttons.
 * If you want to disable only a certain group of buttons you must set interactionMenuButtonsHide array with only the disabled buttons.
 *
 * @package lispa\amos\core\forms
 */
class ItemAndCardHeaderWidget extends Widget
{
    /**
     * @var string $layout Widget view
     */
    public $layout = "@vendor/lispa/amos-core/forms/views/widgets/item_and_card_header_widget.php";

    /**
     * @var Record $model
     */
    private $_model = null;

    /**
     * @var bool $_publicationDateField Model field that contains the publication date.
     */
    private $_publicationDateField = null;

    /**
     * @var bool $_publicationDateNotPresent If true skip the render of the publication date.
     */
    private $_publicationDateNotPresent = false;

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
     * @var UserProfile $_contentCreator The object that contains the profile of the content creator.
     */
    private $_contentCreator = null;

    public $showPrevalentPartnershipAndTargets = false;

    public $customContent = null;

    public $truncateLongWords = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (is_null($this->_model)) {
            throw new \Exception(BaseAmosModule::t('amoscore', 'Model mancante'));
        }

        if (!$this->getPublicationDateNotPresent() && !$this->isHideInteractionMenu() && (is_null($this->_publicationDateField)
                || !is_string($this->_publicationDateField) || !strlen($this->_publicationDateField))) {
            throw new \Exception(BaseAmosModule::t('amoscore',
                'Variabile contenente il nome del campo della data di pubblicazione del contenuto mancante o non settata correttamente'));
        }

        $this->_contentCreator = $this->_model->createdUserProfile;
    }

    /**
     * @return string
     */
    public function getPublicationDateNotPresent()
    {
        return $this->_publicationDateNotPresent;
    }

    /**
     * @param string $publicationDateNotPresent
     */
    public function setPublicationDateNotPresent($publicationDateNotPresent)
    {
        $this->_publicationDateNotPresent = $publicationDateNotPresent;
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
     * @return Record
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @param Record $model
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * @param array $Target
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    protected function findNode($Target)
    {
        $modelClass = \Yii::createObject($Target['classname']);
        $model = $modelClass->findOne($Target['record_id']);
        return $model;
    }

    /**
     * @param $nodes
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getNodesAsString($nodes)
    {
        $targetsCollection = \lispa\amos\cwh\models\CwhNodi::findAll([
            'id' => $nodes
        ]);

        $targetArr = [];
        /** @var CwhNodi $target */
        foreach ($targetsCollection as $target) {
            $targetString = "";
//            if(array_key_exists('lispa\amos\community\models\CommunityContextInterface', class_implements($this->findNode($target)))){
//                $targetString .= Module::t('amoscore', 'Community') . ' ';
//            }
//            if(array_key_exists('lispa\amos\core\interfaces\OrganizationsModelInterface', class_implements($this->findNode($target)))){
//                $targetString .= Module::t('amoscore', 'Organizzazione') . ' ';
//            }
            $targetArr[] = $targetString . $this->findNode($target)->toStringWithCharLimit(-1);
        }

        return implode(', ', $targetArr);
    }

    /**
     * @param $validators
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getValidatorName($validators)
    {
        $validatorsCollection = \lispa\amos\cwh\models\CwhNodi::findAll([
            'id' => $validators
        ]);

        $validatorsArr = [];
        /** @var CwhNodi $target */
        foreach ($validatorsCollection as $singleValidator) {
            if (!(strpos($singleValidator->id, 'user') !== false)) {
                $targetString = "";
                if (array_key_exists('lispa\amos\community\models\CommunityContextInterface', class_implements($this->findNode($singleValidator)))) {
                    $targetString .= Module::t('amoscore', 'community') . ' ';
                }
                if (array_key_exists('lispa\amos\core\interfaces\OrganizationsModelInterface', class_implements($this->findNode($singleValidator)))) {
                    $targetString .= Module::t('amoscore', 'organizzazione') . ' ';
                }
                $validatorsArr[] = $targetString . $this->findNode($singleValidator)->toStringWithCharLimit(-1);
            }
        }

        return implode(', ', $validatorsArr);
    }

    /**
     * @param $validators
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getValidatorNameGeneral($validators)
    {
        $validatorsCollection = \lispa\amos\cwh\models\CwhNodi::findAll([
            'id' => $validators
        ]);

        $validatorsArr = [];
        /** @var CwhNodi $target */
        foreach ($validatorsCollection as $singleValidator) {
            if (!(strpos($singleValidator->id, 'user') !== false)) {
                $modelClass = \Yii::createObject($singleValidator['classname']);
                $model = $modelClass->findOne($singleValidator['record_id']);
                $targetString = "";
                if (array_key_exists('lispa\amos\community\models\CommunityContextInterface', class_implements($model))) {
                    $targetString .= Module::t('amoscore', 'community') . ' ';
                }
                if (array_key_exists('lispa\amos\core\interfaces\OrganizationsModelInterface', class_implements($model))) {
                    $targetString .= Module::t('amoscore', 'organizzazione') . ' ';
                }
                $validatorsArr[] = $targetString . $model->toStringWithCharLimit(-1);
            }
        }

        return implode(', ', $validatorsArr);
    }


    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = '';
        $targets = '';

        try {
            $targets = $this->model->getTargets();
        } catch (\Exception $exception) {
            if ($this->model->hasProperty('destinatari')) {
                $targets = $this->model->destinatari;
            }
        }

        if ($targets) {
            $content = $this->getNodesAsString($targets);
        } else {
            $content .= "";
        }

        $targetString = null;
        if (!empty($this->model->validatori)) {
            $validatorName = $this->getValidatorName($this->model->validatori);
            if ($validatorName != "") {
                $targetString = Module::t('amoscore', 'dalla') . ' ' . $this->getValidatorName($this->model->validatori);
            }
        }

        $contentToRender = [
            'contentCreatorAvatar' => $this->makeContentCreatorAvatar(),
            'contentCreatorNameSurname' => $this->retrieveUserNameAndSurname(),
            'hideInteractionMenu' => $this->isHideInteractionMenu(),
            'interactionMenuButtons' => $this->getInteractionMenuButtons(),
            'interactionMenuButtonsHide' => $this->getInteractionMenuButtonsHide(),
            'publicatonDate' => $this->makePublicationDate(),
            'model' => $this->getModel(),
            'customContent' => $this->customContent,
        ];

        if ($this->showPrevalentPartnershipAndTargets) {
            $contentToRender = array_merge($contentToRender, [
                'contentPrevalentPartnership' => $this->retrievePrevalentPartnership() != "" ? $this->retrievePrevalentPartnership() : null,
                'contentCreatorTargets' => $targetString
            ]);
        }

        $contentToRender['widget'] = $this;

        return $this->renderFile($this->getLayout(), $contentToRender);
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * This method create the HTML to show the content creator avatar.
     * @return string
     */
    private function makeContentCreatorAvatar()
    {
        $html = '';
        if (!is_null($this->_contentCreator)) {
            $moduleAdmin = \Yii::$app->getModule('admin');
            if (!empty($moduleAdmin)) {
                if (property_exists(UserCardWidget::className(), 'enableLink')) {
                    $html = UserCardWidget::widget(['model' => $this->_contentCreator, 'enableLink' => true]);
                } else {
                    $html = UserCardWidget::widget(['model' => $this->_contentCreator]);
                }
            } else {
                $html .= Html::a(
                    Html::img($this->_contentCreator->getAvatarUrl(), ['width' => '50', 'class' => 'avatar']),
                    $this->_contentCreator->getFullViewUrl(),
                    ['title' => $this->getContentCreatorLinkTitle()]
                );
            }
        }
        return $html;
    }

    /**
     * This method returns the link title for the link to the user profile view.
     * @return string
     */
    public function getContentCreatorLinkTitle()
    {
        return BaseAmosModule::t('amoscore', 'Apri il profilo di {user_profile_name}', ['user_profile_name' => ($this->truncateLongWords ? $this->getContentCreator()-> __toString() : $this->getContentCreator()->getNomeCognome())]);
    }

    /**
     * @return UserProfile
     */
    public function getContentCreator()
    {
        return $this->_contentCreator;
    }

    /**
     * This method creates a string that contains the name and surname of the user whose ID is contained in the parameter.
     * @return string
     */
    private function retrieveUserNameAndSurname()
    {
        $nameSurname = BaseAmosModule::t('amoscore', 'Utente Cancellato');
        if (!is_null($this->_contentCreator)) {
            if ($this->truncateLongWords) {
                $nameSurname = $this->_contentCreator->__toString();
            } else {
                $nameSurname = $this->_contentCreator->getNomeCognome();
            }
        }
        return $nameSurname;
    }

    /**
     * This method creates a string that contains the prevalent partnership of the user whose ID is contained in the parameter.
     * @return string
     */
    private function retrievePrevalentPartnership()
    {
        $prevalentPartnershipName = "";
        if (!is_null($this->_contentCreator)) {
            $prevalentPartnership = $this->_contentCreator->prevalentPartnership;
            if (!is_null($prevalentPartnership) && ($prevalentPartnership instanceof OrganizationsModelInterface)) {
                $prevalentPartnershipName = $prevalentPartnership->getNameField();
            }
        }
        return $prevalentPartnershipName;
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
     * This method format the publication date field of the model ad a date. If the publication date is not present in the model returns an empty string.
     * @return string
     */
    private function makePublicationDate()
    {
        $publicationDate = '';
        if (!$this->getPublicationDateNotPresent()) {
            $publicationDateModelField = $this->getPublicationDateField();
            $publicationDate = \Yii::$app->getFormatter()->asDate($this->getModel()->{$publicationDateModelField});
        }
        return $publicationDate;
    }

    /**
     * @return string
     */
    public function getPublicationDateField()
    {
        return $this->_publicationDateField;
    }

    /**
     * @param string $publicationDateField
     */
    public function setPublicationDateField($publicationDateField)
    {
        $this->_publicationDateField = $publicationDateField;
    }
}
