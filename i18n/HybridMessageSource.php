<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\i18n
 * @category   CategoryName
 */

namespace open20\amos\core\i18n;

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