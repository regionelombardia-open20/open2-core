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

use ymaker\social\share\base\Driver;

/**
 * Driver for Facebook.
 *
 * @link https://facebook.com
 *
 * @author Vladimir Kuprienko <vldmr.kuprienko@gmail.com>
 * @since 1.0
 */
class OwnNetwork extends Driver
{
    /**
     * {@inheritdoc}
     */
    protected function processShareData()
    {
        $this->url = static::encodeData($this->url);
        $this->title = static::encodeData($this->title);
    }

    /**
     * @inheritdoc
     */
    public function getLink()
    {
        $this->_link = 'javascript:void(0);';

        return parent::getLink();
    }

}
