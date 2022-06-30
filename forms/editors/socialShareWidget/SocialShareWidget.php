<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\editors\socialShareWidget
 * @category   CategoryName
 */

namespace open20\amos\core\forms\editors\socialShareWidget;

use open20\amos\core\components\ConfiguratorSocialShare;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\interfaces\ContentModelInterface;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use ymaker\social\share\widgets\SocialShare;

/**
 * Class SocialShareWidget
 * @package open20\amos\core\forms\editors\socialShareWidget
 */
class SocialShareWidget extends SocialShare
{
//    public $containerOptions = [
//        'tag' => 'div',
//        'class' => 'container-social-share'
//    ];
//    public $linkContainerOptions = [
//        'tag' => 'div',
//        'class' => 'share-wrap-button'
//    ];

    public $model;

    public $configuratorId;

    const MODE_NORMAL = 'normal';
    const MODE_DROPDOWN = 'dropdown';


    public $containerOptions = ['tag' => 'div', 'class' => 'container-social-share'];

    public $linkContainerOptions = ['tag' => 'div', 'class' => 'share-wrap-button'];
    


    public $enableModalShare = true;
    public $mode = self::MODE_NORMAL;

    
    /**
     * @inheritdoc
     */
    public function init()
    {
        if(!empty($this->configuratorId)){
            $this->configurator = $this->configuratorId;
        }
        $baseUrl = (!empty(\Yii::$app->params['platform']['backendUrl']) ? \Yii::$app->params['platform']['backendUrl'] : '');
        if (!empty(\Yii::$app->components[$this->configurator])) {
            parent::init();
            if (empty($this->imageUrl)) {
                $this->imageUrl = \yii\helpers\Url::to($baseUrl . "/img/img_default.jpg");
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (isset(\Yii::$app->params['disableSocialShare']) && (\Yii::$app->params['disableSocialShare'] === true)) {
            return '';
        }

        $content_id = $this->model->id;
        if($this->model->getValidatedOnce()) {
            if (!empty(\Yii::$app->components[$this->configurator])) {
                $this->setVisibleSocialNetwork();
                $this->renderModalAjax(); //used for share on your own network

                if (!$this->isSocialNetworkEmpty()) {
                    $this->renderModal();
                    if ($this->mode == self::MODE_NORMAL) {
                        parent::run();
                    } else {
                        // SHARE INSIDE A DROPDOWN
                        echo "<div class=\"dropdown socialshared-dropdown\">
                    <a class=\"btn dropdown-toggle\" data-toggle=\"dropdown\" href=\"\" aria-expanded=\"true\" title=\"" . \Yii::t('amoscore', 'Share') . " \">"
                            . AmosIcons::show('share', ['class' => ''])
                            . Html::tag('b', '', ['class' => 'caret'])
                            . "</a><ul class=\"dropdown-menu\"> ";
                        parent::run();
                        echo "</ul></div>"
                            . "<div hidden id='social-share-model' data-classname='' data-key='$content_id'></div>";
                    }
                }
            }
        }
    }

    /**
     * Render Modal for sharing with social
     */
    public function renderModal()
    {
        if ($this->enableModalShare) {
            $view = $this->getView();
            $view->registerJs("$(document).ready(function() {
            $('.social-network').click(function(e) {
                e.preventDefault();
                var icon = $(this).find('span');
                var href = $(this).attr('href');
                var idModel = $('#social-share-model').attr('data-key');
                var clickedButton = this;
                
                $.ajax({
                  url: 'share-ajax?id='+idModel,
                }).done(function() {
                         if($(icon).attr('class') != 'am am-email' && !$(icon).hasClass( 'open-modal' )) {
                         e.preventDefault();
                        window.open(href, 'share', 'height=450, width=550, top=' + ($(window).height() / 2 - 275) + ', left=' + ($(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
                        return false;
                    }
                    else {
                        window.location.href = href;                    }
                    $(clickedButton).removeAttr('target');
                });
                
            });
        });");
        }
    }

    /**
     * @return bool
     */
    public function canShare()
    {
        $canShare = true;
        $model = $this->model;
        $cwhModule = \Yii::$app->getModule('cwh');
        // if is visible to all logged user, you can share on socials
        if (!empty($cwhModule) && $model instanceof ContentModelInterface && in_array(get_class($model), $cwhModule->modelsEnabled)) {
            $destinatari = $model->destinatari;
            if(!empty($destinatari))
            {
                /** @var  $regola */
                foreach ($destinatari as $regola)
                {

                    $cwh_nodi = \open20\amos\cwh\models\CwhNodi::findOne($regola);
                    // if the content is inside an OPEN type community,  you can share the content
                    if(!empty($cwh_nodi)){
                        $ClassnameNetwork = $cwh_nodi->classname;
                        if($ClassnameNetwork == "open20\amos\community\models\Community") {
                            $modelNetwork = $ClassnameNetwork::findOne($cwh_nodi->record_id);
                            if ($modelNetwork && $modelNetwork->community_type_id == 1) {
                                return true;
                            }
                        }
                    }
                    if(!empty($cwh_nodi) && !$cwh_nodi->visibility ){
                        $canShare = false;
                    }
                }
            }
        }
        return $canShare;
    }

    /**
     * Remove the social share buttons that haven't the right visibility
     */
    protected function setVisibleSocialNetwork()
    {
        $allowedSocialNetwork = [];
        $canShare = $this->canShare();
        $socialNetworks = $this->configurator->getSocialNetworks();
        foreach ($socialNetworks as $socialName => $social) {
            $okModule = true;
            //check if the platform has che required plugin
            if (!empty($social['required_module'])) {
                $module = \Yii::$app->getModule($social['required_module']);
                if (empty($module)) {
                    $okModule = false;
                }
            }
            if (!empty($social['visibility']) && $social['visibility'] == ConfiguratorSocialShare::VISIBILITY_ALWAYS && $okModule) {
                $allowedSocialNetwork[$socialName] = $social;
            } elseif ($canShare && $okModule) {
                $allowedSocialNetwork[$socialName] = $social;
            }

        }

        $this->configurator->socialNetworks = $allowedSocialNetwork;
    }

    /**
     * @return bool
     */
    protected function isSocialNetworkEmpty()
    {
        $socialNetworks = $this->configurator->getSocialNetworks();
        if (empty($socialNetworks)) {
            return true;
        }
        return false;
    }

    /**
     *  render the modal for sharing with your network
     */
    public function renderModalAjax()
    {
        $moduleChat = \Yii::$app->getModule('chat');
        if ($moduleChat) {
            $classname = urlencode($this->model->className());
            $content_id = $this->model->id;

            $js = <<<JS

        $('.open-modal').click(function() {
             $('#modal-contacts-share').modal('show')
                .find('#modalContent')
                .load('/admin/user-profile-ajax/ajax-contact-list?classname=$classname&content_id=$content_id');
        });
       
JS;
            $this->getView()->registerJs($js);

            Modal::begin([
                'header' => \Yii::t('amoscore', 'Condividi con gli utenti della tua rete'),
                'headerOptions' => ['id' => 'modalHeader'],
                'id' => 'modal-contacts-share',
                'options' => [
                    'data-url' => Url::current(),
                    'data-content-class' => $this->model->className(),
                    'data-content-id' => $this->model->id
                ],
                'size' => 'modal-lg',
                //keeps from closing modal with esc key or by clicking out of the modal.
                // user must click cancel or X to close
                'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE]
            ]);
            echo "<div id='modalContent'></div>";
            Modal::end();
        }
    }

    public static function isContentShareable(){

    }
}
