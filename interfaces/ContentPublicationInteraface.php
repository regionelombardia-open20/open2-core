<?php
/**
 * Created by PhpStorm.
 * User: michele.lafrancesca
 * Date: 04/02/2022
 * Time: 10:07
 */

namespace open20\amos\core\interfaces;


interface ContentPublicationInteraface
{
    /**
     * Show if the content is visible
     * used in particular to know if attachments file are visible
     * @return boolean
     */
    public function isContentPublic();

}