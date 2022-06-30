<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\record
 * @category   CategoryName
 */

namespace open20\amos\core\record;

use open20\amos\admin\models\UserProfile;
use open20\amos\core\user\User;
use open20\amos\cwh\base\ModelNetworkInterface;
use open20\amos\cwh\models\CwhAuthAssignment;
use open20\amos\cwh\models\CwhConfig;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * Class networkModel
 * @package open20\amos\core\record
 *
 * @property Record[] $networkUserMms
 */
abstract class NetworkModel extends ContentModel implements ModelNetworkInterface
{


    public function hasSubNetworks()
    {
        return false;
    }

    /**
     * @param array $params
     * @param bool|false $onlyActiveStatus
     * @return ActiveQuery $query
     */
    public function buildQuery($params, $queryType, $onlyActiveStatus = false, $userId = null)
    {
        $query = $this->baseSearch($params);

        if(is_null($userId))
        {
//            $userId = Yii::$app->getUser()->getId();
            $userId = Yii::$app->user->getId();
        }

        switch ($queryType) {
            case 'created-by':
                $query->andFilterWhere([static::tableName().'.created_by' => $userId]);
                break;
            case 'all':
                /** @var ActiveQuery $query */
                $query = $this->baseSearch($params);
                if(static::getVisibility() != '1') {
                    $this->getVisibleNetworksQuery($query, $params, $onlyActiveStatus, $userId);
                }
                $this->filterValidated($query);
                break;
            case'to-validate':
                if(!is_null($this->getToValidateStatus())){
                    $query->andFilterWhere([static::tableName().'.status' => $this->getToValidateStatus()]);
                }
                if($this->hasSubNetworks()) {
                    $this->getSubNetworkToValidateQuery($query);
                }
                break;
            case 'own-interest':
                $this->filterValidated($query);
                $query->innerJoin($this->getMmTableName(), static::tableName().'.id = ' . $this->getMmTableName() . '.'.$this->getMmNetworkIdFieldName()
                    . ' AND ' . $this->getMmTableName() . '.'. $this->getMmUserIdFieldName().' = ' . $userId)
                    ->andWhere($this->getMmTableName() . '.deleted_at is null');
                if ($onlyActiveStatus) {
                    $mmTable = Yii::$app->db->schema->getTableSchema($this->getMmTableName());
                    if(isset($mmTable->columns['status'])) {
                        $query->andWhere("ISNULL(".$this->getMmTableName().".status) OR ".$this->getMmTableName().".status = 'ACTIVE'");
                    }
                }
                break;
            case 'own-interest-with-tags':
                $this->filterValidated($query);
                /** @var User $loggedUser */
                $loggedUser = Yii::$app->user->identity;
                $loggedUserId = $loggedUser->id;
                $loggedUserProfileId = $loggedUser->userProfile->id;
                $usersTags = $this->getUsersTag($loggedUserProfileId);
                $usersCommunities = $this->getUsersCommunities($loggedUserId);
                $query = $this->getCommunitiesWithTags($query, $usersTags, $usersCommunities);
                break;
        }
        $this->filterByContext($query);
        
        return $query;
    }

    /**
     * @param ActiveQuery $query
     */
    public function getVisibleNetworksQuery($query, $params = [], $onlyActiveStatus = false, $userId = null)
    {

    }

    /**
     * @param ActiveQuery $query
     */
    public function filterValidated($query)
    {

        if(!empty($this->getCwhValidationStatuses())){
            $query->andWhere([static::tableName().'.status' => $this->getCwhValidationStatuses()]);
        }
    }

    /**
     * @param ActiveQuery $query
     */
    public function filterByContext($query)
    {

    }

