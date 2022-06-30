<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migration
 * @category   CategoryName
 */

namespace open20\amos\core\migration;

use open20\amos\core\module\BaseAmosModule;
use open20\amos\dashboard\models\AmosWidgets;
use open20\amos\core\behaviors\BlameableBehavior;
use yii\db\Migration;

/**
 * Class AmosMigrationWidgets
 * @package open20\amos\core\migration
 */
class AmosMigrationWidgets extends Migration
{
    /**
     * @var array $widgets This array contains all widgets configurations.
     */
    protected $widgets;

    /**
     * @var array $fieldsToSkip Fields to skip during insert or update of a widget.
     */
    protected $fieldsToSkip = [
        'update',
        'old_classname'
    ];

    /**
     */
    public function init()
    {
        parent::init();
        $this->db->enableSchemaCache = false;
        $this->initWidgetsConfs();
    }

    /**
     * In this method you must define the widgets configurations. You must set the configurations in the global "$this->widgets = []" array.
     * A single widget configuration is an array. You must set these minimum keys: classname, type, module, status.
     * You can also set these other keys: child_of, default_order, update.
     * Key "classname": the classname of the widget (string)
     * Key "type": the type of the widget, icon or graphic, defined with AmosWidgets::TYPE_ICON or AmosWidgets::TYPE_GRAPHIC (string)
     * Key "module": the module that is referenced by the widget (string)
     * Key "status": the status of the widget, defined by AmosWidgets::STATUS_ENABLED or AmosWidgets::STATUS_DISABLED (int)
     * Key "default_order": the default order of the widget used in tab dashboard initialization (int) (optional)
     * Key "update": if true means that updates the widget if it exists. The parameter is not considered if parameter "old_classname" is set (bool) (optional)
     * Key "old_classname": if is set means that the widget will be replaced if it exists. Only the other fields set will be overwritten by the corresponding field value. This mean that you can set only the "old_classname" and "classname" key in the conf array. (string) (optional).
     */
    protected function initWidgetsConfs()
    {
        $this->widgets = [];
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $allOk = $this->beforeAddWidgets();
        if ($allOk) {
            foreach ($this->widgets as $widgetData) {
                if (isset($widgetData['old_classname'])) {
                    $ok = $this->replaceExistentWidget($widgetData);
                    if (!$ok) {
                        $allOk = false;
                    }
                } else {
                    $ok = $this->insertOrUpdateWidget($widgetData);
                    if (!$ok) {
                        $allOk = false;
                    }
                }
            }
        }
        if ($allOk) {
            $allOk = $this->afterAddWidgets();
        }
        return $allOk;
    }

    /**
     * Override this to make operations before adding the widgets.
     * @return bool
     */
    public function beforeAddWidgets()
    {
        return true;
    }

    /**
     * Override this to make operations after adding the widgets.
     * @return bool
     */
    public function afterAddWidgets()
    {
        return true;
    }

    /**
     * Method to add a widget configuration.
     * @param array $widgetData Key => value array that contains all data to insert in AmosWidgets table.
     * @return bool
     */
    protected function insertOrUpdateWidget($widgetData)
    {
        $ok = true;
        $msg = "Widget " . $widgetData['classname'] . " ";

        if ($this->checkWidgetExist($widgetData, 'classname')) {
            $msg .= BaseAmosModule::t('amoscore', "exists") . ". ";
            if (isset($widgetData['update'])) {
                $msg .= BaseAmosModule::t('amoscore', "Updating") . "...";
                $ok = $this->updateExistentWidget($widgetData);
                $msg .= ($ok ? 'OK' : BaseAmosModule::t('amoscore', 'ERROR'));
            } else {
                $msg .= BaseAmosModule::t('amoscore', "Skipping") . "...";
            }
        } else {
            $ok = $this->saveWidget($widgetData);
            $msg .= ($ok ? BaseAmosModule::t('amoscore', "added") : BaseAmosModule::t('amoscore', "not added")) . ".";
        }

        echo $msg . "\n";

        return $ok;
    }

    /**
     * This method replace an existent widget if exists.
     * @param array $widgetData Key => value array that contains all widget data.
     * @return bool
     */
    protected function replaceExistentWidget($widgetData)
    {
        $ok = false;
        $msg = "Widget " . $widgetData['old_classname'] . " ";

        if ($this->checkWidgetExist($widgetData, 'old_classname')) {
            $msg .= BaseAmosModule::t('amoscore', "exists. Replacing") . "...";
            $widgetToReplace = AmosWidgets::findOne(['classname' => $widgetData['old_classname']]);
            $ok = $this->saveWidget($widgetData, $widgetToReplace);
            $msg .= ($ok ? "OK" : BaseAmosModule::t('amoscore', "ERROR"));
        } else {
            $msg .= BaseAmosModule::t('amoscore', "not exists. Replacement not possible") . "!!!";
        }

        echo $msg . "\n";

        return $ok;
    }

