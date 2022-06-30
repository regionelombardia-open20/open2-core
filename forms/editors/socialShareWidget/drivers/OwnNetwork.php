<?php
/**
 */

namespace open20\amos\core\forms\editors\socialShareWidget\drivers;

use ymaker\social\share\base\AbstractDriver;

/**
 * Driver for Facebook.
 *
 *
 * @since 1.0
 */
class OwnNetwork extends AbstractDriver
{

    /**
     * {@inheritdoc}
     */
    protected function processShareData()
    {
        $this->url   = static::encodeData($this->url);
        $this->title = static::encodeData($this->title);
    }

    /**
     * @inheritdoc
     */
    protected function buildLink()
    {
        return 'javascript:void(0);';
    }
}