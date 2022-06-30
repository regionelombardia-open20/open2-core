<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views
 * @category   CategoryName
 */

namespace open20\amos\core\views;

use open20\amos\core\views\common\BaseListView;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


class IconView extends BaseListView
{
    public $name = 'icon';

    public $layout = "{items}\n{pager}";

    public $fields = [

    ];

    public $containerOptions = [
        // 'id' => 'dataViewListContainer',
        // 'class'=>'row'
    ];

    public $itemOptions = [
        // "class" => "col-md-4 col-sm-6 col-xs-12",
        // "aria-selected" => "false",
        // "role" => "option"
    ];


    public $itemPerRow = ['xs' => 6];

    /**
     * @var array additional parameters to be passed to [[itemView]] when it is being rendered.
     * This property is used only when [[itemView]] is a string representing a view name.
     */
    public $viewParams = [];
    /**
     * @var string the HTML code to be displayed between any two consecutive items.
     */
    public $separator = "";
    /**
     * @var array the HTML attributes for the container tag of the list view.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     */
    public $options = ['class' => 'icon-view'];

}