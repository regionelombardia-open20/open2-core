<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\i18n\grammar
 * @category   CategoryName
 */

namespace open20\amos\core\i18n\grammar;

use open20\amos\core\interfaces\ModelGrammarInterface;
use open20\amos\core\module\BaseAmosModule;

/**
 * Class ContentModelGrammar
 * @package amos\results\i18n\grammar
 */
class ContentModelGrammar implements ModelGrammarInterface
{
    /**
     * @inheritdoc
     */
    public function getModelSingularLabel()
    {
        return BaseAmosModule::t('amoscore', 'contenuto');
    }

    /**
     * @inheritdoc
     */
    public function getModelLabel()
    {
        return BaseAmosModule::t('amoscore', 'contenuti');
    }

    /**
     * @inheritdoc
     */
    public function getArticleSingular()
    {
        return BaseAmosModule::t('amoscore', 'il');
    }

    /**
     * @inheritdoc
     */
    public function getArticlePlural()
    {
        return BaseAmosModule::t('amoscore', 'i');
    }

    /**
     * @inheritdoc
     */
    public function getIndefiniteArticle()
    {
        return BaseAmosModule::t('amoscore', 'un');
    }

}
