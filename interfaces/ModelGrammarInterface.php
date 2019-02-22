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


interface ModelGrammarInterface
{

    /**
     * @return string The singular model name in translation label
     */

    public function  getModelSingularLabel();

    /**
     * @return string The model name in translation label
     */
    public function getModelLabel();

    /**
     * @return string
     */
    public function getArticleSingular();

    /**
     * @return string
     */
    public function  getArticlePlural();

    /**
     * @return string
     */
    public function getIndefiniteArticle();
}