<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\record
 * @category   CategoryName
 */

namespace lispa\amos\core\record;

use lispa\amos\core\behaviors\BlameableBehavior;
use lispa\amos\core\behaviors\EJsonBehavior;
use lispa\amos\core\behaviors\SoftDeleteByBehavior;
use lispa\amos\core\behaviors\VersionableBehaviour;
use lispa\amos\core\interfaces\StatsToolbarInterface;
use lispa\amos\core\interfaces\WorkflowModelInterface;
use lispa\amos\core\module\AmosModule;
use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\utilities\StringUtils;
use lispa\amos\core\utilities\WorkflowTransitionWidgetUtility;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use Yii;
use yii\base\Behavior;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use yii\helpers\Inflector;
use yii\web\Application as Web;

/**
 * Class Record
 *
 * @property \lispa\amos\admin\models\UserProfile $createdUserProfile
 * @property \lispa\amos\admin\models\UserProfile $updatedUserProfile
 * @property \lispa\amos\admin\models\UserProfile $deletedUserProfile
 * @property string $workflowStatusLabel
 *
 * @package lispa\amos\core\record
 */
class Record extends ActiveRecord implements StatsToolbarInterface
{
    const SCENARIO_FAKE_REQUIRED = 'scenario_fake_required';

    public static $modulesChainBehavior = [];

    /**
     * @var array Array of order fields get from the config file of the module
     */
    public $orderAttributes = NULL;

    /**
     * @var string Selected ORDER attribute (field) from the ORDER form
     */
    public $orderAttribute = NULL;

    /**
     * @var integer ORDER ascending (SORT_ASC), descending (SORT_DESC)
     */
    public $orderType = NULL;

    protected $adminInstalled = NULL;

    public $tagsMandatory;

