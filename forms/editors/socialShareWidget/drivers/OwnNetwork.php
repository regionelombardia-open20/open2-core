<?php
/**
 */

namespace lispa\amos\core\forms\editors\socialShareWidget\drivers;

use ymaker\social\share\base\Driver;

/**
 * Driver for Facebook.
 *
 *
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
