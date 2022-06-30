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

use open20\amos\core\i18n\grammar\ContentModelGrammar;
use open20\amos\core\interfaces\ContentModelInterface;
use open20\amos\core\interfaces\ContentModelSearchInterface;
use open20\amos\core\interfaces\CrudModelInterface;
use open20\amos\core\interfaces\FacilitatorInterface;
use open20\amos\core\interfaces\ModelImageInterface;
use open20\amos\core\interfaces\SearchModelInterface;
use open20\amos\notificationmanager\models\NotificationChannels;
use open20\amos\notificationmanager\record\NotifyRecord;
use Yii;
use yii\base\Event;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\ExpressionInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class ContentModel
 * @package open20\amos\core\record
 */
abstract class ContentModel extends NotifyRecord implements ContentModelInterface, CrudModelInterface, SearchModelInterface, ContentModelSearchInterface, ModelImageInterface, FacilitatorInterface {

    public $modelImage;
    public $attachments;

    /**
     * @var string $regola_pubblicazione Regola di pubblicazione
     */
    public $regola_pubblicazione;

    /**
     * @var string $destinatari Destinatari
     */
    public $destinatari;

    /**
     * @var string $validatori Validatori
     */
    public $validatori;

    /**
     * @var string $destinatari_pubblicazione Destinatari pubblicazione
     */
    public $destinatari_pubblicazione;

    /*
     * @var boolean bypassscope to search for content without added scope
     */
    public $bypassScope = false;

    /**
     * @inheritdoc
     */
    public function getModelImage() {
        $this->modelImage = $this->hasOneFile('image')->one();
        if (empty($this->modelImage)) {
            $this->modelImage = $this->hasOneFile(lcfirst($this->modelFormName) . 'Image')->one();
        }
        return $this->modelImage;
    }

    /**
     * @inheritdoc
     */
    public function getModelImageUrl(
        $size = 'original',
        $protected = true,
        $url = '/img/img_default.jpg',
        $absolute = false,
        $canCache = false
    ) {
        $image = $this->getModelImage();
        if (!is_null($image)) {
            if ($protected) {
                $url = $image->getUrl($size, $absolute, $canCache);
            } else {
                $url = $image->getWebUrl($size, $absolute, $canCache);
            }
        }
        return $url;
    }

