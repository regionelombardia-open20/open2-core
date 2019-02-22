<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\toolbar
 * @category   CategoryName
 */

namespace lispa\amos\core\toolbar;

use lispa\amos\core\helpers\Html;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\toolbar\NavBarOverriden as YiiNavBar;
use lispa\amos\core\utilities\CurrentUser;
use Yii;
use yii\web\JsExpression;
use yii\helpers\Url;

/**
 * Class NavBar - draws the toogle button for hamburger menu (small size screen)
 * @package lispa\amos\core\toolbar
 */
class NavBar extends YiiNavBar
{
    public $disablePlatformLinks = false;

    /*
     * eliminato il bottone di visualizzazione menu su mobile
     */
    protected function renderToggleButton()
    {
        if (!CurrentUser::getUserIdentity()) {
            $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
        } else {
            $benvenuto = Yii::t('amoscore', 'Benvenuto utente');
            if (NULL !== (CurrentUser::getUserProfile())) {
                $benvenuto = Yii::t('amoscore', '{utente}', array(
                    'utente' => CurrentUser::getUserProfile()
                ));
            }
            /* Configuration of Slideshow - begin */
            if (\Yii::$app->getModule('slideshow') && isset(\Yii::$app->params['slideshow']) && \Yii::$app->params['slideshow'] === TRUE) {
                $slideshow = new \lispa\amos\slideshow\models\Slideshow;
                $route = "/" . \Yii::$app->request->getPathInfo();
                $idSlideshow = $slideshow->hasSlideshow($route);
                $slideshowLabel = ($idSlideshow) ? $slideshow->findOne($idSlideshow)->label : NULL;                
            }

            $hasSlideshow = (\Yii::$app->getModule('slideshow') && isset(\Yii::$app->params['slideshow']) && \Yii::$app->params['slideshow'] === TRUE && $idSlideshow) ? TRUE : FALSE;

            if ($hasSlideshow) {
                $itemsSlideshow = ['<li class="divider"></li>',
                    [
                        'label' => (!empty($slideshowLabel)) ? $slideshowLabel : Yii::t('amoscore', 'Mostra introduzione'),
                        'url' => '#',
                        'options' => ['onclick' => new JsExpression('$("#amos-slideshow").modal("show");')]
                    ],
                    '<li class="divider"></li>',
                ];
            } else {
                $itemsSlideshow = '<li class="divider"></li>';
            }

            /* Configuration of Slideshow - end  */
            /* Configurazione translation */
            $headerMenu = new \lispa\amos\core\views\common\HeaderMenu();
            $menuTranslation = $headerMenu->getTranslationField();

            //if there is information page for policy or cookies - display link at the end of menu
            $hasPrivacyLink = isset(\Yii::$app->params['privacyLink']);
            $privacyLink = null;
            if($hasPrivacyLink){
                $privacyLink = \Yii::$app->params['privacyLink'];
            }
            $hasCookiesLink = isset(\Yii::$app->params['cookiesLink']);
            $cookiesLink = null;
            if($hasCookiesLink){
                $cookiesLink = \Yii::$app->params['cookiesLink'];
            }


            $esclusioni = ['faq', 'fulmine']; //Moduli da escludere per la gestione dei widget

            $model= CurrentUser::getUserProfile();
            $url = $model->getAvatarUrl('original');
            Yii::$app->imageUtility->methodGetImageUrl = 'getAvatarUrl';
            $roundImage = Yii::$app->imageUtility->getRoundImage($model);
            $imgAvatar = Html::img($url, [
                'class' => $roundImage['class'],
                'style' => "margin-left: " . $roundImage['margin-left'] . "%; margin-top: " . $roundImage['margin-top'] . "px;",
                'alt' => Yii::t('amoscore', 'Avatar dell\'utente, apertura menu')
            ]);

            $menuItems = [
                    [
                    'label' => AmosIcons::show('menu', [
                        'class' => 'am-2',
                        'title' => 'menu'
                    ]),
                    'items' => [
                        '<li class="dropdown-header col-xs-12"><div class="container-round-img-xs">'.$imgAvatar.'</div><p>' . $benvenuto . '</p></li>',
                        '<li class="divider"></li>',
                        $this->disablePlatformLinks ? '' : ['label' => \Yii::t('amoscore','Il mio profilo'),
                            'url' => ['/admin/user-profile/update-profile', 'id' => CurrentUser::getUserProfile()->id],
                            'linkOptions' => ['title' => \Yii::t('amoscore', 'il mio profilo')],
                        ],
                        ['label' => \Yii::t('amoscore','Esci'),
                            'url' => ['/site/logout'],
                            'linkOptions' => ['data-method' => 'post', 'title' => \Yii::t('amoscore', 'esci')]
                        ],
                        $this->disablePlatformLinks ? '' : ('<li class="divider"></li>'.
                        '<li class="dropdown-header">'.
                            AmosIcons::show('settings', [
                                'class' => 'am-lg',
                            ]).
                            BaseAmosModule::t('amoscore', 'Impostazioni').
                        '</li>'.
                        '<li class="divider"></li>'),
                        $this->disablePlatformLinks ? '' : ['label' => \Yii::t('amoscore', 'Ordinamenti dashboard'),
                            'url' => 'javascript:void(0);',
                            'options' => ['class' => 'enable_order', 'onclick' => new JsExpression('Dashboard.enableDashboardsSortable()')],
                            'linkOptions' => ['title' => \Yii::t('amoscore', 'Ordinamenti dashboard')]
                        ],
                        $this->disablePlatformLinks ? '' : ['label' => \Yii::t('amoscore','Gestisci widget'),
                            'url' => ['/dashboard/manager',
                                'dashboard' => isset(\Yii::$app->controller->module) && \Yii::$app->controller->module->id != 'dashboard' && !in_array(\Yii::$app->controller->module->id, $esclusioni) ? \Yii::$app->controller->module->id : 'primary',
                                'slide' => 0
                            ],
                            'linkOptions' => ['title' => \Yii::t('amoscore', 'gestisci widget')]
                        ],
                        $this->disablePlatformLinks ? '' : (($hasSlideshow) ? '<li class="divider"></li>' : ''),
                        ($hasSlideshow) ? ($itemsSlideshow[1]) : '',
                        ($hasPrivacyLink || $hasCookiesLink) ?
                            '<li class="divider"></li>
                             <li class="dropdown-header">'.
                            AmosIcons::show('info-outline', [
                                'class' => 'am-lg',
                            ]).
                            Yii::t('amoscore', 'Informative').'</li>
                             <li class="divider"></li>'
                            : '',
                        ($hasPrivacyLink) ? $privacyLink : '',
                        ($hasCookiesLink) ? $cookiesLink : '',
                        '<li class="divider"></li>',
                        //Impostare nel params dell'applicazione la versione, per esempio
                        // 'versione' => '1.0',
                        '<li class="dropdown-header pull-right">' . Yii::t('amoscore', 'Versione') . ' ' . ((isset(\Yii::$app->params['versione'])) ? \Yii::$app->params['versione'] : '0.1' ) . '</li>',
                    ],
                'options' => ['class' => 'hamburger context-menu hidden-sm hidden-md hidden-lg'],
                'linkOptions' => ['title' => 'azioni utente']
                ],

            ];
        }
        //Add menu of translation
        if (!empty($menuTranslation)) {
            $menuItems[] = $menuTranslation;
        }

        /**
         * link to frontend
         * check params from platform/backend/config/params.php
         */
        if (isset(\Yii::$app->params['toFrontendLink']) && \Yii::$app->params['toFrontendLink']) {
            /**
             * get params from platform/common/config/params-local.php
             */
            $frontendLink = Html::tag('li',
                Html::a(
                    AmosIcons::show('globe-alt')
                    , Url::to(\Yii::$app->params['platform']['frontendUrl']),
                    ['title' => Yii::t('amoscore', 'frontend'), 'target' => '_blank']
                ),
                ['class' => 'toFrontend']
            );
            $menuItems[] = $frontendLink;
        } /* end link frontend */


        return Nav::widget([
            'options' => [
                'class' => 'navbar-nav navbar-right hidden-lg hidden-md hidden-sm',
            ],
            'encodeLabels' => false,
            'dropDownCaret' => '',
            'items' => $menuItems,
        ]);
    }

}