    /**
     * @var \ReflectionClass|null $reflectionClass
     */
    public $reflectionClass = null;

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_FAKE_REQUIRED] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    /**
     * Base query, it exclude deleted elements
     *
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        $return = parent::find();
        if (array_key_exists('deleted_at', static::getTableSchema()->columns)) {
            $tableName = static::getTableSchema()->name;
            $return->andWhere([$tableName . '.deleted_at' => null]);
        }
        return $return;
    }

    /**
     * Base query, it INCLUDE deleted elements
     *
     * @return \yii\db\ActiveQuery
     */
    public static function basicFind()
    {
        return parent::find();
    }

    /**
     * Array of fields => labels for the ORDER form
     * see "_order.php" file
     * @return mixed
     */
    public function getOrderAttributesLabels()
    {
        $labels = [];
        if ($this->orderAttributes) {
            foreach ($this->orderAttributes as $value) {
                $labels[$value] = $this->getAttributeLabel($value);
            }
        }
        return $labels;
    }

    /**
     *Init the order variables from the module config
     */
    public function initOrderVars()
    {
        //if the search is enabled
        if (
            isset(\Yii::$app->controller->module)
            &&
            isset(\Yii::$app->controller->module->params['orderParams'][\Yii::$app->controller->id])
        ) {
            //clean var
            $moduleParams = \Yii::$app->controller->module->params['orderParams'][\Yii::$app->controller->id];

            //check if is set an array of order params
            if (
                isset($moduleParams['fields'])
                &&
                $moduleParams['fields']
            ) {
                $this->setOrderAttributes($moduleParams['fields']);
            }

            //check if is set a default value
            if (
                isset($moduleParams['default_field'])
                &&
                $moduleParams['default_field']
            ) {
                $this->setOrderAttribute($moduleParams['default_field']);
            }

            //check if is set a default order value
            if (
                isset($moduleParams['order_type'])
                &&
                $moduleParams['order_type']
            ) {
                $this->setOrderType($moduleParams['order_type']);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $cwhModule = \Yii::$app->getModule('cwh');
        if ($this->isEnabledCwh($cwhModule)) {
            if ($this->isEnabledTag()) {
                if (!$cwhModule->tagsMatchEachTree) {
                    $ruleTags = [
                        [
                            'tagsMandatory',
                            'required',
                            'when' => function ($model) {
                                return (!is_null($model->regola_pubblicazione) && in_array($model->regola_pubblicazione,
                                        [2, 4]) && empty($model->tagValues) && empty($_POST[$this->formName()]['tagValues']));
                            },
                            'whenClient' => "function (attribute, value) {
                                var regolaPubblicazione = $('#cwh-regola_pubblicazione');
                               return ( regolaPubblicazione.length && (regolaPubblicazione.val() == '2' || regolaPubblicazione.val() == '4' ) && 
                                $('.kv-selected').length === 0 );
                }",
                            'message' => BaseAmosModule::t('amostag', 'Selezionare almeno 1 tag.')
                        ]
                    ];
                } else {
                    $tagRoots = \lispa\amos\tag\models\TagModelsAuthItemsMm::find()->andWhere(['classname' => $this->className()])->addSelect('tag_id')->groupBy('tag_id')->column();
                    if ($tagRoots) {
                        $ruleTags = [
                            [
                                'tagsMandatory',
                                'required',
                                'when' => function ($model) use ($tagRoots) {
                                    if (!is_null($model->regola_pubblicazione)
                                        && in_array($model->regola_pubblicazione, [2, 4])
                                        && empty($model->tagValues)
                                    ) {
                                        $formTags = $_POST[$this->formName()]['tagValues'];
                                        if (empty($formTags)) {
                                            return true;
                                        }
                                        foreach ($tagRoots as $tagRoot) {
                                            if (empty($formTags[$tagRoot])) {
                                                return true;
                                            }
                                        }
                                    }
                                    return false;
                                },
                                'whenClient' => "function (attribute, value) {
                                    var regolaPubblicazione = $('#cwh-regola_pubblicazione');
                                    var tagValueTrees = $('input[name^=\"" . $this->formName() . "[tagValues][\"]');
                                    var selectedEachTree = true;
                                    $.each( tagValueTrees, function( i, tagsSelected ) {
                                        if(tagsSelected.value == '' || tagsSelected.value.length == 0 ){
                                          selectedEachTree = false;
                                        }
                                    });
                                    return ( regolaPubblicazione.length && (regolaPubblicazione.val() == '2' || regolaPubblicazione.val() == '4' ) && !selectedEachTree);
                                }",
                                'message' => BaseAmosModule::t('amostag', 'Selezionare almeno 1 tag per ogni albero.')
                            ]
                        ];
                    } else {
                        $ruleTags = [];
                    }
                }
                $rules = ArrayHelper::merge($ruleTags, $rules);
            }
        }
        return $rules;
    }

    /**
     * Set the list of fields order for this module
     *
     * @param array $fields
     * @return bool
     */
    public function setOrderAttributes($fields = ['id'])
    {
        $this->orderAttributes = $fields;
        return true;
    }

    /**
     * Set order field
     *
     * @param string $field
     * @return bool
     */
    public function setOrderAttribute($field = 'id')
    {
        if ($this->orderAttributes && in_array($field, $this->orderAttributes)) {
            $this->orderAttribute = $field;
        } else {
            $this->orderAttribute = 'id';
        }
        return true;
    }

    /**
     * Set order type: ascending (SORT_ASC), descending (SORT_DESC)
     * @param int $type
     * @return bool
     */
    public function setOrderType($type = SORT_ASC)
    {
        $this->orderType = (int)$type;
        return true;
    }

    /**
     * Identifies the sort fields
     *
     * @param $params
     */
    public function setOrderVars($params)
    {
        $classSearch = Inflector::id2camel(\Yii::$app->controller->id, '-') . 'Search';

        if (
            array_key_exists($classSearch, $params)
            &&
            array_key_exists("orderAttribute", $params[$classSearch])
            &&
            array_key_exists("orderType", $params[$classSearch])
        ) {
            $this->setOrderAttribute($params[$classSearch]["orderAttribute"]);
            $this->setOrderType($params[$classSearch]["orderType"]);
        }
    }

    /**
     * Check if there is an order variable for the module
     *
     * @return bool
     */
    public function canUseModuleOrder()
    {
        return ($this->orderAttribute && $this->orderType);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->adminInstalled = \Yii::$app->getModule('admin');
        $this->reflectionClass = new \ReflectionClass(static::className());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(),
            [
                'orderAttribute' => \Yii::t('amoscore', 'Campo di ordinamento'),
                'orderType' => \Yii::t('amoscore', 'Criterio di ordinamento'),
                'createdUserProfile' => \Yii::t('amoscore', 'Creato da'),
                'updatedUserProfile' => \Yii::t('amoscore', 'Ultimo aggiornamento di'),
                'deletedUserProfile' => \Yii::t('amoscore', 'Cancellato da')
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviorsParent = parent::behaviors();

        $behaviors = [
            "EJsonBehavior" => [
                'class' => EJsonBehavior::className()
            ],
            "SoftDeleteByBehavior" => [
                'class' => SoftDeleteByBehavior::className()
            ],
            "TimestampBehavior" => [
                'class' => TimestampBehavior::className(),
                'value' => function () {
                    return date('Y-m-d H:i:s');
                },
            ],
            "BlameableBehavior" => [
                'class' => BlameableBehavior::className(),
            ],
            "VersionableBehaviour" => [
                'class' => VersionableBehaviour::className(),
                'versionTable' => "{$this->tableName()}_version"
            ],
        ];

        // ciclo che innesta i behaviors nei model destinati
        foreach (self::$modulesChainBehavior as $item) {
            $module = \Yii::$app->getModule($item);
            if ($this->isEnabledModule($module) && $module->behaviors) {
                $behaviors = ArrayHelper::merge($module->behaviors, $behaviors);
            }
        }

        return ArrayHelper::merge($behaviorsParent, $behaviors);
    }

    public function __toString()
    {
        $representingColumn = $this->representingColumn();
        if (($representingColumn === null) || ($representingColumn === array()))
            if ($this->getTableSchema()->primaryKey !== null) {
                $representingColumn = $this->getTableSchema()->primaryKey;
            } else {
                $columnNames = $this->getTableSchema()->getColumnNames();
                $representingColumn = $columnNames[0];
            }

        if (is_array($representingColumn)) {
            $part = '';
            foreach ($representingColumn as $representingColumn_item) {
                $part .= ($this->$representingColumn_item === null ? '' : $this->__shortText($this->$representingColumn_item, 30)) . ' ';
            }
            return substr($part, 0, -1);
        } elseif (is_string($representingColumn)) {
            return $representingColumn;
        } else {
            return $this->$representingColumn === null ? '' : (string)$this->$representingColumn;
        }
    }

    public function toStringWithCharLimit($char_limit = 30)
    {
        $representingColumn = $this->representingColumn();
        if (($representingColumn === null) || ($representingColumn === array()))
            if ($this->getTableSchema()->primaryKey !== null) {
                $representingColumn = $this->getTableSchema()->primaryKey;
            } else {
                $columnNames = $this->getTableSchema()->getColumnNames();
                $representingColumn = $columnNames[0];
            }

        if (is_array($representingColumn)) {
            $part = '';
            foreach ($representingColumn as $representingColumn_item) {
                $part .= ($this->$representingColumn_item === null ? '' : $this->__shortText($this->$representingColumn_item, $char_limit)) . ' ';
            }
            return substr($part, 0, -1);
        } elseif (is_string($representingColumn)) {
            return $representingColumn;
        } else {
            return $this->$representingColumn === null ? '' : (string)$this->$representingColumn;
        }
    }

    public function representingColumn()
    {
        return null;
    }

    /**
     * Parse string and return limited one
     * @param $text
     * @param $char_limit
     * @return string
     */
    protected function __shortText($text, $char_limit)
    {
        //Remove html tags
        $asString = strip_tags($text);

        //If already good string
        if (strlen($asString) < $char_limit) {
            return $asString;
        }

        if ($char_limit != -1) {

            //Limit string
            $asString = substr($asString, 0, $char_limit + 1);

            //Explode to array
            $arrayString = explode(' ', $asString);

            if (count($arrayString) > 1) {
                //Remove last word
                array_pop($arrayString);

                //Merge string
                $asString = implode(' ', $arrayString);
            }

            //Return it
            return $asString . "...";

        } else {
            return $asString;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedUserProfile()
    {
        if ($this->adminInstalled) {
            $modelClass = \lispa\amos\admin\AmosAdmin::instance()->createModel('UserProfile');
            return $this->hasOne($modelClass::className(), ['user_id' => 'created_by']);
        } else {
            return null;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedUserProfile()
    {
        if ($this->adminInstalled) {
            $modelClass = \lispa\amos\admin\AmosAdmin::instance()->createModel('UserProfile');
            return $this->hasOne($modelClass::className(), ['user_id' => 'updated_by']);
        } else {
            return null;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeletedUserProfile()
    {
        if ($this->adminInstalled) {
            $modelClass = \lispa\amos\admin\AmosAdmin::instance()->createModel('UserProfile');
            return $this->hasOne($modelClass::className(), ['user_id' => 'deleted_by']);
        } else {
            return null;
        }
    }

    /**
     * Override for demos
     *
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $isDemo = $this->isDemo();

        if ($isDemo && (Yii::$app instanceof Web) && $this->inBlackList()) {
            $key = 'success';
            $message = BaseAmosModule::t('amoscore', 'In Demo non &eacute; possibile modificare i contenuti');
            $flashes = Yii::$app->session->getFlash($key);
            if (!Yii::$app->session->hasFlash($key) || !in_array($message, $flashes)) {
                Yii::$app->getSession()->addFlash($key, $message);
            }
            return false;
        }

        return parent::beforeSave($insert);
    }

    /**
     * Override Required for cache
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $tableName = self::getTableSchema()->name;

        $blackList = [
            'amos_user_dashboards',
            'user_lockout',
            'user_profile',
            'translation_user_preference',
            'amos_workflow_transitions_log'
        ];

        //Caching table name
        $vanishTableName = 'vanish_cache';

        if (!in_array($tableName, $blackList) && \Yii::$app->db->schema->getTableSchema($vanishTableName, true) != null) {
            \Yii::$app->db->createCommand()->setSql(
                "INSERT INTO vanish_cache (`table_name`, `updates`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) 
                  VALUES ('{$tableName}', 1, now(), NULL, now(), NULL, now(), NULL)
                  ON DUPLICATE KEY UPDATE updates = updates + 1, updated_at = now()"
            )->execute();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Check is demo environment
     * @return bool
     */
    public function isDemo()
    {
        $demoVar = isset(\Yii::$app->params['isDemo']) ? \Yii::$app->params['isDemo'] : false;
        return $demoVar ?: false;
    }

    /**
     * @return bool
     */
    private function inBlackList()
    {
        $ret = false;
        $demoModelBlackList = isset(\Yii::$app->params['demoModelBlackList']) ? \Yii::$app->params['demoModelBlackList'] : [];
        foreach ($demoModelBlackList as $cls) {
            if ($this instanceof $cls) {
                $ret = true;
                break;
            }
        }
        return $ret;
    }

    /**
     * Override for demos
     * @return bool
     */
    public function beforeDelete()
    {
        $isDemo = $this->isDemo();

        if ($isDemo && (Yii::$app instanceof Web)) {
            $key = 'success';
            $message = BaseAmosModule::t('amoscore', 'In Demo non &eacute; possibile modificare i contenuti');
            $flashes = Yii::$app->session->getFlash($key);
            if (!Yii::$app->session->hasFlash($key) || !in_array($message, $flashes)) {
                Yii::$app->getSession()->addFlash($key, $message);
            }
            return false;
        }

        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if (isset(Yii::$app->params['forms-purify-data']) && (Yii::$app->params['forms-purify-data'] == true)) {
            if (isset(Yii::$app->params['forms-purify-data-white-models'])) {
                $listClassModels = Yii::$app->params['forms-purify-data-white-models'];
                if (in_array($this->className(), $listClassModels)) {
                    return parent::beforeValidate();
                }
            }

            $listAttributes = $this->attributes;
            foreach ($listAttributes as $key => $attribute) {
                $this->$key = HtmlPurifier::process($this->$key);
            }

        }
        return parent::beforeValidate();
    }

    /**
     * method return user ids of record validators
     * @return array
     */
    public function getValidatorUsersId()
    {
        $users = [];

        try {
            $moduleCwh = Yii::$app->getModule('cwh');
            if ($this->isEnabledCwh($moduleCwh)) {
                $users = \lispa\amos\cwh\models\CwhAuthAssignment::find()->andWhere([
                    'item_name' => $moduleCwh->permissionPrefix . '_VALIDATE_' . $this->className(),
                ])->andWhere(['in', 'cwh_nodi_id', $this->validatori])
                    ->select('user_id')->groupBy('user_id')->asArray()->column();
            }
            if (empty($users) && $this instanceof WorkflowModelInterface) {
                $validatorRole = $this->getValidatorRole();
                $authManager = \Yii::$app->authManager;
                $users = $authManager->getUserIdsByRole($validatorRole);
            }

        } catch (\Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }
        return $users;
    }

    /**
     * @inheritdoc
     */
    public function getStatsToolbar($disableLink = false)
    {
        $panels = [];

        $behaviors = $this->getBehaviors();
        /**@var $behavior Behavior */
        foreach ($behaviors as $behavior) {
            if ($behavior->hasMethod(__FUNCTION__)) {
                $panelsAttributes = $behavior->{__FUNCTION__}($disableLink);
                $panels = ArrayHelper::merge($panels, $panelsAttributes);
            }
        }

        return $panels;
    }

    /**
     * This method find a behavior from the model.
     * @param string $className
     * @return Behavior|null
     */
    public function findBehaviorByClassName($className)
    {
        $behaviors = $this->getBehaviors();
        $behaviorToReturn = null;
        foreach ($behaviors as $index => $behavior) {
            /** @var Behavior $behavior */
            if ($behavior->className() == $className) {
                $behaviorToReturn = $behavior;
            }
        }
        return $behaviorToReturn;
    }

    /**
     * This method detach a behavior from the model.
     * @param string $className
     */
    public function detachBehaviorByClassName($className)
    {
        $behaviors = $this->getBehaviors();
        foreach ($behaviors as $index => $behavior) {
            /** @var Behavior $behavior */
            if ($behavior->className() == $className) {
                $this->detachBehavior($index);
            }
        }
    }

    /**
     * @param array $whiteList
     */
    public function detachBehaviorsOnWhiteList(array $whiteList)
    {
        $behaviors = $this->getBehaviors();
        foreach ($behaviors as $index => $behavior) {
            /** @var Behavior $behavior */
            if (!in_array($index, $whiteList)) {
                $this->detachBehavior($index);
            }
        }
    }

    /**
     * This method return an array of array. The array keys are all the model fields
     * and the values are arrays with "name! and "id" keys modified with the string
     * contained in the param. The return array structure is the following:
     * $newNameAndIds = [
     *  'FIELD_NAME_1' => [
     *      'name' => 'NEW_NAME',
     *      'id' => 'NEW_ID'
     *  ],
     *  .
     *  .
     *  .
     * ];
     * @param string $formNameSuffix
     * @return array
     */
    public function renameFormNamesAndIds($formNameSuffix)
    {
        $newFormFieldNamesAndIds = [];
        foreach ($this->attributes() as $attribute) {
            $newFormFieldNamesAndIds[$attribute] = [
                'name' => $this->formName() . $formNameSuffix . '[' . $attribute . ']',
                'id' => strtolower($this->formName()) . '-' . strtolower($formNameSuffix) . '-' . $attribute,
            ];
        }
        return $newFormFieldNamesAndIds;
    }

    /**
     * This method return the correct workflow status label. It checks if the workflow behavior is present,
     * then checks if the model has a workflow status and return the correct label.
     * @return string
     */
    public function getWorkflowStatusLabel()
    {
        $label = '';
        $hasWorkflow = false;
        if ($this->getBehavior('workflow') || $this->findBehaviorByClassName(SimpleWorkflowBehavior::className())) {
            $hasWorkflow = true;
        }
        if ($hasWorkflow && $this->hasWorkflowStatus()) {
            $status = $this->getWorkflowStatus();
            if ($status) {
                $label = WorkflowTransitionWidgetUtility::getLabelStatusFromMetadata($this, $status);
            }
        }
        return $label;
    }

    /**
     * This method is called by search module to fetch results matching one or more tags
     * @param int|array $tagIds
     * @param int $pageSize - data provider page size for search results (default = 5)
     * @return ActiveDataProvider $dataProvider
     */
    public function globalSearchTags($tagIds = null, $pageSize = 5)
    {
        /** @var ActiveDataProvider $dataProvider */
        if ($this->hasMethod('buildQuery')) {
            $dataProvider = $this->search([], 'all', null);
        } else {
            $dataProvider = $this->search([]);
        }
        $pagination = $dataProvider->getPagination();
        if (!$pagination) {
            $pagination = new Pagination();
            $dataProvider->setPagination($pagination);
        }
        $pagination->setPageSize($pageSize);

        $tableName = $this->tableName();
        /** @var ActiveQuery $dataProvider ->query */
        $className = $dataProvider->query->modelClass;

        // Verifico se il modulo supporta i TAG e, in caso, ricerco anche fra quelli
        $moduleTag = Yii::$app->getModule('tag');
        $enableTagSearch = (isset($moduleTag) && in_array($className, $moduleTag->modelsEnabled));

        $searchModels = [];
        if ($enableTagSearch) {
            $dataProvider->query->leftJoin('entitys_tags_mm e_tag',
                "e_tag.record_id= " . $tableName . ".id AND e_tag.deleted_at IS NULL AND e_tag.classname='" . addslashes($className) . "'");

            $dataProvider->query->leftJoin('tag t', "e_tag.tag_id=t.id");
            if ($tagIds) {
                $dataProvider->query->andWhere(['t.id' => $tagIds]);
            }
            foreach ($dataProvider->models as $m) {
                array_push($searchModels, $this->convertToSearchResult($m));
            }
        }
        $dataProvider->setModels($searchModels);

        return $dataProvider;
    }

    /**
     * @param AmosModule|string|null $module
     * @return bool
     */
    public function isEnabledModule($module = null)
    {
        if (!is_null($module)) {
            if (is_string($module)) {
                $module = Yii::$app->getModule($module);
            }
            if ($module->hasProperty('modelsEnabled')) {
                return (!is_null($module) && in_array($this->className(), $module->modelsEnabled));
            }
        }
        return false;
    }

    /**
     * @param AmosModule|null $moduleCwh
     * @return bool
     */
    public function isEnabledCwh($moduleCwh = null)
    {
        if (is_null($moduleCwh)) {
            $moduleCwh = Yii::$app->getModule('cwh');
        }
        return $this->isEnabledModule($moduleCwh);
    }

    /**
     * @param AmosModule|null $moduleTag
     * @return bool
     */
    public function isEnabledTag($moduleTag = null)
    {
        if (is_null($moduleTag)) {
            $moduleTag = Yii::$app->getModule('tag');
        }
        return $this->isEnabledModule($moduleTag);
    }

    /**
     * This method returns the module name in which the model is contained based on the standard
     * framework model classname. If the module name doesn't correspond you must override this
     * method and return the real module name.
     * (i.e. For model classname '...\moduleName\models\DiscussioniTopic' return 'moduleName')
     * @return string
     */
    public function getModelModuleName()
    {
        $namespaceName = $this->reflectionClass->getNamespaceName();
        $splitNamespace = explode('\\', $namespaceName);
        do {
            $moduleName = array_pop($splitNamespace);
        } while (($moduleName == 'models') || ($moduleName == 'base'));
        return $moduleName;
    }

    /**
     * This method returns the controller name route based on the model name. If the route
     * doesn't correspond you must override this method and return the controller route.
     * (i.e. For model 'DiscussioniTopic' return 'discussioni-topic')
     * @return string
     */
    public function getModelControllerName()
    {
        $modelShortName = $this->reflectionClass->getShortName();
        $pieces = StringUtils::splitAtUpperCase($modelShortName);
        $modelControllerName = '';
        $isFirst = true;
        foreach ($pieces as $piece) {
            if (!$isFirst) {
                $modelControllerName .= '-';
            }
            $modelControllerName .= strtolower($piece);
            $isFirst = false;
        }
        return $modelControllerName;
    }
}
