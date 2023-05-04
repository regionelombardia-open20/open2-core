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
use open20\amos\core\record\CachedActiveQuery;

/**
 * Class CwhUtility
 * @package open20\amos\core\utilities
 */
class CwhUtility {

    /**
     * 
     * @param type $model
     * @return string
     */
    public static function getTargetsString($model) {
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
    public static function getValidatorName($validators) {
        $validatorsCollectionQuery = \open20\amos\cwh\models\CwhNodi::find()
                ->andWhere(['id' => $validators])
                ->select(['record_id', 'id', 'classname']);
        $validatorsQuery = CachedActiveQuery::instance($validatorsCollectionQuery);
        $validatorsQuery->cache(60);
        $validatorsCollection = $validatorsQuery->asArray()->all();

        $validatorsArr = [];
        /** @var CwhNodi $target */
        foreach ($validatorsCollection as $singleValidator) {


            if (!(strpos($singleValidator['id'], 'user') !== false)) {

                $targetString = "";
                if (array_key_exists('open20\amos\community\models\CommunityContextInterface',
                                class_implements($singleValidator['classname']))) {
                    $targetString .= BaseAmosModule::t('amoscore', '#item_card_header_widget_from_community') . ' ';
                }
                if (array_key_exists('open20\amos\core\interfaces\OrganizationsModelInterface',
                                class_implements($singleValidator['classname']))) {
                    $targetString .= BaseAmosModule::t('amoscore', '#item_card_header_widget_from_organization') . ' ';
                }
                $fNode = self::findNode($singleValidator);
                if (!is_null($fNode)) {
                    $validatorsArr[] = $targetString . $fNode->toStringWithCharLimit(-1);
                }
            }
        }

        return implode(', ', $validatorsArr);
    }

    /**
     * @param array $Target
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    protected static function findNode($Target) {
        $modelClass = $Target['classname'];
        $activeQuery = $modelClass::find()->andWhere(['id' => $Target['record_id']]);
        $modelQuery = CachedActiveQuery::instance($activeQuery);
        $modelQuery->cache(60);
        $model = $modelQuery->one();

        return $model;
    }

}
