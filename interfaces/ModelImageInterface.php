<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\interfaces
 * @category   CategoryName
 */

namespace lispa\amos\core\interfaces;

/**
 * Interface ModelImageInterface
 * @package lispa\amos\core\interfaces
 */
interface ModelImageInterface
{
    /**
     * This method is the getter for the model image and returns an attachment File object.
     * @return \lispa\amos\attachments\models\File
     */
    public function getModelImage();

    /**
     * This method is the getter for the model image url and web url.
     * @param string $size
     * @param bool $protected
     * @param string $url
     * @param bool $absolute
     * @param bool $canCache
     * @return string
     */
    public function getModelImageUrl($size = 'original', $protected = true, $url = '/img/img_default.jpg', $absolute = false, $canCache = false);
}
