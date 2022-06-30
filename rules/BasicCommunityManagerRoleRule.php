<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\community\rules
 * @category   CategoryName
 */

namespace open20\amos\core\rules;


use yii\helpers\Url;

/**
 * Class CreateSubcommunitiesRule
 * @package open20\amos\community\rules
 */
class BasicCommunityManagerRoleRule extends DefaultOwnContentRule
{
    /**
     * @inheritdoc
     */
    public $name = 'basicCommunityManagerRole';

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        // used by deleteCommunityMnagerContentRule that extend this class in every content,
        // at moment is disabled to avoid that a community manager can delete a content published by an other user in another community


//        $cwhModule = \Yii::$app->getModule('cwh');
//        if (isset($params['model'])) {
//            if (isset($cwhModule)) {
//                $cwhModule->setCwhScopeFromSession();
//                if (!empty($cwhModule->userEntityRelationTable)) {
//                    $entityId = $cwhModule->userEntityRelationTable['entity_id'];
//                    $model =  \open20\amos\community\models\Community::findOne($entityId);
//                    return $this->hasRole($user, $model);
//                }
//            }
//        }
        return false;
    }

    /**
     * @param $user_id
     * @param $model Community
     * @return bool
     */
    public function hasRole($user_id, $model){
        $communityUserMm = \open20\amos\community\models\CommunityUserMm::find()
            ->andWhere(['user_id' => $user_id])
            ->andWhere(['community_id' => $model->id])
            ->andWhere(['role' => \open20\amos\community\models\CommunityUserMm::ROLE_COMMUNITY_MANAGER])
            ->one();

        if(!empty($communityUserMm)){
            return true;
        }
        return false;
    }
}
