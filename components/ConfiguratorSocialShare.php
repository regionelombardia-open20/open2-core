<?php
/**
 */

namespace lispa\amos\core\components;

use lispa\amos\core\interfaces\ContentModelInterface;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use ymaker\social\share\configurators\Configurator;


/**
 * Configurator for social network drivers.
 *
 * @since 1.0
 */
class ConfiguratorSocialShare extends Configurator
{
    const VISIBILITY_ALWAYS = 'always';
    const VISIBILITY_ONLY_PUBLIC_CONTENT = 'only_public_content';
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

    public $options = [
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

    public function initDeafaultOption(){
        if(empty($this->socialNetworks)) {
            $this->socialNetworks = [
                'facebook' => [
                    'class' => \lispa\amos\core\forms\editors\socialShareWidget\drivers\Facebook::class,
                    'label' => \yii\helpers\Html::tag('span', '', ['class' => 'am am-facebook-box', 'title' => \Yii::t('amoscore','Share with facebook')]),
                ],
                    'twitter' => [
                    'class' => \ymaker\social\share\drivers\Twitter::class,
                    'label' => \yii\helpers\Html::tag('span', '', ['class' => 'am am-twitter-box', 'title' => \Yii::t('amoscore','Share with twitter')]),
                    'options' => ['class' => 'tw'],
                    'visibility' => ConfiguratorSocialShare::VISIBILITY_ONLY_PUBLIC_CONTENT
                    ],
//                'googlePlus' => [
//                    'class' => \ymaker\social\share\drivers\GooglePlus::class,
//                    'label' => \yii\helpers\Html::tag('span', '', ['class' => 'am am-google-plus-box']),
//                    'options' => ['class' => 'gp'],
//                ],
                'linkedIn' => [
                    'class' => \ymaker\social\share\drivers\LinkedIn::class,
                    'label' => \yii\helpers\Html::tag('span', '', ['class' => 'am am-linkedin-box', 'title' => \Yii::t('amoscore','Share with linkedin')]),
                    'options' => ['class' => 'gp'],
                    'visibility' => ConfiguratorSocialShare::VISIBILITY_ONLY_PUBLIC_CONTENT
                ],
                'email' => [
                    'class' => \lispa\amos\core\forms\editors\socialShareWidget\drivers\Email::class,
                    'label' => \yii\helpers\Html::tag('span', '', ['class' => 'am am-email', 'title' => \Yii::t('amoscore','Share with email')]),
                    'options' => ['class' => 'email-btn'],
                    'visibility' => ConfiguratorSocialShare::VISIBILITY_ONLY_PUBLIC_CONTENT
                ],
                'ownNetwork' => [
                    'class' => \lispa\amos\core\forms\editors\socialShareWidget\drivers\OwnNetwork::class,
                    'label' => \yii\helpers\Html::tag('span', '', ['class' => 'am am-accounts-alt open-modal', 'title' => \Yii::t('amoscore','Share with your own network')]),
                    'options' => ['class' => 'own-network'],
                    'visibility' => ConfiguratorSocialShare::VISIBILITY_ALWAYS,
                    'required_module' => 'chat'
                ]
            ];
        }

    }

}
