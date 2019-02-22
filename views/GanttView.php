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
use lispa\amos\gantt\widgets\GanttWidget;

class GanttView extends BaseListView
{
    public $model = null;

    public $clientOptions = [

    ];

    public $drag_links_permissions = null;

    public function run()
    {
        return \lispa\amos\core\helpers\Html::tag(
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