    /**
     * @param ActiveQuery $query
     */
    public function getSubNetworkToValidateQuery($query, $userId = null)
    {
        if(is_null($userId)){
            $userId = Yii::$app->user->id;
        }
        //check permission to validate a subnetwork
        $subnetworksPermissions = CwhAuthAssignment::find()->andWhere([
            'cwh_config_id' => self::getCwhConfigId(),
            'item_name' => "CWH_PERMISSION_VALIDATE_" . $this->modelClassName,
            'cwh_auth_assignment.user_id' => $userId
        ])->select('cwh_network_id')->column();
        //user with role network validator can validate root communities too (communities with no parent)
        if (Yii::$app->user->can($this->getValidatorRole())) {
            $query->andWhere([
                'or',
                [static::tableName() .'.parent_id' => null],
                [static::tableName() .'.parent_id' => $subnetworksPermissions]
            ]);
        } else { //user does not have persiion to validate root communities, search only for subcommunities user can validate
            $query->andWhere([static::tableName() .'.parent_id' => $subnetworksPermissions]);
        }
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function searchParticipants($params)
    {
        /** @var yii\db\ActiveQuery $query */
        $query = $this->getNetworkUserMms();
        $query->orderBy('user_profile.cognome ASC');
        $participantsDataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $participantsDataProvider;
    }

    /**
     * @return ActiveQuery
     */
    public function getNetworkUserMms()
    {
        return $this->hasMany($this->getMmClassName(), [$this->getMmNetworkIdFieldName() => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNetworkUsers()
    {
        return $this->hasMany(User::className(), ['id' => $this->getMmUserIdFieldName()])->via('networkUserMms');
    }

    /**
     * Get Id of configuration record for network model Community
     * @return int $cwhConfigId
     */
    public static function getCwhConfigId()
    {
        $cwhConfigId = null;
        $cwhConfig = CwhConfig::findOne(['tablename' => static::tableName()]);
        if (!is_null($cwhConfig)) {
            $cwhConfigId = $cwhConfig->id;
        }
        return $cwhConfigId;
    }

    public static function getVisibility(){
        return "1";
    }

    /**
     * Query for communities in user network.
     * @param int|null $userId - if null the logged userId is considered.
     * @param array $params
     * @param bool|false $onlyActiveStatus - if search only active users in the network (if status attribute is defined in mm table)
     * @return ActiveQuery
     */
    public function getUserNetworkQuery($userId = null, $params = [], $onlyActiveStatus = false)
    {
        if(is_null($userId)){
            $userId = Yii::$app->user->id;
        }
        return $this->buildQuery($params, 'own-interest', $onlyActiveStatus, $userId);
    }

    /**
     * Get networks user can join (visible, not already joined)
     *
     * @param int|null $userId - if null the logged userId is considered.
     * @param array $params
     * @param bool|false $onlyActiveStatus - if search only active users in the network (if status attribute is defined in mm table)
     * @return ActiveQuery
     */
    public function getUserNetworkAssociationQuery($userId = null, $params = [], $onlyActiveStatus = false)
    {

        if(is_null($userId)){
            $userId = Yii::$app->user->id;
        }
        /** @var ActiveQuery $query */
        $query = $this->buildQuery($params, 'all', $onlyActiveStatus, $userId);

        /** @var ActiveQuery $queryJoined */
        $queryJoined = $this->getUserNetworkQuery($userId, $params, false)->select(static::tableName().'.id')->column();
        if(!empty($queryJoined)){
            $query->andWhere(['not in', static::tableName().'.id' , $queryJoined]);
        }
        $query->andWhere(static::tableName().'.deleted_at is null');

        return $query;
    }

    /**
     * @param $id
     * @return ActiveQuery
     */
    public function getAssociationTargetQuery($id = null)
    {
        if(!is_null($id)){
            $this->id = $id;
        }
        $userNetworkIds = $this->getNetworkUserMms()->select($this->getMmUserIdFieldName())->column();
        /** @var ActiveQuery $userQuery */
        $userQuery = User::find()
            ->andFilterWhere(['not in', User::tableName() . '.id', $userNetworkIds])
            ->joinWith('userProfile')
            ->andWhere(['is not', UserProfile::tableName().'.id', null])
            ->andWhere([User::tableName().'.status' => User::STATUS_ACTIVE])
            ->andWhere([UserProfile::tableName().'.attivo' => UserProfile::STATUS_ACTIVE])
            ->orderBy(['cognome' => SORT_ASC, 'nome' => SORT_ASC]);

        return $userQuery;
    }

    public function getJoinWidget()
    {
        return '';
    }

    /**
     * @return array
     */
    public function getValidatorUsersId()
    {
        $users = [];

        try {
            $authManager = Yii::$app->getAuthManager();
            $users = $authManager->getUserIdsByRole($this->getValidatorRole(), true);
        } catch (\Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }
        return $users;
    }

    /**
     * Retrieve the user's tags
     * @param int $userProfileId
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function getUsersTag($userProfileId)
    {
        $usersTags = [];
        if (!is_null(Yii::$app->getModule('cwh'))) {
            $usersTags = \open20\amos\cwh\utility\CwhUtil::findInterestTagIdsByUser($userProfileId);
        }
        return $usersTags;
    }

    /**
     * Retrieve the user's communities
     * @param int $userId
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function getUsersCommunities($userId)
    {
        $usersCommunities = [];
        if (!is_null(Yii::$app->getModule('cwh'))) {
            /** @var ActiveQuery $query */
            $query = \open20\amos\community\models\CommunityUserMm::find();
            $usersCommunities = $query->select(['community_id'])->andWhere(['user_id' => $userId])->column();
        }
        return $usersCommunities;
    }

    /**
     * Retrieve the elements matching with tags in input
     * @param ActiveQuery $query
     * @param array $usersTags
     * @param array $usersCommunities
     * @return mixed
     */
    private function getCommunitiesWithTags($query, $usersTags = [], $usersCommunities = [])
    {
        $this->setTagValues($usersTags);
        $query->innerJoin('entitys_tags_mm entities_tag',
            "entities_tag.classname = '" . addslashes($this->modelClassName) . "' AND entities_tag.record_id=" . static::tableName() . ".id");
        $query->andWhere(['or', ["community.community_type_id" => 1], ["community.community_type_id" => 2]]);
        $query->andWhere(['in', 'community.id', $usersCommunities]);
        $query->andWhere(['in', 'entities_tag.tag_id', $usersTags]);
        $query->andWhere(['entities_tag.deleted_at' => null]);
        return $query;
    }
}
