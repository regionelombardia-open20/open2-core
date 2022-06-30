<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\interfaces
 * @category   CategoryName
 */

namespace open20\amos\core\interfaces;

use yii\db\ActiveQuery;

/**
 * Interface NewsletterInterface
 * @package open20\amos\core\interfaces
 */
interface NewsletterInterface
{
    /**
     * @return string
     */
    public function newsletterOrderByField();
    
    /**
     * @return string
     */
    public function newsletterPublishedStatus();
    
    /**
     * @param string $searchParam
     * @param ActiveQuery $query
     * @return ActiveQuery
     */
    public function newsletterSearchFilter($searchParam, $query);
    
    /**
     * @return string
     */
    public function newsletterContentTitle();
    
    /**
     * @return string
     */
    public function newsletterContentTitleField();
    
    /**
     * @return string
     */
    public function newsletterContentStatusField();
    
    /**
     * @return array
     */
    public function newsletterContentGridViewColumns();
    
    /**
     * @return array
     */
    public function newsletterSelectContentsGridViewColumns();
}
