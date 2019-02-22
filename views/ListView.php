<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views
 * @category   CategoryName
 */

namespace lispa\amos\core\views;

use lispa\amos\core\views\common\BaseListView;
use Yii;

class ListView extends BaseListView
{
    public $name = 'list';

    public $layout = "{items}\n{pager}";

    public $template = '{view} {update} {delete}';
    public $buttons;
    public $buttonClass = 'lispa\amos\core\views\common\Buttons';
    public $viewOptions = [
        'class' => ''
    ];
    public $updateOptions = [
        'class' => ''
    ];
    public $deleteOptions = [
        'class' => ''
    ];

    public $_isDropdown = false;


}
