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

use open20\amos\core\exceptions\AmosException;
use open20\amos\core\module\BaseAmosModule;

/**
 * Class CwhUtility
 * @package open20\amos\core\utilities
 */
class CwhUtility
{

    /** 
     * 
     * @param type $model
     * @return string
     */
    public static function getTargetsString($model) 
    { 
        $targetString = null;
        if (!empty($model->validatori)) {
            $validatorName = self::getValidatorName($model->validatori);
            if ($validatorName != "") {
                $targetString = $validatorName;
            }
        }
        return $targetString;
    }

    /**
     * @param $validators
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getValidatorName($validators)
    {
        $validatorsCollection = \open20\amos\cwh\models\CwhNodi::findAll([
                'id' => $validators
        ]);

        $validatorsArr = [];
        /** @var CwhNodi $target */
        foreach ($validatorsCollection as $singleValidator) {
            if (!(strpos($singleValidator->id, 'user') !== false)) {
                $targetString = "";
                if (array_key_exists('open20\amos\community\models\CommunityContextInterface',
                        class_implements(self::findNode($singleValidator)))) {                  
                    $targetString .= BaseAmosModule::t('amoscore', '#item_card_header_widget_from_community').' ';
                }
                if (array_key_exists('open20\amos\core\interfaces\OrganizationsModelInterface',
                        class_implements(self::findNode($singleValidator)))) {
                    $targetString .= BaseAmosModule::t('amoscore', '#item_card_header_widget_from_organization').' ';
                }
                $validatorsArr[] = $targetString.self::findNode($singleValidator)->toStringWithCharLimit(-1);
            }
        }

        return implode(', ', $validatorsArr);
    }

    /**
     * @param array $Target
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    protected static function findNode($Target)
    {
        $modelClass = \Yii::createObject($Target['classname']);
        $model      = $modelClass->findOne($Target['record_id']);
        return $model;
    }
}