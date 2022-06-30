<?php
/**
 */

namespace open20\amos\core\forms\editors\socialShareWidget\drivers;

use open20\amos\core\module\BaseAmosModule;
use ymaker\social\share\base\Driver;
use yii\helpers\Html;

/**
 * Driver for Facebook.
 *
 *
 * @since 1.0
 */
class Email extends Driver
{
    /**
     * {@inheritdoc}
     */
    protected function processShareData()
    {
        $this->url = rawurlencode (BaseAmosModule::t('amoscore', '#share_body_message') .static::encodeData($this->url));
        $this->title = \Yii::$app->name.': '.rawurlencode($this->title);
    }

    /**
     * @inheritdoc
     */
    public function getLink()
    {
        $this->_link = 'mailto:?subject={title}&body={url}';

        return parent::getLink();
    }

}
