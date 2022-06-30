<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms
 * @category   CategoryName
 */

namespace open20\amos\core\forms;

use open20\amos\admin\AmosAdmin;
use open20\amos\admin\models\UserProfile;
use open20\amos\admin\widgets\UserCardWidget;
use open20\amos\core\helpers\Html;
use open20\amos\core\interfaces\OrganizationsModelInterface;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\module\Module;
use open20\amos\core\record\Record;
use open20\amos\cwh\models\CwhNodi;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

/**
 * Class ItemAndCardHeaderWidget
 *
 * Widget to display the header in list view, icon view, item view and card view.
 * The interaction menu has three default buttons: favourite, signal and share.
 * If you want to enable only a certain group of buttons you must set interactionMenuButtons array with only the enabled buttons.
 * If you want to disable only a certain group of buttons you must set interactionMenuButtonsHide array with only the disabled buttons.
 *
 * @package open20\amos\core\forms
 */
class ItemAndCardHeaderWidget extends Widget
{
    /**
     * @var string $layout Widget view
     */
    public $layout = "@vendor/open20/amos-core/forms/views/widgets/item_and_card_header_widget.php";

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

    /**
     * @var bool $showPrevalentPartnershipAndTargets
     */
    public $showPrevalentPartnershipAndTargets = false;

     /**
     * @var bool $showPrevalentPartnership
     */
    public $showPrevalentPartnership = false;

    /**
     * @var string $customContent
     */
    public $customContent = null;

    /**
     * @var bool $truncateLongWords
     */
    public $truncateLongWords = false;

    /**
     * @var bool $absoluteUrlAvatar
     */
    public $absoluteUrlAvatar = false;

    /**
     * @var bool $checkReadPermissionForUserLink If true check if the logged user can access the view of the content creator. If false the view link is always enabled.
     */
    public $checkReadPermissionForUserLink = true;

    /**
     * @var bool $enableLink If true enable links on creator avatar and name.
     */
    public $enableLink = true;

    /**
     * @var bool $hideCreatorNameSurname If true hide the name and surname of the content creator.
     */
    public $hideCreatorNameSurname = false;

    /**
     * @var string $customCreatorAvatarUrl Custom creator avatar url.
     */
    public $customCreatorAvatarUrl = null;
    
    /**
     * @var array $creatorLinkOptions
     */
    public $creatorAvatarLinkOptions = [];
    
