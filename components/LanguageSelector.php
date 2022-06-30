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

use yii\base\BootstrapInterface;

/**
 * Class LanguageSelector
 * @package open20\amos\core\components
 */
class LanguageSelector implements BootstrapInterface
{
    /**
     * @var array $supportedLanguages
     */
    public $supportedLanguages = [];

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $preferredLanguage = isset($app->request->cookies['language']) ? (string)$app->request->cookies['language'] : null;
        // or in case of database:
        // $preferredLanguage = $app->user->language;

        if (empty($preferredLanguage)) {
            $preferredLanguage = $app->request->getPreferredLanguage($this->supportedLanguages);
        }

        $app->language = $preferredLanguage;
    }
}
