<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\interfaces
 * @category   CategoryName
 */

namespace open20\amos\core\interfaces;

/**
 * Interface ContentSettingsMenuInterface
 * @package open20\amos\core\interfaces
 */
interface ContentSettingsMenuInterface
{
    /**
     * This method returns the entry to be added in the content settings menu.
     * Usually the entry is an Html::a element. The param $options can contain
     * options for the link or other options useful in the function.
     * @param array $options
     * @return string
     */
    public function getContentSettingsMenuEntry($options = []);
}
