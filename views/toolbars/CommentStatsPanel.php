<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\toolbars
 * @category   CategoryName
 */

namespace open20\amos\core\views\toolbars;

use yii\helpers\ArrayHelper;

class CommentStatsPanel extends StatsPanel
{
    /**
     * @return string
     */
    protected function renderHtml(){
        $url = $this->url;
        $options = [
            'title' => $this->description
        ];
        $content = "{$this->icon} ({$this->count}) {$this->label}";
        if ($this->disableLink) {
            return $content;
        } else {
            return \open20\amos\core\helpers\Html::a($content, $url, $options);
        }
    }

    /**
     * @return string
     */
    protected function renderJavascript(){

        return $this->renderHtml();
    }
}