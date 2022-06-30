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

/**
 * Interface ContentModelInterface
 *
 * Must be implemented by those model that provides contents to share/publish such as
 * News, Discussioni, Documenti, ..
 *
 * @package open20\amos\core\record
 */
interface ContentModelInterface extends BaseContentModelInterface, WorkflowModelInterface, ModelLabelsInterface
{
    /**
     * @return array The columns ti show as default in GridViewWidget
     */
    public function getGridViewColumns();

    /**
     * @return DateTime date begin of publication
     */
    public function getPublicatedFrom();

    /**
     * @return DateTime date end of publication
     */
    public function getPublicatedAt();

    /**
     * @return \yii\db\ActiveQuery category of content
     */
    public function getCategory();

    /**
     * @return string The classname of the generic dashboard widget to access the plugin
     */
    public function getPluginWidgetClassname();
}
