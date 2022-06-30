<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms
 * @category   CategoryName
 */

namespace open20\amos\core\forms;

use open20\amos\core\helpers\Html;
use open20\amos\core\record\Record;
use Yii;
use yii\base\Widget;

/**
 * Class CreateNewButtonWidget
 * Renders the "create new" button also according to the permissions that the user has.
 *
 * @package open20\amos\core\forms
 */
class ChangeViewButtonWidget extends Widget
{
    /**
     * @var string $createNewBtnLabel Label for create button. Default to "Crea nuovo".
     */
    public $htmlButtons = [];


    /**
     * @return string
     */
    public function run()
    {
        return $this->renderButtons();
    }


    /**
     * @return string
     */
    public function renderButtons()
    {
       $content = '';
       foreach ($this->htmlButtons as $button){
           $content .=  $button;
       }
       return $content;
    }
}
