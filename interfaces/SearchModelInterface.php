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


interface SearchModelInterface {
     /**
     * @param array $searchParamsArray Array of search words
     * @param int|null $pageSize
     * @return ActiveDataProvider Do the search on all text fields
     */
    public function globalSearch($searchParamsArray, $pageSize);
    
    
     /**
     * @param object $model The model to convert into SearchResult
     * @return SearchResult 
     */
     public function convertToSearchResult($model);
}
