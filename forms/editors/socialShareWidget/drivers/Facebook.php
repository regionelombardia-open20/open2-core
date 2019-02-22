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
class Facebook extends Driver
{

    /**
     * @inheritdoc
     */
    public function getLink()
    {
        $this->_link = 'http://www.facebook.com/sharer.php?u={url}';

        return parent::getLink();
    }

    /**
     * @inheritdoc
     */
    protected function processShareData()
    {
        $this->url = static::encodeData($this->url);
        $this->description = static::encodeData($this->title);
        $this->imageUrl = static::encodeData($this->imageUrl);
        $this->description = static::encodeData($this->description);
    }

}