    /**
     * This method check if a widget already exists in the amos_widgets table. Return true if the widget exists.
     * @param array $widgetData The widget data.
     * @param string $classNameField The widget data class name field.
     * @return bool Returns true if the widget exists. False otherwise.
     */
    protected function checkWidgetExist($widgetData, $classNameField)
    {
        $className = $widgetData[$classNameField];
        $condition = ['classname' => $className];
        if (isset($widgetData['module'])) {
            $condition['module'] = $widgetData['module'];
        }
        $oldWidgets = AmosWidgets::findAll($condition);
        $countOldWidgets = count($oldWidgets);
        return ($countOldWidgets > 0);
    }

    /**
     * This method update an existent widget if exists.
     * @param array $widgetData Key => value array that contains all widget data.
     * @return bool
     */
    protected function updateExistentWidget($widgetData)
    {
        $condition = ['classname' => $widgetData['classname']];
        if (isset($widgetData['module'])) {
            $condition['module'] = $widgetData['module'];
        }
        $widgetToUpdate = AmosWidgets::findOne($condition);
        $ok = $this->saveWidget($widgetData, $widgetToUpdate);
        return $ok;
    }

    /**
     * This method save the widget in the amos_widgets table. It return true or false in case of success or failure.
     * @param array $widgetData Key => value array that contains all widget data.
     * @param AmosWidgets|null $widget Optional param. It's the widget to be updated.
     * @return bool
     */
    protected function saveWidget($widgetData, $widget = null)
    {
        $cleanedWidgetData = $this->cleanWidgetConf($widgetData);
        $adminId = 1;
        if (is_null($widget)) {
            $widget = new AmosWidgets();
            $widget->created_by = $adminId;
        }
        $amosWidgetsBehaviors = $widget->getBehaviors();
        foreach ($amosWidgetsBehaviors as $behaviorIndex => $amosWidgetsBehavior) {
            if (strcmp(get_class($amosWidgetsBehavior), BlameableBehavior::className()) == 0) {
                $widget->detachBehavior($behaviorIndex);
                break;
            }
        }
        $widget->detachBehavior('auditTrailBehavior');
        $widget->updated_by = $adminId;
        $widget->setAttributes($cleanedWidgetData);
        $ok = $widget->save(false);
        return $ok;
    }

    /**
     * This method clean widget conf and return an array ready to be inserted in the amos_widgets table.
     * @param array $widgetData Key => value array that contains all widget data.
     * @return array
     */
    protected function cleanWidgetConf($widgetData)
    {
        $cleanedWidgetData = [];
        foreach ($widgetData as $fieldName => $fieldValue) {
            if (!in_array($fieldName, $this->fieldsToSkip)) {
                $cleanedWidgetData[$fieldName] = $fieldValue;
            }
        }
        return $cleanedWidgetData;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $allOk = $this->beforeRemoveWidgets();
        if ($allOk) {
            /**
             * TODO SET FOREIGN_KEY_CHECKS funziona su MySQL ma non è detto che funzioni anche altrove, quindi le migration potrebbero scoppiare.
             * La soluzione sarebbe trovare l'elenco di tabelle che hanno un constraint con la tabella dei widget, cercare il widget in tutte le
             * altre tabelle ed eliminarlo dalle tabelle prima di cancellarlo dalla tabella dei widget.
             * SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME from INFORMATION_SCHEMA.KEY_COLUMN_USAGE where REFERENCED_TABLE_NAME = 'amos_widgets'
             */
            if ($this->db->driverName === 'mysql') {
                $this->execute("SET FOREIGN_KEY_CHECKS = 0;");
            }
            foreach ($this->widgets as $singleWidget) {
                if ((isset($singleWidget['dontRemove']) && $singleWidget['dontRemove']) || (isset($singleWidget['update']) && $singleWidget['update'])) {
                    continue;
                }
                $condition = ['and', ['like', 'classname', $singleWidget['classname']]];
                if (isset($singleWidget['module'])) {
                    $condition[] = ['like', 'module', $singleWidget['module']];
                }
                $this->delete(AmosWidgets::tableName(), $condition);
            }
            if ($this->db->driverName === 'mysql') {
                $this->execute("SET FOREIGN_KEY_CHECKS = 1;");
            }
        }
        if ($allOk) {
            $allOk = $this->afterRemoveWidgets();
        }
        return $allOk;
    }

    /**
     * Override this to make operations before removing the widgets.
     * @return bool
     */
    public function beforeRemoveWidgets()
    {
        return true;
    }

    /**
     * Override this to make operations after removing the widgets.
     * @return bool
     */
    public function afterRemoveWidgets()
    {
        return true;
    }
}
