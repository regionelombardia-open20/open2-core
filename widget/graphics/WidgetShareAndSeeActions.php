<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\events\widgets\graphics
 * @category   CategoryName
 */

namespace open20\amos\core\widget\graphics;

use open20\amos\core\widget\WidgetGraphic;
use open20\amos\notificationmanager\base\NotifyWidgetDoNothing;
use yii\helpers\Url;


/**
 * Class WidgetShareAndSeeActions
 * @package open20\amos\core\widget\graphics
 */
class WidgetShareAndSeeActions extends WidgetGraphic
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function getHtml()
    {

        $current_page_url = \Yii::$app->params['platform']['frontendUrl'] . Url::current();
        $support_email=  \Yii::$app->params['widgetShareEmail'];
        $sharing_link = [
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u=" . $current_page_url,
            'twitter' => "https://twitter.com/intent/tweet?text=" . $current_page_url,
            'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url=" . $current_page_url,
            'whatsapp' => "https://api.whatsapp.com/send?text=" . $current_page_url,
            'telegram' => "https://t.me/share/url?url=" . $current_page_url,
        ];

        $actions_link = [
            'download' => Url::current(),
            'print' => "window.print();",
            'send' => "mailto:?subject=" . $support_email . $current_page_url,
        ];


        
        return $this->render('share_and_see_actions', [
            'sharing_link' => $sharing_link,
            'actions_link' => $actions_link,
            'widget' => $this,
        ]);
    }
}