    /**
     * Getter for $this->attachments;
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getAttachments() {
        if (empty($this->attachments)) {
            $query = $this->hasMultipleFiles(lcfirst($this->modelFormName) . 'Attachments');
            $query->multiple = false;
            $this->attachments = $query->one();
        }
        return $this->attachments;
    }

    /**
     * @param $attachments
     */
    public function setAttachments($attachments) {
        $this->attachments = $attachments;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return ArrayHelper::merge(parent::rules(), [
                [['destinatari_pubblicazione'], 'safe'],
                [['attachments'], 'file', 'maxFiles' => 0],
                [['modelImage'], 'file', 'extensions' => 'jpeg, jpg, png, gif'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
        if ($this->isSearch) {
            // bypass scenarios() implementation in the parent class
            return Model::scenarios();
        } else {
            return parent::scenarios();
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        $parentBehaviors = parent::behaviors();
        $behaviors = [];
        if (\Yii::$app->getModule('attachments')) {
            $behaviors['fileBehavior'] = [
                'class' => \open20\amos\attachments\behaviors\FileBehavior::className()
            ];
        }
        if ($this->isSearch) {
            //if the parent model News is a model enabled for tags, NewsSearch will have TaggableBehavior too
            $moduleTag = \Yii::$app->getModule('tag');
            if (!is_null($moduleTag) && in_array($this->modelClassName, $moduleTag->modelsEnabled)) {
                $behaviors = ArrayHelper::merge($moduleTag->behaviors, $behaviors);
            }
        }
        return ArrayHelper::merge($parentBehaviors, $behaviors);
    }

    /**
     * @inheritdoc
     */
    public function getShortDescription() {
        return $this->getDescription(true);
    }

    /**
     * @inheritdoc
     */
    public function getPublicatedFrom() {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getPublicatedAt() {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getCategory() {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getPluginWidgetClassname() {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getGrammar() {
        return new ContentModelGrammar();
    }

    /**
     * @inheritdoc
     */
    public function getToValidateStatus() {
        return $this->modelFormName . 'Workflow/PUBLISHREQUEST';
    }

    /**
     * @inheritdoc
     */
    public function getValidatedStatus() {
        return $this->modelFormName . 'Workflow/PUBLISHED';
    }

    /**
     * @inheritdoc
     */
    public function getDraftStatus() {
        return $this->modelFormName . 'Workflow/DRAFT';
    }

    /**
     * @inheritdoc
     */
    public function getValidatorRole() {
        return strtoupper($this->modelFormName . '_VALIDATOR');
    }

    /**
     * @inheritdoc
     */
    public function getFacilitatorRole() {
        return "FACILITATOR";
    }

    /**
     * @inheritdoc
     */
    public function getExternalFacilitatorRole()
    {
        return "FACILITATOR_EXTERNAL";
    }

    /**
     * @inheritdoc
     */
    public function getCwhValidationStatuses()
    {
        $validationStatuses = [];
        $validatedStatus = $this->getValidatedStatus();
        if (!is_null($validatedStatus)) {
            $validationStatuses[] = $validatedStatus;
        }
        return $validationStatuses;
    }

    /**
     * Returns the full url to the action with the model id.
     * @param $url
     * @return null|string
     */
    private function getFullUrl($url) {
        if (!empty($url)) {
            return Url::toRoute(["/" . $url, "id" => $this->id]);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getCreateUrl() {
        return $this->getModelModuleName() . '/' . $this->getModelControllerName() . '/create';
    }

    /**
     * @inheritdoc
     */
    public function getFullCreateUrl() {
        return $this->getCreateUrl();
    }

    /**
     * @inheritdoc
     */
    public function getViewUrl() {
        return $this->getModelModuleName() . '/' . $this->getModelControllerName() . '/view';
    }

    public function getBypassScope() {
        return $this->bypassScope;
    }

    public function setBypassScope($bypassScope) {
        $this->bypassScope = $bypassScope;
    }

    /**
     * @inheritdoc
     */
    public function getFullViewUrl() {
        return $this->getFullUrl($this->getViewUrl());
    }

    /**
     * @inheritdoc
     */
    public function getUpdateUrl() {
        return $this->getModelModuleName() . '/' . $this->getModelControllerName() . '/update';
    }

    /**
     * @inheritdoc
     */
    public function getFullUpdateUrl() {
        return $this->getFullUrl($this->getUpdateUrl());
    }

    /**
     * @inheritdoc
     */
    public function getDeleteUrl() {
        return $this->getModelModuleName() . '/' . $this->getModelControllerName() . '/delete';
    }

    /**
     * @inheritdoc
     */
    public function getFullDeleteUrl() {
        return $this->getFullUrl($this->getDeleteUrl());
    }

    /**
     * @inheritdoc
     */
    public function searchDefaultOrder($dataProvider) {
        // Check if can use the custom module order
        if ($this->canUseModuleOrder()) {
            $dataProvider->setSort($this->createOrderClause());
        } else { // For widget graphic last news, order is incorrect without this else
            $dataProvider->setSort([
                'defaultOrder' => [
                    'created_at' => SORT_DESC
                ]
            ]);
        }
        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function searchOwnInterestsQuery($params) {
        return $this->buildQuery($params, 'own-interest');
        
    }

    /**
     * @inheritdoc
     */
    public function searchAllQuery($params) {
        return $this->buildQuery($params, 'all');
    }

    /**
     * @inheritdoc
     */
    public function searchCreatedByMeQuery($params) {
        return $this->buildQuery($params, 'created-by');
    }

    /**
     * @inheritdoc
     */
    public function searchToValidateQuery($params) {
        return $this->buildQuery($params, 'to-validate');
    }

    /**
     * Array of fields to search with equal match in search method
     *
     * @return array
     */
    public function searchFieldsMatch() {
        return [];
    }

    /**
     * Array of fields to search with like condition in search method
     *
     * @return array
     */
    public function searchFieldsLike() {
        return [];
    }

    /**
     * Array of fields to search with <= condition in search method
     *
     * @return array
     */
    public function searchFieldsLessEqual() {
        return [];
    }

    /**
     * Array of fields to search with >= condition in search method
     *
     * @return array
     */
    public function searchFieldsGreaterEqual() {
        return [];
    }

    /**
     * Apply search filtering conditions using above methods
     *
     * @param $query
     */
    public function applySearchFilters($query) {
        $searchFieldMatch = $this->searchFieldsMatch();

        if (!empty($searchFieldMatch)) {
            foreach ($searchFieldMatch as $searchField) {
                $query->andFilterWhere([static::tableName() . '.' . $searchField => $this->{$searchField}]);
            }
        }

        $searchFieldLike = $this->searchFieldsLike();
        if (!empty($searchFieldLike)) {
            foreach ($searchFieldLike as $searchField) {
                $query->andFilterWhere(['like', static::tableName() . '.' . $searchField, $this->{$searchField}]);
            }
        }

        $searchFieldLessEqual = $this->searchFieldsLessEqual();
        if (!empty($searchFieldLessEqual)) {
            foreach ($searchFieldLessEqual as $searchField) {
                $query->andFilterWhere(['<=', static::tableName() . '.' . $searchField, $this->{$searchField}]);
            }
        }

        $searchFieldGreaterEqual = $this->searchFieldsGreaterEqual();
        if (!empty($searchFieldGreaterEqual)) {
            foreach ($searchFieldGreaterEqual as $searchField) {
                $query->andFilterWhere(['>=', static::tableName() . '.' . $searchField, $this->{$searchField}]);
            }
        }
    }

    /**
     * Array of fields to search with like condition in global search
     *
     * @return array
     */
    public function searchFieldsGlobalSearch() {
        return [];
    }

    /**
     * Use to add Join condition/add other filtering condition
     *
     * @param ActiveQuery $query
     */
    public function getSearchQuery($query) {
        
    }

    /**
     * Additional filtering for serach query in case the model is not enabled in cwh or cwh in not enabled
     * Override if necessary
     *
     * @param $query
     */
    public function getSearchQueryCwhDisabled($query) {
        if (!empty($this->getPublicatedFrom())) {
            $now = date('Y-m-d');
            $query->andFilterWhere(['<=', $this->getPublicatedFrom(), $now]);
            if (!empty($this->getPublicatedAt())) {
                $query->andFilterWhere(['>=', $this->getPublicatedAt(), $now]);
            }
        }
    }

    /**
     * Content base search: all content matching search parameters and not deleted.
     *
     * @param   array $params Search parameters
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function baseSearch($params) {
        //init the default search values
        $this->initOrderVars();

        //check params to get orders value
        $this->setOrderVars($params);
        /** @var Record $className */
        $className = $this->modelClassName;

        return $className::find()->distinct();
    }

    /**
     * @param array $params
     * @param string $queryType
     * @param int|null|ExpressionInterface $limit
     * @param bool $onlyDrafts
     * @return ActiveDataProvider|\yii\data\BaseDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function search($params, $queryType = null, $limit = null, $onlyDrafts = false) {

        if (!empty($queryType)) {
            $query = $this->buildQuery($params, $queryType, $onlyDrafts);
        } else {
            $query = $this->baseSearch($params);
        }

        $query->limit($limit);

        /** Switch off notifications - method of NotifyRecord */
        //        $this->switchOffNotifications($query);

        $dp_params = ['query' => $query,];
        if ($limit) {
            $dp_params ['pagination'] = false;
        }

        //set the data provider
        $dataProvider = new ActiveDataProvider($dp_params);
        $dataProvider = $this->searchDefaultOrder($dataProvider);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if (isset($params[$this->formName()]['tagValues'])) {

            $tagValues = $params[$this->formName()]['tagValues'];
            $this->setTagValues($tagValues);
            if (is_array($tagValues) && !empty($tagValues)) {
                $orQueries = null;
                $i = 0;
                foreach ($tagValues as $rootId => $tagId) {
                    if (!empty($tagId)) {
                        if ($i == 0) {
                            $query->innerJoin('entitys_tags_mm entities_tag',
                                "entities_tag.classname = '" . addslashes($this->modelClassName) . "' AND entities_tag.record_id=" . static::tableName() . ".id");
                            $orQueries[] = 'or';
                        }
                        $tags = explode(',', $tagId);
                        $tags = array_unique($tags);
                        $orQueries[] = ['and', ["entities_tag.tag_id" => $tags], ['entities_tag.root_id' => $rootId], ['entities_tag.deleted_at' => null]];
                        $i++;
                    }
                }
                if (!empty($orQueries)) {
                    $query->andWhere($orQueries);
                }
            }
        }

        $query->andFilterWhere([
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
        ]);

        $this->applySearchFilters($query);

        $this->getSearchQuery($query);

        return $dataProvider;
    }

    /**
     * Search the Content created by the logged user
     *
     * @param array $params Array di parametri per la ricerca
     * @param int $limit
     * @return ActiveDataProvider|\yii\data\BaseDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function searchOwn($params, $limit = null, $onlyDrafts = false) {
        return $this->search($params, 'created-by', $limit, $onlyDrafts);
    }

    /**
     * Ritorna solamente $this.
     *
     * @return $this
     */
    public function validazioneAbilitata() {
        return $this;
    }

    /**
     * Search content to validate based on cwh rules if cwh is active, all content in 'to validate status' otherwise
     *
     * @param array $params Array di parametri per la ricerca
     * @param int $limit
     * @return ActiveDataProvider|\yii\data\BaseDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function searchToValidate($params, $limit = null) {
        return $this->search($params, 'to-validate', $limit);
    }

    /**
     * Search all validated content
     *
     * @param array $params Array of get parameters for search
     * @param int|null $limit
     * @return ActiveDataProvider|\yii\data\BaseDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function searchAll($params, $limit = null) {
        return $this->search($params, 'all', $limit);
    }

    /**
     * Search all contents (any status) for plugin administrator only
     *
     * @param array $params Array of get parameters for search
     * @param int|null $limit
     * @return ActiveDataProvider|\yii\data\BaseDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function searchAllAdmin($params, $limit = null) {
        return $this->search($params, null, $limit);
    }

    /**
     * Search method useful for retrieve all validated content (based on publication rule and visibility).
     *
     * @param array $params Array of get parameters for search
     * @param int|null $limit
     * @return ActiveDataProvider|\yii\data\BaseDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function searchOwnInterest($params, $limit = null) {
        return $this->search($params, 'own-interest', $limit);
    }

    /**
     * @param array $params
     * @param string $queryType
     * @return ActiveQuery $query
     */
    public function buildQuery($params, $queryType, $onlyDrafts = false) {
        $validByScopeIgnoreStatus = false;
        $validByScopeIgnoreDates = false;
        if (isset($params['validByScopeIgnoreStatus'])) {
            $validByScopeIgnoreStatus = $params['validByScopeIgnoreStatus'];
            unset($params['validByScopeIgnoreStatus']);
        }

        if (isset($params['validByScopeIgnoreDates'])) {
            $validByScopeIgnoreDates = $params['validByScopeIgnoreDates'];
            unset($params['validByScopeIgnoreDates']);
        }

        $query = $this->baseSearch($params);

        $classname = $this->modelClassName;
        $moduleCwh = \Yii::$app->getModule('cwh');
        $cwhActiveQuery = null;

        $isSetCwh = !is_null($moduleCwh) && in_array($classname, $moduleCwh->modelsEnabled);

        if ($isSetCwh) {
            /** @var \open20\amos\cwh\AmosCwh $moduleCwh */
            $moduleCwh->setCwhScopeFromSession();
            $cwhActiveQuery = new \open20\amos\cwh\query\CwhActiveQuery(
                $classname,
                [
                'queryBase' => $query,
                'bypassScope' => $this->getBypassScope()
                ]
            );
            $cwhActiveQuery->validByScopeIgnoreStatus = $validByScopeIgnoreStatus;
            $cwhActiveQuery->validByScopeIgnoreDates = $validByScopeIgnoreDates;
        }

        switch ($queryType) {
            case 'created-by':
                if ($isSetCwh) {
                    $query = $cwhActiveQuery->getQueryCwhOwn();
                } else {
                    $query->andFilterWhere([
                        static::tableName() . '.created_by' => Yii::$app->user->id
                    ]);
                }
                if ($onlyDrafts) {
                    $query->andWhere([
                        static::tableName() . '.status' => $this->getDraftStatus()
                    ]);
                }
                break;
            case 'all':
                if ($isSetCwh) {
                    $query = $cwhActiveQuery->getQueryCwhAll();
                } else {
                    $query->andWhere([
                        static::tableName() . '.status' => $this->getValidatedStatus()
                    ]);
                    $this->getSearchQueryCwhDisabled($query);
                }
                break;
            case'to-validate':
                if ($isSetCwh) {
                    $query = $cwhActiveQuery->getQueryCwhToValidate();
                } else {
                    $query->andWhere([
                        static::tableName() . '.status' => $this->getToValidateStatus()
                    ]);
                    $this->getSearchQueryCwhDisabled($query);
                }
                break;
            case 'own-interest':
                if ($isSetCwh) {
                    $query = $cwhActiveQuery->getQueryCwhOwnInterest();
                } else {
                    $query->andWhere([
                        static::tableName() . '.status' => $this->getValidatedStatus()
                    ]);
                    $this->getSearchQueryCwhDisabled($query);
                }

                break;
            case 'admin-scope':
                if ($isSetCwh) {
                    $cwhActiveQuery->joinPublication();
                    $cwhActiveQuery->filterByScope($query);
                }
                break;
        }

        return $query;
    }

    /**
     * Search all validated contents
     *
     * @param array $searchParamsArray Array of search words
     * @param int|null $pageSize
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function globalSearch($searchParamsArray, $pageSize = 5) {

        $dataProvider = $this->search([], 'all', null);
        $pagination = $dataProvider->getPagination();
        if (!$pagination) {
            $pagination = new Pagination();
            $dataProvider->setPagination($pagination);
        }
        $pagination->setPageSize($pageSize);

        // Verifico se il modulo supporta i TAG e, in caso, ricerco anche fra quelli
        $moduleTag = \Yii::$app->getModule('tag');
        $enableTagSearch = !is_null($moduleTag) && in_array($this->modelClassName, $moduleTag->modelsEnabled);

        if ($enableTagSearch) {
            $dataProvider->query->leftJoin('entitys_tags_mm e_tag', "e_tag.record_id=" . static::tableName() . ".id AND e_tag.deleted_at IS NULL AND e_tag.classname='" . addslashes($this->modelClassName) . "'");

//            if (Yii::$app->db->schema->getTableSchema('tag__translation')) {
//                // Esiste la tabella delle traduzioni dei TAG. Uso quella per la ricerca
//                $dataProvider->query->leftJoin('tag__translation tt', "e_tag.tag_id=tt.tag_id");
//                $tagTranslationSearch = true;
//            }

            $dataProvider->query->leftJoin('tag t', "e_tag.tag_id=t.id");
        }


        //search tag
        $tagsValues = \Yii::$app->request->get('tagValues');
        if ($enableTagSearch) {
            $arrayTagIds = [];
            if (!empty($tagsValues)) {
                $tagIds = ArrayHelper::merge($arrayTagIds, explode(',', $tagsValues));
                $dataProvider->query->andFilterWhere(['t.id' => $tagIds]);
            }
        } else {
            if (!empty($tagsValues)) {
                $dataProvider->query->andWhere(0);
            }
        }

        //search string
        foreach ($searchParamsArray as $searchString) {
            $orQueries = null;

            $searchFieldGlobal = $this->searchFieldsGlobalSearch();
            if (!empty($searchFieldGlobal)) {
                $orQueries[] = 'or';
                foreach ($searchFieldGlobal as $searchField) {
                    $orQueries[] = ['like', static::tableName() . '.' . $searchField, $searchString];
                }
            }

            if (!is_null($orQueries)) {
                $dataProvider->query->andWhere($orQueries);
            }
        }

        $searchModels = [];
        foreach ($dataProvider->models as $m) {
            array_push($searchModels, $this->convertToSearchResult($m));
        }
        $dataProvider->setModels($searchModels);

        return $dataProvider;
    }

    /**
     * @param object $model The model to convert into SearchResult
     * @return SearchResult
     */
    public function convertToSearchResult($model) {
        $searchResult = new SearchResult();
        $searchResult->url = $model->getFullViewUrl();
        $searchResult->box_type = "image";
        $searchResult->id = $model->id;
        $searchResult->titolo = $model->getTitle();
        $publicationDate = $this->getPublicatedAt();
        if (!is_null($publicationDate)) {
            $searchResult->data_pubblicazione = $model->created_at;
        } else {
            $searchResult->data_pubblicazione = $model->created_at;
        }
        $searchResult->immagine = $model->getModelImage();
        $searchResult->abstract = $model->getShortDescription();
        return $searchResult;
    }

    /**
     * Return true if model has been validated at least once (if workflow is active)
     *
     * @return bool
     */
    public function getValidatedOnce() {
        if ($this->isNewRecord) {
            return false;
        }

        if ($this->getBehavior('workflow')) {
            if ($this->getBehavior('workflowLog') || $this->getBehavior('WorkflowLogFunctionsBehavior')) {
                return !is_null($this->getStatusLastUpdateUser($this->getValidatedStatus()));
            }
        }
        return true;
    }

    public function setEventAfterCounter()
    {
        Event::on(
            \open20\amos\core\widget\WidgetIcon::className(),
            \open20\amos\core\widget\WidgetIcon::EVENT_AFTER_COUNT,
            [$this, 'notificationOffAfterCount']
        );
    }

    /**
     * Switch off notification service for not readed discussion notifications
     */
    public function notificationOffAfterCount()
    {
        /** @var \open20\amos\notificationmanager\AmosNotify $notify */
        $notify = $this->getNotifier();

        return true;
    }
}
