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

use open20\amos\core\migration\libs\common\MigrationCommon;
use open20\amos\core\module\BaseAmosModule;
use yii\db\Migration;
use yii\db\Query;

/**
 * Class AmosMigrationWorkflow
 *
 * This class is useful to add all the configurations for a new workflow. The only method to override is "setWorkflow". This method
 * return an array of configurations array. In this array you can specify all workflow configuration. There are three type of workflow
 * configuration defined in class constants: self::TYPE_WORKFLOW, self::TYPE_WORKFLOW_STATUS, self::TYPE_WORKFLOW_TRANSITION, self::TYPE_WORKFLOW_METADATA.
 *
 * A single workflow configuration is also an array like this:
 * [
 *      'type' => "CONFIGURATION_TYPE",
 *      "OTHER_FIELDS"
 * ]
 *
 * A workflow configuration is like this:
 * [
 *      'type' => AmosMigrationWorkflow::TYPE_WORKFLOW,
 *      'id' => 'WORKFLOW_ID',
 *      'initial_status_id' => 'STATUS_ID'
 * ]
 *
 * A workflow status configuration is like this:
 * [
 *      'type' => AmosMigrationWorkflow::TYPE_WORKFLOW_STATUS,
 *      'id' => 'STATUS_ID',
 *      'workflow_id' => 'WORKFLOW_ID',
 *      'label' => 'Workflow status description',
 *      'sort_order' => 0 // An integer
 * ]
 *
 * A workflow status transition configuration is like this:
 * [
 *      'type' => AmosMigrationWorkflow::TYPE_WORKFLOW_TRANSITION,
 *      'workflow_id' => 'WORKFLOW_ID',
 *      'start_status_id' => 'START_STATUS_ID',
 *      'end_status_id' => 'END_STATUS_ID'
 * ]
 *
 * A workflow metadata configuration is like this:
 * [
 *      'type' => AmosMigrationWorkflow::TYPE_WORKFLOW_METADATA,
 *      'workflow_id' => 'WORKFLOW_ID',
 *      'status_id' => 'STATUS_ID',
 *      'key' => 'KEY_TO_ADD',
 *      'value' => 'KEY_VALUE'
 * ]
 *
 * The preferred method to create a migration that create a workflow and override "setWorkflow" method is like this:
 * protected function setWorkflow()
 * {
 *      return ArrayHelper::merge(
 *          parent::setWorkflow(),
 *          $this->workflowConf(),
 *          $this->workflowStatusConf(),
 *          $this->workflowTransitionsConf(),
 *          $this->workflowMetadataConf()
 *      );
 * }
 * The methods are all private and are all to be created at own developer discretion, because all the configurations can stay in one array in setWorkflow() method.
 *
 * @package open20\amos\core\migration
 */
class AmosMigrationWorkflow extends Migration
{
    const TYPE_WORKFLOW = 'workflow';
    const TYPE_WORKFLOW_STATUS = 'workflow_status';
    const TYPE_WORKFLOW_TRANSITION = 'workflow_transition';
    const TYPE_WORKFLOW_METADATA = 'workflow_metadata';

    /**
     * @var array $_allWorkflowConfTypes Array that contains all workflow configuration types. Useful in check array structure.
     */
    private $_allWorkflowConfTypes = [
        self::TYPE_WORKFLOW,
        self::TYPE_WORKFLOW_STATUS,
        self::TYPE_WORKFLOW_TRANSITION,
        self::TYPE_WORKFLOW_METADATA
    ];

    /**
     * @var bool $_processInverted If true switch safeUp and safeDown operations. This mean that in up the permissions are removed and in down the permissions are added.
     */
    private $_processInverted = false;

    /**
     * @var string $_errors Generic error variable.
     */
    private $_errors = '';

    /**
     * @var array $_fieldsToSkip Fields to skip when cleaning the single workflow configuration.
     */
    private $_fieldsToSkip = [
        'type',
        'remove'
    ];

    /**
     * @var array $_workflowConf This array contains all workflow configurations.
     */
    private $_workflowConf = [];

    /**
     * @var string $_workflowTable The workflow table name.
     */
    private $_workflowTable = '{{%sw_workflow}}';

