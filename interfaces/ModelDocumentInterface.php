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
 * Interface ModelDocumentInterface
 * @package lispa\amos\core\interfaces
 */
interface ModelDocumentInterface
{
    /**
     * This method is the getter for the document file and returns an attachment File object.
     * @return \lispa\amos\attachments\models\File
     */
    public function getDocument();

    /**
     * This method is the getter for the document url and web url.
     * @param string $size
     * @param bool $protected
     * @param string $url
     * @param bool $absolute
     * @param bool $canCache
     * @return string
     */
    public function getDocumentUrl($size = 'original', $protected = true, $url = '/img/img_default.jpg', $absolute = false, $canCache = false);

    /**
     * This method is the getter for the document image and returns an HTML ready to be rendered.
     * @param bool $onlyIconName
     * @return string
     */
    public function getDocumentImage($onlyIconName = false);
}
