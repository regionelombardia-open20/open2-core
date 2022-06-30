<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\layouts\parts
 * @category   CategoryName
 */

use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\toolbar\Nav;
use open20\amos\core\toolbar\NavBar;
use open20\amos\core\utilities\CurrentUser;
use yii\web\JsExpression;
use yii\helpers\Url;
use open20\amos\core\module\BaseAmosModule;

/* @var $this \yii\web\View */
?>
<?php
/* Configuration of Slideshow - begin */
if (\Yii::$app->getModule('slideshow') && isset(\Yii::$app->params['slideshow']) && \Yii::$app->params['slideshow'] === TRUE) {
    $slideshow = new \open20\amos\slideshow\models\Slideshow;
    $route = "/" . \Yii::$app->request->getPathInfo();
    $idSlideshow = $slideshow->hasSlideshow($route);
    $slideshowLabel = ($idSlideshow) ? $slideshow->findOne($idSlideshow)->label : NULL;
    echo \open20\amos\slideshow\widgets\SlideshowWidget::widget([]);
}
/** @var bool|false $disablePlatformLinks - if true all the links to dashboard, settings, etc are disabled */
$disablePlatformLinks = isset(\Yii::$app->params['disablePlatformLinks']) ? \Yii::$app->params['disablePlatformLinks'] : false;

/** @var bool|false $disableSettings - if true hide the settings link in the navbar  */
$canDisablePlatform = false;
// if the params hideSettings == true or the user as has at least one of the provided roles, hide the link settings
if(isset(\Yii::$app->params['hideSettings']['roles']) && is_array(\Yii::$app->params['hideSettings']['roles'])){
    $can = false;
    foreach (\Yii::$app->params['hideSettings']['roles'] as $role) {
        $can = $can || \Yii::$app->user->can($role);
    }
    $canDisablePlatform = $can;
}
$disableSettings = (isset(\Yii::$app->params['hideSettings']) &&  !is_array(\Yii::$app->params['hideSettings']) && \Yii::$app->params['hideSettings']) || $canDisablePlatform;


$hasSlideshow = (\Yii::$app->getModule('slideshow') && isset(\Yii::$app->params['slideshow']) && \Yii::$app->params['slideshow'] === TRUE && $idSlideshow) ? TRUE : FALSE;

if ($hasSlideshow) {
    $itemsSlideshow = ['<li class="divider"></li>',
        [
            'label' => (!empty($slideshowLabel)) ? $slideshowLabel : BaseAmosModule::t('amoscore', 'Mostra introduzione'),
            'url' => '#',
            //'options' => ['onclick' => new JsExpression('$("#amos-slideshow").modal("show");') , 'class' => 'open-slideshow-modal'] TODO remove
            'options' => ['class' => 'open-slideshow-modal'] //moved js in global.js
        ],
        '<li class="divider"></li>',
    ];
} else {
    $itemsSlideshow = '<li class="divider"></li>';
}

//if there is information page for policy or cookies - display link at the end of user menu
$hasPrivacyLink = isset(\Yii::$app->params['privacyLink']);
$privacyLink = null;
if ($hasPrivacyLink) {
    $privacyLink = \Yii::$app->params['privacyLink'];
}
$hasCookiesLink = isset(\Yii::$app->params['cookiesLink']);
$cookiesLink = null;
if ($hasCookiesLink) {
    $cookiesLink = \Yii::$app->params['cookiesLink'];
}

/* Configuration of Slideshow - end  */

/* Configuration header menu: field translation */
$headerMenu = new open20\amos\core\views\common\HeaderMenu();
$menuTranslation = $headerMenu->getTranslationField();
$menuCustom = $headerMenu->getCustomContent();
/* echo Translation button */
$headerMenu->getToggleTranslate();

?>

