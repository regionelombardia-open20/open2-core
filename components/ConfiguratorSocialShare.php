<?php
/**
 */

namespace open20\amos\core\components;

use open20\amos\core\interfaces\ContentModelInterface;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use ymaker\social\share\configurators\Configurator;
use open20\amos\core\module\BaseAmosModule;

/**
 * Configurator for social network drivers.
 *
 * @since 1.0
 */
class ConfiguratorSocialShare extends Configurator
{
    const VISIBILITY_ALWAYS              = 'always';
    const VISIBILITY_ONLY_PUBLIC_CONTENT = 'only_public_content';
    const VISIBILITY_ONLY_MOBILE         = 'only_mobile';

    /**
     * Configuration of social network drivers.
     *
     * @var array
     */
    public $socialNetworks = [];

    /**
     * CSS options for share links.
     *
     * @var array
     */
    public $options          = [
        'class' => 'social-network',
    ];
    public $registerMetaTags = false;

    /**
     * Set default values for special link options.
     */
    public function init()
    {
        $this->initDeafaultOption();
        parent::init();
    }

    public function initDeafaultOption()
    {
        $isFrontend    = self::isFrontend();
        $url           = self::getCurrentUrl();
        $socialModule  = \Yii::$app->getModule('social');
        $haveAnalytics = ((!empty($socialModule) && !empty($socialModule->googleAnalytics)) ? true : false);
        if (empty($this->socialNetworks)) {
            $this->socialNetworks = [
                'facebook' => [
                    'class' => \open20\amos\core\forms\editors\socialShareWidget\drivers\Facebook::class,
                    'label' => \yii\helpers\Html::tag('span', '',
                        ['class' => 'ic ic-facebook', 'title' => BaseAmosModule::t('amoscore', 'Share with facebook')]),
                    'options' => ['class' => 'fb', 'onclick' => ($haveAnalytics ? "__gaTracker('send', 'event', 'Facebook', 'Share', '".$url."', '".$isFrontend."');"
                            : "return false;")],
                    'visibility' => ConfiguratorSocialShare::VISIBILITY_ONLY_PUBLIC_CONTENT
                ],
                'twitter' => [
                    'class' => \open20\amos\core\forms\editors\socialShareWidget\drivers\Twitter::class,
                    'label' => \yii\helpers\Html::tag('span', '',
                        ['class' => 'ic ic-twitter', 'title' => BaseAmosModule::t('amoscore', 'Share with twitter')]),
                    'options' => ['class' => 'tw', 'onclick' => ($haveAnalytics ? "__gaTracker('send', 'event', 'Twitter', 'Share', '".$url."', '".$isFrontend."');"
                            : "return false;")],
                    'visibility' => ConfiguratorSocialShare::VISIBILITY_ONLY_PUBLIC_CONTENT
                ],
//                'googlePlus' => [
//                    'class' => \ymaker\social\share\drivers\GooglePlus::class,
//                    'label' => \yii\helpers\Html::tag('span', '', ['class' => 'am am-google-plus-box']),
//                    'options' => ['class' => 'gp'],
//                ],
                'linkedIn' => [
                    'class' => \open20\amos\core\forms\editors\socialShareWidget\drivers\LinkedIn::class,
                    'label' => \yii\helpers\Html::tag('span', '',
                        ['class' => 'ic ic-linkedin', 'title' => BaseAmosModule::t('amoscore', 'Share with linkedin')]),
                    'options' => ['class' => 'lk', 'onclick' => ($haveAnalytics ? "__gaTracker('send', 'event', 'LinkedIn', 'Share', '".$url."', '".$isFrontend."');"
                            : "return false;")
                    ],
                    'visibility' => ConfiguratorSocialShare::VISIBILITY_ONLY_PUBLIC_CONTENT,
                ],
                'telegram' => [
                    'class' => \open20\amos\core\forms\editors\socialShareWidget\drivers\Telegram::class,
                    'label' => \yii\helpers\Html::tag('span', '',
                        ['class' => 'ic ic-telegram', 'title' => BaseAmosModule::t('amoscore', 'Share with Telegram')]),
                    'options' => ['class' => 'tg', 'onclick' => ($haveAnalytics ? "__gaTracker('send', 'event', 'Telegram', 'Share', '".$url."', '".$isFrontend."');"
                            : "return false;")],
                    'visibility' => ConfiguratorSocialShare::VISIBILITY_ONLY_MOBILE,
                ],
                'whatsApp' => [
                    'class' => \open20\amos\core\forms\editors\socialShareWidget\drivers\WhatsApp::class,
                    'label' => \yii\helpers\Html::tag('span', '',
                        ['class' => 'ic ic-whatsapp', 'title' => BaseAmosModule::t('amoscore', 'Share with WhatsApp')]),
                    'options' => ['class' => 'wa', 'onclick' => ($haveAnalytics ? "__gaTracker('send', 'event', 'WhatsApp', 'Share', '".$url."', '".$isFrontend."');"
                            : "return false;")],
                    'visibility' => ConfiguratorSocialShare::VISIBILITY_ONLY_MOBILE
                ],
                'email' => [
                    'class' => \open20\amos\core\forms\editors\socialShareWidget\drivers\Email::class,
                    'label' => \yii\helpers\Html::tag('span', '',
                        ['class' => 'ic ic-mail', 'title' => BaseAmosModule::t('amoscore', 'Share with email')]),
                    'options' => ['class' => 'email', 'onclick' => ($haveAnalytics ? "__gaTracker('send', 'event', 'Email', 'Share', '".$url."', '".$isFrontend."');"
                            : "return false;")],
                    'visibility' => ConfiguratorSocialShare::VISIBILITY_ONLY_PUBLIC_CONTENT
                ],
                'ownNetwork' => [
                    'class' => \open20\amos\core\forms\editors\socialShareWidget\drivers\OwnNetwork::class,
                    'label' => \yii\helpers\Html::tag('span', '',
                        ['class' => 'ic ic-user open-modal', 'title' => BaseAmosModule::t('amoscore',
                            'Share with your own network')]),
                    'options' => ['class' => 'own-network', 'onclick' => ($haveAnalytics ? "__gaTracker('send', 'event', 'OwnNetwork', 'Share', '".$url."', '".$isFrontend."');"
                            : "return false;")],
                    'visibility' => ConfiguratorSocialShare::VISIBILITY_ALWAYS,
                    'required_module' => 'chat'
                ]
            ];
        }
    }

    /**
     *
     * @return int
     */
    public static function isFrontend()
    {
        $webRoot = \Yii::getAlias('@webroot');
        $arrPath = explode(DIRECTORY_SEPARATOR, $webRoot);
        if (in_array('frontend', $arrPath)) {
            return 1;
        }
        return 0;
    }
 
    public static function getCurrentUrl()
    {
        $current     = \yii\helpers\Url::current();
        $url         = strtok($current, '?');
        $array_query = [];
        parse_str(parse_url($current, PHP_URL_QUERY), $array_query);
        $queryString = '';
        foreach ($array_query as $k => $v) {
            if (strpos($k, 'csrf') !== false) {
                unset($array_query[$k]);
            }
        }
        $queryString = http_build_query($array_query);

        $url2 = $url.(!empty($array_query) ? '?'.$queryString : '');
        return $url2;
    }
}