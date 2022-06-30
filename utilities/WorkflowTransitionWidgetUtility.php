<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\utilities
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;

use open20\amos\core\interfaces\WorkflowMetadataInterface;
use open20\amos\core\module\AmosModule;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;
use raoul2000\workflow\base\Status;
use yii\base\BaseObject;

/**
 * Class WorkflowTransitionWidgetUtility
 * @package open20\amos\core\utilities
 */
class WorkflowTransitionWidgetUtility extends BaseObject
{
    /**
     * This method return the workflow status label if is set in the metadata field.
     * @param Record $model
     * @param string $key
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getLabelStatus($model, $key = '')
    {
        if (!$key) {
            $key = $model->getWorkflowStatus()->getId();
        }
        /** @var Status $status */
        $status = $model->getWorkflowSource()->getStatus($key, $model);
        $label = '';
        if ($status) {
            $label = self::getLabelStatusFromMetadata($model, $status);
        }
        return $label;
    }

    /**
     * This method return the workflow status label if is set in the metadata field.
     * @param Record $model
     * @param Status|\raoul2000\workflow\base\StatusInterface $status
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getLabelStatusFromMetadata($model, $status)
    {
        // Default metadata key
        $defaultLabelKey = 'label';
        $metadati = $status->getMetaData();
        $statusLabelKey = (($model instanceof WorkflowMetadataInterface) ? $model->getMetadataLabelKey() : $defaultLabelKey);
        if (isset($metadati[$statusLabelKey])) {
            $label = $metadati[$statusLabelKey];
        } else if (isset($metadati[$defaultLabelKey])) {
            $label = $metadati[$defaultLabelKey];
        } else {
            $label = $status->label;
        }
        return $label;
    }

    /**
     * This method return the workflow status button label if is set in the metadata field.
     * @param AmosModule $module
     * @param Record|WorkflowMetadataInterface $model
     * @param array $metadati
     * @param string $currentStatus
     * @param string $translationCategory
     * @return string
     */
    public static function getStatusButtonLabel($module, $model, $metadati, $currentStatus, $translationCategory)
    {
        // Default metadata keys
        $defaultLabelKey = 'label';
        $defaultLButtonLabelKey = 'buttonLabel';

        // Custom metadata keys returned by model methods
        if ($model instanceof WorkflowMetadataInterface) {
            $labelKey = $model->getMetadataLabelKey();
            $buttonLabelKey = $model->getMetadataButtonLabelKey();
        } else {
            $labelKey = $defaultLabelKey;
            $buttonLabelKey = $defaultLButtonLabelKey;
        }

        if (!empty($metadati[$currentStatus . '_' . $buttonLabelKey])) {
            $buttonLabel = $module::t($translationCategory, $metadati[$currentStatus . '_' . $buttonLabelKey]);
        } else if (!empty($metadati[$currentStatus . '_' . $defaultLButtonLabelKey])) {
            $buttonLabel = $module::t($translationCategory, $metadati[$currentStatus . '_' . $defaultLButtonLabelKey]);
        } else {
            if (!empty($metadati[$currentStatus . '_' . $labelKey])) {
                $buttonLabel = $module::t($translationCategory, $metadati[$currentStatus . '_' . $labelKey]);
            } else if (!empty($metadati[$currentStatus . '_' . $defaultLabelKey])) {
                $buttonLabel = $module::t($translationCategory, $metadati[$currentStatus . '_' . $defaultLabelKey]);
            } else if (isset($metadati[$buttonLabelKey])) {
                $buttonLabel = $module::t($translationCategory, $metadati[$buttonLabelKey]);
            } else if (isset($metadati[$defaultLButtonLabelKey])) {
                $buttonLabel = $module::t($translationCategory, $metadati[$defaultLButtonLabelKey]);
            } else if (isset($metadati[$labelKey])) {
                $buttonLabel = $module::t($translationCategory, $metadati[$labelKey]);
            } else if (isset($metadati[$defaultLabelKey])) {
                $buttonLabel = $module::t($translationCategory, $metadati[$defaultLabelKey]);
            } else {
                $buttonLabel = BaseAmosModule::t('amoscore', 'Change status');
            }
        }

        return $buttonLabel;
    }

    /**
     * This method return the workflow status button description if is set in the metadata field.
     * @param AmosModule $module
     * @param Record|WorkflowMetadataInterface $model
     * @param array $metadati
     * @param string $currentStatus
     * @param string $translationCategory
     * @return string
     */
    public static function getStatusButtonDescription($module, $model, $metadati, $currentStatus, $translationCategory)
    {
        // Default metadata key
        $defaultDescriptionKey = 'description';
        $descriptionKey = $defaultDescriptionKey;

        // Custom metadata key returned by model methods
        if ($model instanceof WorkflowMetadataInterface) {
            $descriptionKey = $model->getMetadataDescriptionKey();
        }

        if (isset($metadati[$currentStatus . '_' . $descriptionKey])) {
            $stateDescriptor = $module::t($translationCategory, $metadati[$currentStatus . '_' . $descriptionKey]);
        } elseif (isset($metadati[$currentStatus . '_' . $defaultDescriptionKey])) {
            $stateDescriptor = $module::t($translationCategory, $metadati[$currentStatus . '_' . $defaultDescriptionKey]);
        } elseif (isset($metadati[$descriptionKey])) {
            $stateDescriptor = $module::t($translationCategory, $metadati[$descriptionKey]);
        } elseif (isset($metadati[$defaultDescriptionKey])) {
            $stateDescriptor = $module::t($translationCategory, $metadati[$defaultDescriptionKey]);
        } else {
            $stateDescriptor = '';
        }

        return $stateDescriptor;
    }

    /**
     * This method return the workflow status button data confirm if is set in the metadata field.
     * @param AmosModule $module
     * @param Record|WorkflowMetadataInterface $model
     * @param array $metadati
     * @param string $currentStatus
     * @param string $translationCategory
     * @param string|null $dataConfirmParam
     * @return string
     */
    public static function getStatusButtonDataConfirm($module, $model, $metadati, $currentStatus, $translationCategory, $dataConfirmParam = null)
    {
        // Default metadata key
        $defaultMessageKey = 'message';
        $messageKey = $defaultMessageKey;

        // Custom metadata key returned by model methods
        if ($model instanceof WorkflowMetadataInterface) {
            $messageKey = $model->getMetadataButtonMessageKey();
        }

        if (isset($metadati[$currentStatus . '_' . $messageKey])) {
            $dataConfirm = $module::t($translationCategory, $metadati[$currentStatus . '_' . $messageKey]);
        } elseif (isset($metadati[$messageKey])) {
            $dataConfirm = $module::t($translationCategory, $metadati[$messageKey]);
        } elseif (!is_null($dataConfirmParam)) {
            $dataConfirm = $dataConfirmParam;
        } else {
            $dataConfirm = BaseAmosModule::t('amoscore', 'Are you sure you want to change status?');
        }

        return $dataConfirm;
    }
}
