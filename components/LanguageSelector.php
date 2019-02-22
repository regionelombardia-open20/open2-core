<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\components
 * @category   CategoryName
 */

namespace lispa\amos\core\components;

use yii\base\BootstrapInterface;

/**
 * Class LanguageSelector
 * @package lispa\amos\core\components
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