<header>

    <?php
    NavBar::begin([
        'options' => [
            'class' => 'navbar-default',
        ],
        'disablePlatformLinks' => $disablePlatformLinks
    ]);

    if (!CurrentUser::getUserIdentity()) {
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    } else {
        $benvenuto = BaseAmosModule::t('amoscore', 'Benvenuto utente');
        if (NULL !== (CurrentUser::getUserProfile())) {
            if (CurrentUser::getUserProfile()->sesso == 'Maschio') {
                $benvenuto = BaseAmosModule::t('amoscore', 'Benvenuto {utente}', array(
                    'utente' => CurrentUser::getUserProfile()
                ));
            } elseif (CurrentUser::getUserProfile()->sesso == 'Femmina') {
                $benvenuto = BaseAmosModule::t('amoscore', 'Benvenuta {utente}', array(
                    'utente' => CurrentUser::getUserProfile()
                ));
            }
        }

        $model = CurrentUser::getUserProfile();
        $url = $model->getAvatarUrl('original');
        Yii::$app->imageUtility->methodGetImageUrl = 'getAvatarUrl';
        $roundImage = Yii::$app->imageUtility->getRoundImage($model);
        $imgAvatar = Html::img($url, [
            'class' => $roundImage['class'],
            'style' => "margin-left: " . $roundImage['margin-left'] . "%; margin-top: " . $roundImage['margin-top'] . "%;",
            'alt' => BaseAmosModule::t('amoscore', 'Avatar dell\'utente'),
			'data-test' => "user-menu"
        ]);


        $items = [];

        $userMenu = [
            'label' => '<div class="avatar-xs">' . $imgAvatar . '</div>'
//                'label' => AmosIcons::show('account', [
//                    'class' => 'am-2',
//                    'alt' => $this->title
//                ])
                . '<p>' . BaseAmosModule::t('amoscore', '{utente}', array(
                    'utente' => CurrentUser::getUserProfile()
                )) . '</p>',
            'items' => [
                '<li class="dropdown-header">' . $benvenuto . '</li>',
                '<li class="divider"></li>',
                //'<li class="dropdown-header">Azioni</li>',
                'myProfile' => $disablePlatformLinks ? '' : ([
                    'label' => BaseAmosModule::t('amoscore', 'Il mio profilo'),
                    'url' => ['/admin/user-profile/update', 'id' => CurrentUser::getUserProfile()->id],
                    'linkOptions' => ['title' => BaseAmosModule::t('amoscore', 'Il mio profilo')]
                ]),
                [
                    'label' => BaseAmosModule::t('amoscore', 'Esci'),
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post', 'title' => BaseAmosModule::t('amoscore', 'esci')]
                ],
                ($hasPrivacyLink || $hasCookiesLink) ?
                    '<li class="divider"></li>
                     <li class="dropdown-header">' . BaseAmosModule::t('amoscore', 'Informative') . '</li>
                     <li class="divider"></li>'
                    : '',
                ($hasPrivacyLink) ? $privacyLink : '',
                ($hasCookiesLink) ? $cookiesLink : '',
            ],
            'options' => ['class' => 'user-menu'],
            'linkOptions' => ['title' => BaseAmosModule::t('amoscore', 'azioni utente')]
        ];

        $settings = [
            'label' => AmosIcons::show('settings', [
                    'class' => 'am-2',
                ]) . '<span class="sr-only">' . BaseAmosModule::t('amoscore', 'impostazioni') . '</span>',
            'items' => [
                '<li class="dropdown-header">' . BaseAmosModule::t('amoscore', 'Impostazioni') . '</li>',
                '<li class="divider"></li>',
                [
                    'label' => BaseAmosModule::t('amoscore', 'Ordinamenti dashboard'),
                    'url' => 'javascript:void(0);',
                    'visible' => (\Yii::$app->controller instanceof open20\amos\dashboard\controllers\base\DashboardController),
                    'options' =>
                        [
                            'class' => 'enable_order',
                            'id' => 'dashboard-edit-button',
                        ],
                    'linkOptions' => ['title' => BaseAmosModule::t('amoscore', 'Impostazioni')]
                ],
                [
                    'label' => BaseAmosModule::t('amoscore', 'Gestisci widget'),
                    'url' => [
                        '/dashboard/manager',
                        'module' => $this->context->module->id,
                        'slide' => 1
                    ],
                    'linkOptions' => ['title' => BaseAmosModule::t('amoscore', 'Gestisci widget')],
                ],
                ($hasSlideshow) ? '<li class="divider"></li>' : '',
                ($hasSlideshow) ? ($itemsSlideshow[1]) : '',
                '<li class="divider"></li>',
                //Impostare nel params dell'applicazione la versione, per esempio
                // 'versione' => '1.0',
                '<li class="dropdown-header pull-right">' . BaseAmosModule::t('amoscore',
                    'Versione') . ' ' . ((isset(\Yii::$app->params['versione'])) ? \Yii::$app->params['versione'] : '0.1') . '</li>',
            ],
            'options' => ['class' => 'context-menu'],
            'linkOptions' => ['title' => BaseAmosModule::t('amoscore', 'Impostazioni')]
        ];

        $deimpersonate = [
            'label' => AmosIcons::show('assignment-account', [
                    'class' => 'am-2 new-message',
                ]) . '<span class="sr-only">' . BaseAmosModule::t('amoscore', 'De-Impersonate') . '</span>',
            'url' => '/admin/security/deimpersonate',
            'linkOptions' => [
                'title' => BaseAmosModule::t('amoscore', 'De-impersonate')
            ]
        ];

        if (!$disablePlatformLinks && !$disableSettings) {
            $items[] = $settings;
        }

        if (Yii::$app->session->has('IMPERSONATOR')) {
            $items[] = $deimpersonate;
        }

        $items[] = $userMenu;
        $menuItems = $items;

        //Add menu of translation
        if (!empty($menuTranslation)) {
            $menuItems[] = $menuTranslation;
        }
        if (!empty($menuCustom)) {
            $menuItems[] = $menuCustom;
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
                    AmosIcons::show('globe-alt') . Html::tag('p', BaseAmosModule::t('amoscore', '#frontend')) //TODO add translation into amos-core
                    , Url::to(\Yii::$app->params['platform']['frontendUrl']),
                    ['title' => BaseAmosModule::t('amoscore', 'frontend'), 'target' => '_blank']
                ),
                ['class' => 'toFrontend']
            );
            $menuItems[] = $frontendLink;
        } /* end link frontend */


    }

    echo Nav::widget([
        'options' => [
            'class' => 'navbar-nav navbar-right',
        ],
        'encodeLabels' => false,
        'dropDownCaret' => '',
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

</header>