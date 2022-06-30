<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\components
 * @category   CategoryName
 */

namespace open20\amos\core\components;

use yii\i18n\MissingTranslationEvent;

/**
 * Class TranslationEventHandler
 * @package open20\amos\core\components
 */
class TranslationEventHandler
{
    public static function handleMissingTranslation(MissingTranslationEvent $event)
    {
        $event->translatedMessage = "@MISSING: {$event->category}.{$event->message} FOR LANGUAGE {$event->language} @";
    }
}