    /**
     * @var array $creatorLinkOptions
     */
    public $creatorNameLinkOptions = [];

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
        if (is_null($this->_contentCreator)) {
            $this->_contentCreator = UserProfile::findOne(['user_id' => 1]);
        }
    }

    /**
     * @return bool
     */
    public function getPublicationDateNotPresent()
    {
        return $this->_publicationDateNotPresent;
    }

    /**
     * @param bool $publicationDateNotPresent
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
        $targetsCollection = \open20\amos\cwh\models\CwhNodi::findAll([
            'id' => $nodes
        ]);

        $targetArr = [];
        /** @var CwhNodi $target */
        foreach ($targetsCollection as $target) {
            $targetString = "";
//            if(array_key_exists('open20\amos\community\models\CommunityContextInterface', class_implements($this->findNode($target)))){
//                $targetString .= Module::t('amoscore', 'Community') . ' ';
//            }
//            if(array_key_exists('open20\amos\core\interfaces\OrganizationsModelInterface', class_implements($this->findNode($target)))){
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
        $validatorsCollection = \open20\amos\cwh\models\CwhNodi::findAll([
            'id' => $validators
        ]);

        $validatorsArr = [];
        /** @var CwhNodi $target */
        foreach ($validatorsCollection as $singleValidator) {
            if (!(strpos($singleValidator->id, 'user') !== false)) {
                $targetString = "";
                if (array_key_exists('open20\amos\community\models\CommunityContextInterface', class_implements($this->findNode($singleValidator)))) {
                    $targetString .= Module::t('amoscore', '#item_card_header_widget_from_community') . ' ';
                }
                if (array_key_exists('open20\amos\core\interfaces\OrganizationsModelInterface', class_implements($this->findNode($singleValidator)))) {
                    $targetString .= Module::t('amoscore', '#item_card_header_widget_from_organization') . ' ';
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
        $validatorsCollection = \open20\amos\cwh\models\CwhNodi::findAll([
            'id' => $validators
        ]);

        $validatorsArr = [];
        /** @var CwhNodi $target */
        foreach ($validatorsCollection as $singleValidator) {
            if (!(strpos($singleValidator->id, 'user') !== false)) {
                $modelClass = \Yii::createObject($singleValidator['classname']);
                $model = $modelClass->findOne($singleValidator['record_id']);
                $targetString = "";
                if (array_key_exists('open20\amos\community\models\CommunityContextInterface', class_implements($model))) {
                    $targetString .= Module::t('amoscore', 'community') . ' ';
                }
                if (array_key_exists('open20\amos\core\interfaces\OrganizationsModelInterface', class_implements($model))) {
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
                $targetString = $validatorName;
            }
        }

        $contentToRender = [
            'contentCreatorAvatar' => $this->makeContentCreatorAvatar(),
            'contentCreatorNameSurname' => $this->retrieveUserNameAndSurname(),
            'contentCreator' => $this->_contentCreator,
            'hideInteractionMenu' => $this->isHideInteractionMenu(),
            'interactionMenuButtons' => $this->getInteractionMenuButtons(),
            'interactionMenuButtonsHide' => $this->getInteractionMenuButtonsHide(),
            'publicatonDate' => $this->makePublicationDate(),
            'model' => $this->getModel(),
            'customContent' => $this->customContent,
            
        ];
        if ($this->showPrevalentPartnership) {
            $contentToRender = array_merge($contentToRender, [
                'contentPrevalentPartnership' => $this->retrievePrevalentPartnership() != "" ? $this->retrievePrevalentPartnership() : null
            ]);
        }
        else if ($this->showPrevalentPartnershipAndTargets) {
            $contentToRender = array_merge($contentToRender, [
                'contentPrevalentPartnership' => $this->retrievePrevalentPartnership() != "" ? $this->retrievePrevalentPartnership() : null,
                'contentCreatorTargets' => $targetString
            ]);
        }
        else{};

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
            /** @var AmosAdmin $moduleAdmin */
            $moduleAdmin = AmosAdmin::instance();
            if (!empty($moduleAdmin)) {
                $userCardWidgetConf = [
                    'model' => $this->_contentCreator,
                    'absoluteUrl' => $this->absoluteUrlAvatar
                ];
                if (property_exists(UserCardWidget::className(), 'enableLink')) {
                    $userCardWidgetConf['enableLink'] = $this->creatorLinkEnabled();
                }
                if ($this->customCreatorAvatarUrl) {
                    $userCardWidgetConf['customUserAvatarUrl'] = $this->customCreatorAvatarUrl;
                } else if (isset(\Yii::$app->params['customContentCreatorAvatarUrl']) && \Yii::$app->params['customContentCreatorAvatarUrl']) {
                    $userCardWidgetConf['customUserAvatarUrl'] = \Yii::$app->params['customContentCreatorAvatarUrl'];
                }
                if ($this->creatorAvatarLinkOptions) {
                    $userCardWidgetConf['creatorLinkOptions'] = $this->creatorAvatarLinkOptions;
                } else if (isset(\Yii::$app->params['itemCardWidgetCreatorAvatarLinkOptions']) && \Yii::$app->params['itemCardWidgetCreatorAvatarLinkOptions']) {
                    $userCardWidgetConf['creatorLinkOptions'] = \Yii::$app->params['itemCardWidgetCreatorAvatarLinkOptions'];
                }
                $html = UserCardWidget::widget($userCardWidgetConf);
            } else {
                if ($this->customCreatorAvatarUrl) {
                    $avatarUrl = $this->customCreatorAvatarUrl;
                } else if (isset(\Yii::$app->params['customContentCreatorAvatarUrl']) && \Yii::$app->params['customContentCreatorAvatarUrl']) {
                    $avatarUrl = \Yii::$app->params['customContentCreatorAvatarUrl'];
                } else if ($this->absoluteUrlAvatar) {
                    $avatarUrl = $this->_contentCreator->getAvatarWebUrl();
                } else {
                    $avatarUrl = $this->_contentCreator->getAvatarUrl();
                }
                $defaultLinkOptions = [
                    'title' => $this->getContentCreatorLinkTitle()
                ];
                if ($this->creatorAvatarLinkOptions) {
                    $linkOptions = ArrayHelper::merge($defaultLinkOptions, $this->creatorAvatarLinkOptions);
                } else if (isset(\Yii::$app->params['itemCardWidgetCreatorAvatarLinkOptions']) && \Yii::$app->params['itemCardWidgetCreatorAvatarLinkOptions']) {
                    $linkOptions = ArrayHelper::merge($defaultLinkOptions, \Yii::$app->params['itemCardWidgetCreatorAvatarLinkOptions']);
                } else {
                    $linkOptions = $defaultLinkOptions;
                }
                $avatar = Html::img($avatarUrl, ['width' => '50', 'class' => 'avatar']);
                if ($this->creatorLinkEnabled()) {
                    $html .= Html::a(
                        $avatar,
                        $this->getCreatorLink(),
                        $linkOptions
                    );
                } else {
                    $html .= $avatar;
                }
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
        return BaseAmosModule::t('amoscore', 'Apri il profilo di {user_profile_name}', ['user_profile_name' => ($this->truncateLongWords ? $this->getContentCreator()->__toString() : $this->getContentCreator()->getNomeCognome())]);
    }

    /**
     * @return UserProfile
     */
    public function getContentCreator()
    {
        return $this->_contentCreator;
    }

    /**
     * @return bool
     */
    public function creatorLinkEnabled()
    {
        if (!$this->enableLink || (isset(\Yii::$app->params['disableLinkContentCreator']) && (\Yii::$app->params['disableLinkContentCreator'] === true))) {
            return false;
        }
        $contentCreatorUserProfile = $this->getContentCreator();
        return (!$this->checkReadPermissionForUserLink || (\Yii::$app instanceof \yii\console\Application) || \Yii::$app->user->can('USERPROFILE_READ', $contentCreatorUserProfile));
    }

    /**
     * @return string
     */
    public function getCreatorLink()
    {
        if (!$this->creatorLinkEnabled()) {
            return null;
        }
        $contentCreatorUserProfile = $this->getContentCreator();
        if ($this->absoluteUrlAvatar) {
            return \Yii::$app->getUrlManager()->createAbsoluteUrl($contentCreatorUserProfile->getFullViewUrl());
        } else {
            return $contentCreatorUserProfile->getFullViewUrl();
        }
    }

    /**
     * @return string
     */
    public function getCreator($contentCreatorNameSurname)
    {
        if ($this->creatorLinkEnabled()) {
            $defaultLinkOptions = [
                'title' => $this->getContentCreatorLinkTitle()
            ];
            if ($this->creatorNameLinkOptions) {
                $linkOptions = ArrayHelper::merge($defaultLinkOptions, $this->creatorNameLinkOptions);
            } else if (isset(\Yii::$app->params['itemCardWidgetCreatorNameLinkOptions']) && \Yii::$app->params['itemCardWidgetCreatorNameLinkOptions']) {
                $linkOptions = ArrayHelper::merge($defaultLinkOptions, \Yii::$app->params['itemCardWidgetCreatorNameLinkOptions']);
            } else {
                $linkOptions = $defaultLinkOptions;
            }
            return Html::a($contentCreatorNameSurname, $this->getCreatorLink(), $linkOptions);
        } else {
            return Html::tag('strong', $contentCreatorNameSurname);
        }
    }

    /**
     * This method creates a string that contains the name and surname of the user whose ID is contained in the parameter.
     * @return string
     */
    private function retrieveUserNameAndSurname()
    {
        if ($this->hideCreatorNameSurname || (isset(\Yii::$app->params['hideListsContentCreatorName']) && (\Yii::$app->params['hideListsContentCreatorName'] === true))) {
            return '';
        }
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