    /**
     * @var string $_workflowStatusTable The workflow statuses table name.
     */
    private $_workflowStatusTable = '{{%sw_status}}';

    /**
     * @var string $_workflowTransitionsTable The workflow status transitions table name.
     */
    private $_workflowTransitionsTable = '{{%sw_transition}}';

    /**
     * @var string $_workflowMetadataTable The workflow metadata table name.
     */
    private $_workflowMetadataTable = '{{%sw_metadata}}';

    /**
     * @var array $_workflowFieldsToCheck Array with the fields to check in the workflow table.
     */
    private $_workflowFieldsToCheck = ['id'];

    /**
     * @var array $_workflowStatusFieldsToCheck Array with the fields to check in the workflow statuses table.
     */
    private $_workflowStatusFieldsToCheck = ['id', 'workflow_id'];

    /**
     * @var array $_workflowTransitionsFieldsToCheck Array with the fields to check in the workflow status transitions table.
     */
    private $_workflowTransitionsFieldsToCheck = ['workflow_id', 'start_status_id', 'end_status_id'];

    /**
     * @var array $_workflowMetadataFieldsToCheck Array with the fields to check in the workflow metadata table.
     */
    private $_workflowMetadataFieldsToCheck = ['workflow_id', 'status_id', 'key'];

    /**
     * @return bool
     */
    public function isProcessInverted()
    {
        return $this->_processInverted;
    }

    /**
     * @param bool $processInverted
     */
    public function setProcessInverted($processInverted)
    {
        $this->_processInverted = $processInverted;
    }

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        $this->db->enableSchemaCache = false;
        $this->_workflowConf = $this->setWorkflow();

        if (empty($this->_workflowConf)) {
            throw new \Exception(BaseAmosModule::t('amoscore', 'The workflow configuration array is empty'));
        }

