<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\utilities
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;

use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;
use yii\db\ActiveQuery;
use yii\log\Logger;

/**
 * Class FormUtility
 * @package open20\amos\core\utilities
 */
class FormUtility
{
    /**
     * Return the error triangle to put in a tab header.
     * @return string
     */
    public static function tabErrorTriangle()
    {
        return Html::tag('span', '&nbsp; ' . AmosIcons::show('alert-triangle'), [
            'id' => 'errore-alert-common',
            'class' => 'errore-alert hidden',
            'title' => BaseAmosModule::t('amoscore', 'La tab contiene degli errori')
        ]);
    }

    /**
     * This method link two models.
     * @param Record $startModel
     * @param Record $modelToLink
     * @param string $relationToLink
     * @return bool
     */
    public static function linkModels($startModel, $modelToLink, $relationToLink)
    {
        $ok = true;
        try {
            $startModel->link($relationToLink, $modelToLink);
        } catch (\Exception $exception) {
            $ok = false;
            \Yii::getLogger()->log($exception->getMessage(), Logger::LEVEL_ERROR);
        }
        return $ok;
    }

    /**
     * Save all values selected by user in a multi select field.
     * @param array $attrMmPost
     * @param string $mmModelClassName
     * @param string $firstIdField
     * @param int $firstIdValue
     * @param string $secondIdField
     * @return bool
     */
    public static function saveMmsFields($attrMmPost, $mmModelClassName, $firstIdField, $firstIdValue, $secondIdField)
    {
        $allOk = true;
        if (!empty($attrMmPost)) {
            if (!is_array($attrMmPost)) {
                $attrMmPost = [$attrMmPost];
            }
            foreach ($attrMmPost as $attrId) {
                $ok = self::saveMmField($mmModelClassName, $firstIdField, $firstIdValue, $secondIdField, $attrId);
                if (!$ok) {
                    $allOk = false;
                }
            }
        }
        return $allOk;
    }

    /**
     * Save an mm value in MM table.
     * @param string $mmModelClassName
     * @param string $firstIdField
     * @param string $secondIdField
     * @param mixed $firstIdValue
     * @param mixed $secondIdValue
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public static function saveMmField($mmModelClassName, $firstIdField, $firstIdValue, $secondIdField, $secondIdValue)
    {
        /** @var Record $mmModelClassName */
        /** @var ActiveQuery $query */
        $query = $mmModelClassName::find();
        $exists = $query->andWhere([
            $firstIdField => $firstIdValue,
            $secondIdField => $secondIdValue,
        ])->exists();
        if ($exists) {
            return true;
        }
        /** @var \open20\amos\core\record\Record $attrMmModel */
        $attrMmModel = new $mmModelClassName();
        $attrMmModel->{$firstIdField} = $firstIdValue;
        $attrMmModel->{$secondIdField} = $secondIdValue;
        $ok = $attrMmModel->save(false);
        return $ok;
    }

    /**
     * Return an array with the values used in boolean fields. If the param 'invertValues' is true the values are returned inverted.
     * @param bool $invertValues
     * @return array
     */
    public static function getBooleanFieldsValues($invertValues = false)
    {
        if ($invertValues) {
            return [
                Html::BOOLEAN_FIELDS_VALUE_YES => BaseAmosModule::t('amoscore', 'Yes'),
                Html::BOOLEAN_FIELDS_VALUE_NO => BaseAmosModule::t('amoscore', 'No')
            ];
        } else {
            return [
                Html::BOOLEAN_FIELDS_VALUE_NO => BaseAmosModule::t('amoscore', 'No'),
                Html::BOOLEAN_FIELDS_VALUE_YES => BaseAmosModule::t('amoscore', 'Yes')
            ];
        }
    }
}
