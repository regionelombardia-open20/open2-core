<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\toolbars
 * @category   CategoryName
 */

namespace lispa\amos\core\views\toolbars;

use lispa\amos\core\icons\AmosIcons;
use Yii;

class StatsToolbarPanels
{
    /**
     * @param $model
     * @param $count
     * @return array
     */
    public static function getCommentsPanel($model, $count, $disableLink = false)
    {
        return array('comments' => new CommentStatsPanel([
            'icon' => AmosIcons::show('comments'),
            'label' => '',
            'description' => \Yii::t('amoscore', '#StatsBar_Interventions'),
            'count' => $count,
            'disableLink' => $disableLink,
            'url' => Yii::$app->getUrlManager()->createUrl([
                $model->getViewUrl(),
                'id' => $model->getPrimaryKey(),
                '#' => 'comments_anchor',
            ])
        ]));
    }

    /**
     * @param $model
     * @param $count
     * @return array
     */
    public static function getTagsPanel($model, $count, $disableLink = false)
    {
        return array('tags' => new StatsPanel([
            'icon' => AmosIcons::show('label'),
            'label' => '',
            'count' => $count,
            'disableLink' => $disableLink,
            'description' => Yii::t('amoscore', '#StatsBar_Tags'),
            'url' => Yii::$app->getUrlManager()->createUrl([
                $model->getViewUrl(),
                'id' => $model->getPrimaryKey(),
                '#' => 'tab-classifications'
            ])
        ]));
    }

    /**
     * @param $model
     * @param $count
     * @return array
     */
    public static function getDocumentsPanel($model, $count, $disableLink = false)
    {
        return array('documents' => new StatsPanel([
            'icon' => AmosIcons::show('paperclip', [], 'dash'),
            'label' => '',
            'count' => $count, //calculate only attach and not principal files.
            'disableLink' => $disableLink,
            'description' => Yii::t('amoscore', '#StatsBar_Attachments'),
            'url' => \Yii::$app->getUrlManager()->createUrl([
                $model->getViewUrl(),
                'id' => $model->getPrimaryKey(),
                '#' => 'tab-attachments'
            ])
        ]));
    }

    /**
     * @param $model
     * @param $count
     * @return array
     */
    public static function getReportsPanel($model, $count, $disableLink = false)
    {
        return array('reports' => new StatsPanel([
            'icon' => AmosIcons::show('flag', [], 'dash'),
            'label' => '',
            'count' => $count,
            'disableLink' => $disableLink,
            'description' => Yii::t('amoscore', '#StatsBar_Reports'),
            'url' => $model->getViewUrl(),
        ]));
    }
}
