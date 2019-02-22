<?php

namespace lispa\amos\core\forms\editors\socialShareWidget;


use lispa\amos\core\components\ConfiguratorSocialShare;
use lispa\amos\core\exceptions\AmosException;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\interfaces\ContentModelInterface;
use yii\base\Exception;
use yii\base\Widget;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use ymaker\social\share\widgets\SocialShare;

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

    const MODE_NORMAL = 'normal';
    const MODE_DROPDOWN = 'dropdown';


    public $wrapperTag = 'div';
    public $wrapperOptions = ['class' => 'container-social-share'];

    public $linkWrapperTag = 'div';
    public $linkWrapperOptions = ['class' => 'share-wrap-button'];


    public $enableModalShare = true;
    public $mode = self::MODE_NORMAL;




    public function init()
    {
        if(!empty(\Yii::$app->components[$this->configuratorId])) {
            parent::init();
            if (empty($this->imageUrl)) {
                $this->imageUrl = \yii\helpers\Url::to(\Yii::$app->params['platform']['backendUrl'] . "/img/img_default.jpg");
            }
        }
    }

    /**
     *
     */
    public function run()
    {
        if(!empty(\Yii::$app->components[$this->configuratorId])) {
            $this->setVisibleSocialNetwork();
            $this->renderModalAjax(); //used for share on your own network

            if (!$this->isSocialNetworkEmpty()) {
                $this->renderModal();
                if ($this->mode == self::MODE_NORMAL) {
                    parent::run();
                } else {
                    // SHARE INSIDE A DROPDOWN
                    echo "<div class=\"dropdown socialshared-dropdown\">
                    <a class=\"btn dropdown-toggle\" data-toggle=\"dropdown\" href=\"\" aria-expanded=\"true\" title=\"" . \Yii::t('amoscore', 'Social share') . " \">"
                        . AmosIcons::show('share', ['class' => ''])
                        . Html::tag('b', '', ['class' => 'caret'])
                        . "</a><ul class=\"dropdown-menu\"> ";
                    parent::run();
                    echo "</ul></div>";
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
                var icon = $(this).find('span');

                if($(icon).attr('class') != 'am am-email' && !$(icon).hasClass( 'open-modal' )) {
                    e.preventDefault();
                    window.open($(this).attr('href'), 'share', 'height=450, width=550, top=' + ($(window).height() / 2 - 275) + ', left=' + ($(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
                    return false;
                }
                $(this).removeAttr('target');
            });
        });");
        }
    }

    /**
     * @return bool
     */
    public function canShare(){
        $canShare = true;
        $model = $this->model;
        $cwhModule = \Yii::$app->getModule('cwh');
        // if is visible to all logged user, you can share on socials
        if(!empty($cwhModule) && $model instanceof ContentModelInterface && in_array(get_class($model), $cwhModule->modelsEnabled)) {
            $destinatari = $model->destinatari;
            if(!empty($destinatari))
            {
                foreach ($destinatari as $regola)
                {
                    $cwh_nodi = \lispa\amos\cwh\models\CwhNodi::findOne($regola);
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
    protected function setVisibleSocialNetwork(){
        $allowedSocialNetwork = [];
        $canShare = $this->canShare();
            $socialNetworks = $this->_configurator->getSocialNetworks();
            foreach ($socialNetworks as $socialName => $social) {
                $okModule = true;
                //check if the platform has che required plugin
                if(!empty($social['required_module'])){
                    $module = \Yii::$app->getModule($social['required_module']);
                    if(empty($module)){
                        $okModule = false;
                    }
                }
                if (!empty($social['visibility']) && $social['visibility'] == ConfiguratorSocialShare::VISIBILITY_ALWAYS && $okModule){
                    $allowedSocialNetwork[$socialName] = $social;
                }
                elseif ($canShare && $okModule){
                    $allowedSocialNetwork[$socialName] = $social;
                }

            }

        $this->_configurator->socialNetworks = $allowedSocialNetwork;
    }

    /**
     * @return bool
     */
    protected function isSocialNetworkEmpty(){
        $socialNetworks = $this->_configurator->getSocialNetworks();
        if(empty($socialNetworks)){
            return true;
        }
        return false;
    }

    /**
     *  render the modal for sharing with your network
     */
    public function renderModalAjax(){
        $moduleChat = \Yii::$app->getModule('chat');
        if($moduleChat) {
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

}