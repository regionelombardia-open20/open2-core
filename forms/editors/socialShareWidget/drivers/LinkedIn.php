<?php
/**
 */

namespace open20\amos\core\forms\editors\socialShareWidget\drivers;

use ymaker\social\share\base\AbstractDriver;

/**
 * Driver for LinkedIn.
 *
 *
 * @property bool|string $siteName
 *
 * @since 1.0
 */
class LinkedIn extends AbstractDriver
{
    /**
     * @var bool|string
     */
    public $siteName  = false;
    
    /**
     * {@inheritdoc}
     */
    protected function processShareData()
    {
        $this->url         = $this->url;
        
        if (\is_string($this->siteName)) {
            $this->appendToData('siteName', $this->siteName);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function buildLink()
    {
      
        $link = 'https://www.linkedin.com/sharing/share-offsite/?url={url}';

        if ($this->siteName) {
            $this->addUrlParam($link, 'source', '{siteName}');
        }

        return $link;
    }
}