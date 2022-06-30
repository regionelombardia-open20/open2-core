<?php
/**
 */

namespace open20\amos\core\forms\editors\socialShareWidget\drivers;

use open20\amos\core\module\BaseAmosModule;
use ymaker\social\share\base\AbstractDriver;
use yii\helpers\Html;

/**
 * Driver for Facebook.
 *
 *
 * @since 1.0
 */
class Email extends AbstractDriver
{

    /**
     * {@inheritdoc}
     */
    protected function processShareData()
    {
        $this->url   = rawurlencode(BaseAmosModule::t('amoscore', '#share_body_message').' '.$this->url);
        $this->title = \Yii::$app->name.': '.rawurlencode($this->title);       
    }

    /**
     * @inheritdoc
     */
    protected function buildLink()
    {
        return 'mailto:?subject={title}&body={url}';
    }
}