<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\i18n
 * @category   CategoryName
 */

namespace lispa\amos\core\i18n;

use yii\i18n\PhpMessageSource;

class HybridMessageSource extends PhpMessageSource
{
    /**
     * Get all messages public
     * @param $category
     * @param $language
     * @return array
     */
    public function getAllMessages($category, $language) {
        return self::loadMessages($category, $language);
    }

}