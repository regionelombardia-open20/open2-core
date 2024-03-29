<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\toolbars
 * @category   CategoryName
 */

namespace open20\amos\core\views\toolbars;

use open20\amos\core\icons\AmosIcons;
use Yii;
use open20\amos\core\module\BaseAmosModule;

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
            'description' => BaseAmosModule::t('amoscore', '#StatsBar_Interventions'),
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
            'description' => BaseAmosModule::t('amoscore', '#StatsBar_Tags'),
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
            'description' => BaseAmosModule::t('amoscore', '#StatsBar_Attachments'),
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
            'description' => BaseAmosModule::t('amoscore', '#StatsBar_Reports'),
            'url' => $model->getViewUrl(),
        ]));
    }
}
