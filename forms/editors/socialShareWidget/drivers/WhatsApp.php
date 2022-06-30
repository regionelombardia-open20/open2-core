<?php
/**
 */

namespace open20\amos\core\forms\editors\socialShareWidget\drivers;

use ymaker\social\share\base\AbstractDriver;

/**
 * Driver for WhatsApp messenger.
 *
 *
 * WARNING: This driver works only in mobile devices
 * with installed WhatsApp client.
 *
 * @since 1.0
 */
class WhatsApp extends AbstractDriver
{

    /**
     * {@inheritdoc}
     */
    protected function processShareData()
    {
        //  $this->url         = static::encodeData($this->url);
        $title             = static::encodeData(strip_tags($this->title));
        $this->description = (!empty($title) ? strip_tags($this->title).' - '.strip_tags($this->description)
                : strip_tags($this->description));

        $this->url = static::encodeData($this->url).' - '.$this->description;
        if (strlen($this->url) > 3000) {
            $this->url = substr($this->url, 0, 3000).'...';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function buildLink()
    {
        return 'whatsapp://send?text={url}';
    }
}