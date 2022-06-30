<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views
 * @category   CategoryName
 */

namespace open20\amos\core\views;

use open20\amos\core\views\common\BaseListView;
use open20\amos\gantt\widgets\GanttWidget;

class GanttView extends BaseListView
{
    public $model = null;

    public $clientOptions = [

    ];

    public $drag_links_permissions = null;

    public function run()
    {
        return \open20\amos\core\helpers\Html::tag(
            $this->itemsContainerTag,
            GanttWidget::widget([
                'model' => $this->model,
                'clientOptions' => $this->clientOptions,
                'drag_links_permissions' => $this->drag_links_permissions
            ]),
            $this->itemsContainerOptions
        );
    }


}
