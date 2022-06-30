<?php
/**
 * Aria S.p.A.
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

    const MODE_NORMAL   = 'normal';
    const MODE_DROPDOWN = 'dropdown';

    public $containerOptions     = ['tag' => 'div', 'class' => 'container-social-share'];
    public $linkContainerOptions = ['tag' => 'div', 'class' => 'share-wrap-button'];
    public $enableModalShare     = true;
    public $mode                 = self::MODE_NORMAL;
    public $quote;
    public $isProtected          = true;
    public $isComment            = false;
    public $isRedationalContent  = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setConfigurator();

        if ($this->isComment == false && $this->isProtected == false && $this->mode == self::MODE_NORMAL) {
            $this->containerOptions['style'] = $this->containerOptions['style'].'display: flex;
                flex-direction: row;
                align-items: flex-start;
                justify-content: flex-end;
                font-size: 2em;
                width: 100%;';
        } else if ($this->isProtected == false && $this->isComment == true && $this->mode == self::MODE_NORMAL) {
            $this->containerOptions['style'] = $this->containerOptions['style'].'display: flex;
                flex-direction: row;
                align-items: flex-start;
                justify-content: flex-end;
                font-size: 2em;
                width: 100%;';
        }
        $baseUrl = (!empty(\Yii::$app->params['platform']['backendUrl']) ? \Yii::$app->params['platform']['backendUrl'] : '');
        if (!empty(\Yii::$app->components[$this->configuratorId])) {
            parent::init();
            if (empty($this->imageUrl)) {
                $this->imageUrl = \yii\helpers\Url::to($baseUrl."/img/img_default.jpg");
            }
        }
    }

    public function setConfigurator()
    {
        if (!empty($this->configuratorId) && !empty(\Yii::$app->components[$this->configuratorId]['class'])) {
            $this->configurator = new \Yii::$app->components[$this->configuratorId]['class'];
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

        $methodExists = false;
        if (method_exists($this->model, 'getValidatedOnce')) {
            $methodExists = true;
        }

        if (($this->isRedationalContent == true) || ($methodExists && $this->model->getValidatedOnce()) || ($this->isComment
            == true)) {
            if (!empty(\Yii::$app->components[$this->configuratorId])) {
                $this->setVisibleSocialNetwork();
                $this->renderModalAjax(); //used for share on your own network

                if (!$this->isSocialNetworkEmpty()) {
                    $this->renderModal();
                    if ($this->mode == self::MODE_NORMAL) {
                        if ($this->isProtected == true && $this->isComment == true) {
                            echo '<style>'
                            .'.container-social-share{display: flex;
                                flex-direction: row;
                                align-items: flex-start;
                                justify-content: flex-end;
                                font-size: 2em;
                                width: 100%;}'
                            .'.container-social-share > .share-wrap-button + .share-wrap-button{ padding-left: 5px}'                           
                            .'</style>';
                        } else if ($this->isProtected == false) {
                            echo '<style>'
                            .'.container-social-share .share-wrap-button + .share-wrap-button {
                                padding-left: 5px;
                            }'
                            .'</style>';
                        }
                        parent::run();
                        echo "<div hidden id='social-share-model' data-classname='' data-key='$content_id'></div>";

                    } else {
                        // SHARE INSIDE A DROPDOWN
                        echo "<div class=\"dropdown socialshared-dropdown\">
                    <a class=\"btn dropdown-toggle\" data-toggle=\"dropdown\" href=\"\" aria-expanded=\"true\" title=\"".\Yii::t('amoscore',
                            'Share')." \">"
                        .AmosIcons::show('share', ['class' => ''])
                        .Html::tag('b', '', ['class' => 'caret'])
                        ."</a><ul class=\"dropdown-menu\"> ";
                        parent::run();
                        echo "</ul></div>"
                        ."<div hidden id='social-share-model' data-classname='' data-key='$content_id'></div>";
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
            $free = false;
            if ($this->isProtected == false) {
                $free = true;
            }
            $view = $this->getView();
            if ($free) {

                $view->registerJs("$(document).ready(function() {
            $('.social-network').click(function(e) {
                e.preventDefault();
                var icon = $(this).find('span');
                var href = $(this).attr('href');
                var idModel = $('#social-share-model').attr('data-key');
                var clickedButton = this;
                         if($(icon).attr('class') != 'am am-email' && !$(icon).hasClass( 'open-modal' )) {
                         e.preventDefault();
                        window.open(href, 'share', 'height=450, width=550, top=' + ($(window).height() / 2 - 275) + ', left=' + ($(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
                        return false;
                    }
            });
        });");
            } else {
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
    }

    /**
     * @return bool
     */
    public function canShare()
    {
        $canShare  = true;
        $model     = $this->model;
        $cwhModule = \Yii::$app->getModule('cwh');
        // if is visible to all logged user, you can share on socials
        if ($this->isProtected == true && !empty($cwhModule) && $model instanceof ContentModelInterface && in_array(get_class($model),
                $cwhModule->modelsEnabled)) {
            $destinatari = $model->destinatari;
            if (!empty($destinatari)) {
                /** @var  $regola */
                foreach ($destinatari as $regola) {

                    $cwh_nodi = \open20\amos\cwh\models\CwhNodi::findOne($regola);
                    // if the content is inside an OPEN type community,  you can share the content
                    if (!empty($cwh_nodi)) {
                        $ClassnameNetwork = $cwh_nodi->classname;
                        if ($ClassnameNetwork == "open20\amos\community\models\Community") {
                            $modelNetwork = $ClassnameNetwork::findOne($cwh_nodi->record_id);
                            if ($modelNetwork && $modelNetwork->community_type_id == 1) {
                                return true;
                            }
                        }
                    }
                    if (!empty($cwh_nodi) && !$cwh_nodi->visibility) {
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
        $canShare             = $this->canShare();
        $socialNetworks       = $this->configurator->getSocialNetworks();
        $isMobile             = $this->isMobile();

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
            } else if ($canShare && $okModule) {
                if (!empty($social['visibility']) && $social['visibility'] == ConfiguratorSocialShare::VISIBILITY_ONLY_MOBILE) {
                    if ($isMobile) {
                        $allowedSocialNetwork[$socialName] = $social;
                    }
                } else {
                    $allowedSocialNetwork[$socialName] = $social;
                }
            }
        }
        if ($this->isProtected == false || $this->isComment == true) {
            if (isset($allowedSocialNetwork['email'])) {
                unset($allowedSocialNetwork['email']);
            }
            if (isset($allowedSocialNetwork['ownNetwork'])) {
                unset($allowedSocialNetwork['ownNetwork']);
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
            $classname  = urlencode($this->model->className());
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
                    'data-url' => Url::current([],true),
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

    public static function isContentShareable()
    {

    }

    public function isMobile()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',
                $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
                substr($useragent, 0, 4))) {
            return true;
        }
        return false;
    }
}