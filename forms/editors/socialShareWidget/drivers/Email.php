<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    Open20Package
 * @category   CategoryName
 */
/**
 * @link https://github.com/yiimaker/yii2-social-share
 * @copyright Copyright (c) 2017-2018 Yii Maker
 * @license BSD 3-Clause License
 */

namespace open20\amos\core\forms\editors\socialShareWidget\drivers;

use open20\amos\core\module\BaseAmosModule;
use ymaker\social\share\base\Driver;
use yii\helpers\Html;

/**
 * Driver for Facebook.
 *
 * @link https://facebook.com
 *
 * @author Vladimir Kuprienko <vldmr.kuprienko@gmail.com>
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