        $isOkArrayStructure = $this->checkArrayStructure();
        if (!$isOkArrayStructure) {
            throw new \Exception(BaseAmosModule::t('amoscore', 'The structure of the workflow configuration array is not correct') . $this->_errors);
        }
    }

    /**
     * Override this method to set all the workflow configurations.
     * @return array
     */
    protected function setWorkflow()
    {
        return [];
    }

    /**
     * This method checks the entire array structure element by element and set the errors in the global variable $this->_errors used in the exception message to print errors to developer.
     * @return bool
     */
    private function checkArrayStructure()
    {
        $ok = true;

        while (list($index, $workflowConf) = each($this->_workflowConf)) {
            // Check if the single workflow conf array is an array
            if (!is_array($workflowConf)) {
                $this->_errors .= "\n" . BaseAmosModule::t('amoscore', 'Workflow conf element') . " '" . $index . "' " . BaseAmosModule::t('amoscore', 'is not an array');
                $ok = false;
                continue;
            }
            // Check if there is the "type" key in the single workflow conf array
            if (!isset($workflowConf['type'])) {
                $this->_errors .= "\n" . BaseAmosModule::t('amoscore', 'Workflow conf element') . " '" . $index . "' " . BaseAmosModule::t('amoscore', "has not the 'type' key");
                $ok = false;
                continue;
            }
            // Check if the "type" key is one of the possible types
            if (!in_array($workflowConf['type'], $this->_allWorkflowConfTypes)) {
                $this->_errors .= "\n" . BaseAmosModule::t('amoscore', 'Workflow conf element') . " '" . $index . "' " . BaseAmosModule::t('amoscore', "has not valid conf type");
                $ok = false;
                continue;
            }
        }

        return $ok;
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->isProcessInverted()) {
            return $this->manageRemoveConfs();
        } else {
            return $this->manageAddConfs();
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        if ($this->isProcessInverted()) {
            return $this->manageAddConfs();
        } else {
            return $this->manageRemoveConfs();
        }
    }

    /**
     * This method manages the addition of workflow configurations.
     * @return bool
     */
    private function manageAddConfs()
    {
        $ok = $this->beforeAddConfs();
        if ($ok) {
            $ok = $this->addConfs();
        }
        if ($ok) {
            $ok = $this->afterAddConfs();
        }
        return $ok;
    }

    /**
     * Override this to make operations before adding the workflow configurations.
     * @return bool
     */
    protected function beforeAddConfs()
    {
        return true;
    }

    /**
     * This method add all workflow configurations set in the global array. It verify if a configuration is already present.
     * If the configuration not exists the method create it, otherwise it goes over.
     * @return  boolean
     */
    private function addConfs()
    {
        $ok = true;
        foreach ($this->_workflowConf as $workflowConf) {
            if (isset($workflowConf['remove']) && $workflowConf['remove']) {
                $ok = $this->removeConf($workflowConf);
            } else {
                $ok = $this->createConf($workflowConf);
            }
            if (!$ok) {
                break;
            }
        }
        return $ok;
    }

    /**
     * Method useful to add a single workflow configuration.
     * @param array $workflowConf Key => value array that contains a workflow configuration.
     * @return bool
     */
    private function createConf($workflowConf)
    {
        $newConf = $this->cleanWorkflowConf($workflowConf);
        $utilWorkflow = $this->getTablenameAndFieldsToCheck($workflowConf['type'], $newConf);
        if ($this->checkConfExist($utilWorkflow['tableName'], $utilWorkflow['fieldsToCheck'], $newConf)) {
            echo $utilWorkflow['messages']['infoMsg'];
        } else {
            try {
                $this->insert($utilWorkflow['tableName'], $newConf);
            } catch (\Exception $exception) {
                echo $utilWorkflow['messages']['errorMsg'];
                echo $exception->getMessage() . "\n";
                return false;
            }
            echo $utilWorkflow['messages']['successMsg'];
        }
        return true;
    }

    /**
     * @param array $workflowConf
     * @return array
     */
    private function cleanWorkflowConf($workflowConf)
    {
        $newConf = [];
        foreach ($workflowConf as $fieldName => $fieldValue) {
            if (!in_array($fieldName, $this->_fieldsToSkip)) {
                $newConf[$fieldName] = $fieldValue;
            }
        }
        return $newConf;
    }

    /**
     * The method checks the configuration type and create an array with the appropriate tablename, fields to check
     * and messages to print in the console after add workflow configuration.
     * @param string $workflowConfType The workflow configuration type. One of these: self::TYPE_WORKFLOW, self::TYPE_WORKFLOW_STATUS, self::TYPE_WORKFLOW_TRANSITION
     * @param array $newConf Optional array that contains a single workflow configuration.
     * @return array
     */
    private function getTablenameAndFieldsToCheck($workflowConfType, $newConf = [])
    {
        $tableName = '';
        $fieldsToCheck = [];
        $messages = [];

        switch ($workflowConfType) {
            case self::TYPE_WORKFLOW:
                $tableName = $this->_workflowTable;
                $fieldsToCheck = $this->_workflowFieldsToCheck;
                if (!empty($newConf)) {
                    $confName = $this->composeConsoleConfName($fieldsToCheck, $newConf);
                    $messages = [
                        'infoMsg' => "Workflow $confName esistente. Skippo...\n",
                        'successMsg' => "Workflow $confName creato.\n",
                        'errorMsg' => "Errore durante la creazione del workflow $confName.\n"
                    ];
                }
                break;
            case self::TYPE_WORKFLOW_STATUS:
                $tableName = $this->_workflowStatusTable;
                $fieldsToCheck = $this->_workflowStatusFieldsToCheck;
                if (!empty($newConf)) {
                    $confName = $this->composeConsoleConfName($fieldsToCheck, $newConf);
                    $messages = [
                        'infoMsg' => "Stato $confName esistente. Skippo...\n",
                        'successMsg' => "Stato $confName creato.\n",
                        'errorMsg' => "Errore durante la creazione dello stato $confName.\n"
                    ];
                }
                break;
            case self::TYPE_WORKFLOW_TRANSITION:
                $tableName = $this->_workflowTransitionsTable;
                $fieldsToCheck = $this->_workflowTransitionsFieldsToCheck;
                if (!empty($newConf)) {
                    $confName = $this->composeConsoleConfName($fieldsToCheck, $newConf);
                    $messages = [
                        'infoMsg' => "Transizione $confName esistente. Skippo...\n",
                        'successMsg' => "Transizione $confName creata.\n",
                        'errorMsg' => "Errore durante la creazione della transizione $confName.\n"
                    ];
                }
                break;
            case self::TYPE_WORKFLOW_METADATA:
                $tableName = $this->_workflowMetadataTable;
                $fieldsToCheck = $this->_workflowMetadataFieldsToCheck;
                if (!empty($newConf)) {
                    $confName = $this->composeConsoleConfName($fieldsToCheck, $newConf);
                    $messages = [
                        'infoMsg' => "Metadata $confName esistente. Skippo...\n",
                        'successMsg' => "Metadata $confName creata.\n",
                        'errorMsg' => "Errore durante la creazione del metadata $confName.\n"
                    ];
                }
                break;
        }

        return [
            'tableName' => $tableName,
            'fieldsToCheck' => $fieldsToCheck,
            'messages' => $messages
        ];
    }

    /**
     * Returns the configuration name ready to be viewed in console.
     * @param array $fieldsToCheck Array that contains the fields to be verified.
     * @param array $newConf Array that contains the values to be inserted in the table.
     * @return string
     */
    private function composeConsoleConfName($fieldsToCheck, $newConf)
    {
        $confName = '';
        foreach ($fieldsToCheck as $fieldName) {
            if (strlen($confName) > 0) {
                $confName .= ' - ';
            }
            $confName .= $newConf[$fieldName];
        }
        return $confName;
    }

    /**
     * Method that verify the existence of the configuration. It use the fields to check to make the verify.
     * @param string $tablename The table name
     * @param array $fieldsToCheck The fields to check on the table.
     * @param array $fieldsValues The configuration values to check.
     * @return bool
     */
    private function checkConfExist($tableName, $fieldsToCheck, $fieldsValues)
    {
        $query = new Query();
        $query->from($tableName);
        foreach ($fieldsToCheck as $fieldName) {
            $query->andWhere([$fieldName => $fieldsValues[$fieldName]]);
        }
        $valuesCount = $query->count();
        return ($valuesCount > 0);
    }

    /**
     * Override this to make operations after adding the workflow configurations.
     * @return bool
     */
    protected function afterAddConfs()
    {
        return true;
    }

    /**
     * This method manages the remove of workflow configurations.
     * @return bool
     */
    public function manageRemoveConfs()
    {
        try {
            $ok = $this->beforeRemoveConfs();
            if ($ok) {
                $ok = $this->removeConfs();
            }
            if ($ok) {
                $ok = $this->afterRemoveConfs();
            }
        } catch (\Exception $exception) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Errore durante la rimozione del workflow'));
            MigrationCommon::printConsoleMessage($exception->getMessage());
            return false;
        }
        return $ok;
    }

    /**
     * Override this to make operations before removing the workflow configurations.
     * @return bool
     */
    protected function beforeRemoveConfs()
    {
        return true;
    }

    /**
     * Method useful to remove all the configurations.
     * @return boolean
     */
    private function removeConfs()
    {
        $ok = true;
        foreach ($this->_workflowConf as $workflowConf) {
            if (isset($workflowConf['remove']) && $workflowConf['remove']) {
                $ok = $this->createConf($workflowConf);
            } else {
                $ok = $this->removeConf($workflowConf);
            }
            if (!$ok) {
                break;
            }
        }
        return $ok;
    }

    /**
     * Method that remove the single configuration.
     * @param array $workflowConf
     */
    private function removeConf($workflowConf)
    {
        $ok = true;
        $utilWorkflow = $this->getTablenameAndFieldsToCheck($workflowConf['type']);
        $where = [];
        foreach ($utilWorkflow['fieldsToCheck'] as $fieldName) {
            $where[$fieldName] = $workflowConf[$fieldName];
        }
        try {
            $this->delete($utilWorkflow['tableName'], $where);
        } catch (\Exception $exception) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Errore durante la rimozione della configurazione del workflow'));
            MigrationCommon::printConsoleMessage($exception->getMessage());
            $ok = false;
        }
        return $ok;
    }

    /**
     * Override this to make operations after removing the workflow configurations.
     * @return bool
     */
    protected function afterRemoveConfs()
    {
        return true;
    }
}
