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


interface CmsModelInterface {
     /**
     * Search method useful to retrieve data to show in frontend (with cms)
     * 
     * @param $params
     * @param int|null $limit
     * @return ActiveDataProvider 
     */
    public function cmsSearch($params, $limit);
    
    /**
     * return a list of fields that can be shown in frontend pages made by cms. For each field , also the field type is specified. 
     * In "Backend modules" cms section, user can choose to show only some of these fields.
     * 
     * @return array An array of open20\amos\core\record\CmsField objects
     */
    public function cmsViewFields() ;
    
     /**
     * return the list of fields to search for in frontend pages made by cms.For each field , also the field type is specified. 
     * 
     * @return array An array of open20\amos\core\record\CmsField objects
     */
    public function cmsSearchFields();
    
    /**
     * Method to know if the module can be viewed from the frontend
     * 
     * @param int $id
     * @return boolean 
     */
    public function cmsIsVisible($id);

}
