<?php
/**
 */

namespace open20\amos\core\forms\editors\socialShareWidget\drivers;

use ymaker\social\share\base\AbstractDriver;

/**
 * Driver for Telegram messenger.
 *
 *
 * @since 1.0
 */
class Telegram extends AbstractDriver
{
    /**
     * @var bool|string
     */
    public $message = false;

    /**
     * {@inheritdoc}
     */
    protected function processShareData()
    {
        $this->url         = static::encodeData($this->url);
        $title             = static::encodeData(strip_tags($this->title));
        $this->description = (!empty($title) ? static::encodeData(strip_tags($this->title)).' - '.static::encodeData(strip_tags($this->description))
                : static::encodeData(strip_tags($this->description)));
        if (strlen($this->description) > 3000) {
            $this->description = substr($this->description, 0, 3000).'...';
        }
        if (\is_string($this->message)) {
            $this->appendToData('message', $this->message);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function buildLink()
    {
        $link = 'https://telegram.me/share/url?url={url}&text={description}';

        if ($this->message) {
            $this->addUrlParam($link, 'text', '{message}');
        }

        return $link;
    }
}