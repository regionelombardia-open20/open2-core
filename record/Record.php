<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\record
 * @category   CategoryName
 */

namespace open20\amos\core\record;

use open20\amos\core\behaviors\BlameableBehavior;
use open20\amos\core\behaviors\EJsonBehavior;
use open20\amos\core\behaviors\SoftDeleteByBehavior;
use open20\amos\core\behaviors\VersionableBehaviour;
use open20\amos\core\helpers\Html;
use open20\amos\core\helpers\StringHelper;
use open20\amos\core\interfaces\CrudModelInterface;
use open20\amos\core\interfaces\StatsToolbarInterface;
use open20\amos\core\interfaces\WorkflowModelInterface;
use open20\amos\core\module\AmosModule;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\utilities\StringUtils;
use open20\amos\core\utilities\WorkflowTransitionWidgetUtility;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\WorkflowException;
use raoul2000\workflow\helpers\WorkflowHelper;
use Yii;
use yii\base\Behavior;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\Application as Web;

/**
 * Class Record
 *
 * @property \open20\amos\admin\models\UserProfile $createdUserProfile
 * @property \open20\amos\admin\models\UserProfile $updatedUserProfile
 * @property \open20\amos\admin\models\UserProfile $deletedUserProfile
 * @property string $workflowStatusLabel
 *
 * @package open20\amos\core\record
 */
class Record extends ActiveRecord implements StatsToolbarInterface, CrudModelInterface
{
    const SCENARIO_FAKE_REQUIRED = 'scenario_fake_required';

    public static $modulesChainBehavior = [];
    public $useBullet                   = false;

    const BULLET_TYPE_ALL = 1;
    const BULLET_TYPE_OWN = 2;

    protected static $myTags = null;

    /**
     * @var array Array of order fields get from the config file of the module
     */
    public $orderAttributes = null;

    /**
     * @var string Selected ORDER attribute (field) from the ORDER form
     */
    public $orderAttribute = null;

    /**
     * @var integer ORDER ascending (SORT_ASC), descending (SORT_DESC)
     */
    public $orderType         = null;
    protected $adminInstalled = null;
    public $tagsMandatory;

    /**
     * @var \ReflectionClass|null $reflectionClass
     */
    public $reflectionClass = null;

    /**
     *
     * @var Record $moduleObj
     */
    protected $moduleObj;

    /**
     *
     * @var bool $usePrettyUrl
     */
    protected $usePrettyUrl;

    /**
     *
     * @var bool $useFrontendView
     */
    protected $useFrontendView;

    /**
     *
     * @var bool $moduleBackendobjects
     */
    public $moduleBackendobjects;

    /**
     * Bypass check in ValidatorUpdateContentRule
     * @var bool $byBassRuleCwh
     */
    public $byBassRuleCwh = false;

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        if (isset(\Yii::$app->params['amosDb'])) {
            $database = \Yii::$app->params['amosDb'];
            return \Yii::$app->get($database);
        }

        return parent::getDb();
    }

    /**
     * Return the view url of the CMS if is available
     * @return string
     */
    public function getBackendobjectsUrl()
    {
        $link = '';
        try {
            if (!empty($this->moduleBackendobjects) && !empty($this->moduleBackendobjects->modelsDetailMapping)) {
                $cls      = $this->className();
                $module   = $this->moduleBackendobjects;
                $link     = $module::getDetachUrl($this->id, $cls, $module->modelsDetailMapping[$cls]);
                $parseUrl = parse_url($link);
                if (!empty($parseUrl) && !empty($parseUrl['path']) && !empty($parseUrl['query'])) {
                    $link = $parseUrl['path'].'/'.$this->getPrettyUrl().'?'.$parseUrl['query'];
                }
            }
        } catch (\Exception $e) {
            Yii::getLogger()->log($e->getMessage().'-'.$e->getFile().'-'.$e->getLine(), \yii\log\Logger::LEVEL_ERROR);
        }
        return $link;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios                               = parent::scenarios();
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
        $className = get_called_class();
        /** @var ActiveRecord $model */
        $model     = new $className();
        $return    = parent::find();
        if ($model->hasAttribute('deleted_at')) {
            $tableName = $className::tableName();
            $return->andWhere([$tableName.'.deleted_at' => null]);
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

    public function afterFind()
    {
        parent::afterFind();
        if (!empty(\Yii::$app->params['disableAfterFindPurify'])) {
            return;
        }

        foreach ($this->attributes as $key => $value) {
            if (is_string($this->$key)) {
                $this->$key = StringHelper::purifyString(Html::decode($value));
            }
        }
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
     * Init the order variables from the module config
     */
    public function initOrderVars()
    {
        //if the search is enabled
        if (
            isset(\Yii::$app->controller->module) &&
            isset(\Yii::$app->controller->module->params['orderParams'][\Yii::$app->controller->id])
        ) {
            //clean var
            $moduleParams = \Yii::$app->controller->module->params['orderParams'][\Yii::$app->controller->id];

            //check if is set an array of order params
            if (
                isset($moduleParams['fields']) &&
                $moduleParams['fields']
            ) {
                $this->setOrderAttributes($moduleParams['fields']);
            }

            //check if is set a default value
            if (
                isset($moduleParams['default_field']) &&
                $moduleParams['default_field']
            ) {
                $this->setOrderAttribute($moduleParams['default_field']);
            }

            //check if is set a default order value
            if (
                isset($moduleParams['order_type']) &&
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
        $rules     = parent::rules();
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
                    $tagRoots = \open20\amos\tag\models\TagModelsAuthItemsMm::find()->andWhere(['classname' => $this->className()])->addSelect('tag_id')->groupBy('tag_id')->column();
                    if ($tagRoots) {
                        $ruleTags = [
                            [
                                'tagsMandatory',
                                'required',
                                'when' => function ($model) use ($tagRoots) {
                                    if (!is_null($model->regola_pubblicazione) && in_array($model->regola_pubblicazione,
                                            [2, 4]) && empty($model->tagValues)
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
                                    var tagValueTrees = $('input[name^=\"".$this->formName()."[tagValues][\"]');
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
        $parseFields = [];
        $tableName   = $this->tableName();
        foreach ($fields as $k => $v) {
            $table         = \Yii::$app->db->schema->getTableSchema($tableName);
            $exist         = (strpos($v, $this->tableName().'.') !== false || strpos($v, $this->tableName().'`.') !== false
                || !isset($table->columns[$v]));
            $parseFields[] = ($exist === false ? $this->tableName().'.' : '').$v;
        }
        $this->orderAttributes = $parseFields;
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
            $tableName            = $this->tableName();
            $table                = \Yii::$app->db->schema->getTableSchema($tableName);
            $exist                = (strpos($v, $this->tableName().'.') !== false || strpos($v, $this->tableName().'`.')
                !== false || !isset($table->columns[$v]));
            $this->orderAttribute = ($exist === false ? $this->tableName().'.' : '').$field;
        } else {
            $this->orderAttribute = $this->tableName().'.id';
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
        $this->orderType = (int) $type;
        return true;
    }

    /**
     * Identifies the sort fields
     *
     * @param $params
     */
    public function setOrderVars($params)
    {
        $classSearch = Inflector::id2camel(\Yii::$app->controller->id, '-').'Search';

        if (
            array_key_exists($classSearch, $params) &&
            array_key_exists("orderAttribute", $params[$classSearch]) &&
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
        $this->adminInstalled  = \Yii::$app->getModule(\open20\amos\admin\AmosAdmin::getModuleName());
        $this->reflectionClass = new \ReflectionClass(static::className());
        if (empty($this->moduleBackendobjects)) {
            $this->moduleBackendobjects = Yii::$app->getModule('backendobjects');
        }

        if (empty($this->getModuleObj())) {
            if (!empty(\Yii::$app->controller) && !empty(\Yii::$app->controller->module)) {
                $moduleName = \Yii::$app->controller->module->id;
                $module     = \Yii::$app->getModule($moduleName);
                $this->setModuleObj($module);
            }
        }
        if (empty($this->getUsePrettyUrl())) {
            if (!empty($this->moduleObj) && !empty($this->moduleObj->usePrettyUrl) && ($this->moduleObj->usePrettyUrl == true)) {
                $this->setUsePrettyUrl(true);
            }
        }
        if (empty($this->getUseFrontendView())) {
            if (!empty($this->moduleObj) && !empty($this->moduleObj->useFrontendView) && ($this->moduleObj->useFrontendView
                == true)) {
                $this->setUseFrontendView(true);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(),
                [
                    'orderAttribute' => BaseAmosModule::t('amoscore', 'Campo di ordinamento'),
                    'orderType' => BaseAmosModule::t('amoscore', 'Criterio di ordinamento'),
                    'createdUserProfile' => BaseAmosModule::t('amoscore', 'Creato da'),
                    'updatedUserProfile' => BaseAmosModule::t('amoscore', 'Ultimo aggiornamento di'),
                    'deletedUserProfile' => BaseAmosModule::t('amoscore', 'Cancellato da')
                ]
        );
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviorsParent = parent::behaviors();

        $behaviors             = [
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
                $columnNames        = $this->getTableSchema()->getColumnNames();
                $representingColumn = $columnNames[0];
            }

        if (is_array($representingColumn)) {
            $part = '';
            foreach ($representingColumn as $representingColumn_item) {
                $part .= ($this->$representingColumn_item === null ? '' : $this->__shortText($this->$representingColumn_item,
                        30)).' ';
            }
            return substr($part, 0, -1);
        } elseif (is_string($representingColumn)) {
            return $representingColumn;
        } else {
            return $this->$representingColumn === null ? '' : (string) $this->$representingColumn;
        }
    }

    public function toStringWithCharLimit($char_limit = 30)
    {
        $representingColumn = $this->representingColumn();
        if (($representingColumn === null) || ($representingColumn === array()))
                if ($this->getTableSchema()->primaryKey !== null) {
                $representingColumn = $this->getTableSchema()->primaryKey;
            } else {
                $columnNames        = $this->getTableSchema()->getColumnNames();
                $representingColumn = $columnNames[0];
            }

        if (is_array($representingColumn)) {
            $part = '';
            foreach ($representingColumn as $representingColumn_item) {
                $part .= ($this->$representingColumn_item === null ? '' : $this->__shortText($this->$representingColumn_item,
                        $char_limit)).' ';
            }
            return substr($part, 0, -1);
        } elseif (is_string($representingColumn)) {
            return $representingColumn;
        } else {
            return $this->$representingColumn === null ? '' : (string) $this->$representingColumn;
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
            return $asString."...";
        } else {
            return $asString;
        }
    }

    /**
     * @return ActiveQuery|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getCreatedUserProfile()
    {
        if ($this->adminInstalled) {
            $modelClass = \open20\amos\admin\AmosAdmin::instance()->createModel('UserProfile');
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
            $modelClass = \open20\amos\admin\AmosAdmin::instance()->createModel('UserProfile');
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
            $modelClass = \open20\amos\admin\AmosAdmin::instance()->createModel('UserProfile');
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

        $this->setUseBullet();

        if ($isDemo && (Yii::$app instanceof Web) && $this->inBlackList()) {
            $key     = 'success';
            $message = BaseAmosModule::t('amoscore', 'In Demo non &eacute; possibile modificare i contenuti');
            $flashes = Yii::$app->session->getFlash($key);
            if (!Yii::$app->session->hasFlash($key) || !in_array($message, $flashes)) {
                Yii::$app->getSession()->addFlash($key, $message);
            }
            return false;
        }

        // checking for tagging user_profile
        if (!$this->isNewRecord) {

            if (isset(\Yii::$app->params['mention-models-enabled']) && is_array(\Yii::$app->params['mention-models-enabled'])
                && array_key_exists($this->className(), \Yii::$app->params['mention-models-enabled'])
            ) {

                $user_profile_ids = [];

                if (method_exists($this, 'getValidatedStatus')) {

                    if ($this->getValidatedStatus() == $this->status) {
                        // extract user_profile id filtered..
                        $user_profile_ids = $this->checkNewMentionIds();
                    }
                } else {
                    $user_profile_ids = $this->checkNewMentionIds();
                }

                // get all UserProfile for sending a mail
                $user_profiles = \open20\amos\admin\models\UserProfile::find()
                    ->andWhere(['id' => $user_profile_ids])
                    ->all();

                // send email for user_profiles
                $this->sendEmailForUserProfiles($user_profiles);
            }
        }


        return parent::beforeSave($insert);
    }

    /**
     *
     */
    protected function setUseBullet()
    {
        if ($this->isNewRecord) {
            $this->useBullet = true;
        } else {
            if (array_key_exists('status', $this->getDirtyAttributes())) {
                $this->useBullet = true;
            } else {
                $this->useBullet = false;
            }
        }
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
            'amos_workflow_transitions_log',
            'token_users',
            'token_group',
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

        $this->updateBullets($tableName);

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     *
     * @param int $type
     * @param bool $reset
     * @param string $tableName
     * @param bool $general
     * @param bool $enabled_only_by_community
     * @return int
     */
    public function getBullet($type = 1, $reset = false, $tableName = null, $general = false,
                              $enabled_only_by_community = false)
    {
        $enabled = true;
        if ($enabled_only_by_community == true) {
            $enabled = (self::checkScope() == 0 ? false : true);
        }
        if (!\Yii::$app->user->isGuest && $enabled == true) {
            if (empty($tableName)) {
                $tableName = self::getTableSchema()->name;
            }

            $result = self::getBulletType($type, $tableName, $reset, $general, $enabled_only_by_community);

            return $result['bullet'];
        } else return 0;
    }

    /**
     *
     * @param int $type
     * @param bool $reset
     * @param string $tableName
     * @param bool $general
     * @param bool $enabled_only_by_community
     * @return int
     */
    public static function getStaticBullet($type = 1, $reset = false, $tableName, $general = false,
                                           $enabled_only_by_community = false)
    {
        $enabled = true;
        if ($enabled_only_by_community == true) {
            $enabled = (self::checkScope() == 0 ? false : true);
        }
        if (!\Yii::$app->user->isGuest && $enabled == true) {

            $result = self::getBulletType($type, $tableName, $reset, $general, $enabled_only_by_community);

            return $result['bullet'];
        } else return 0;
    }

    /**
     *
     * @param int $type
     * @param string $tableName
     * @param bool $reset
     * @param bool $general
     * @return array
     */
    public static function getBulletType($type, $tableName, $reset, $general = false)
    {
        $result       = ['bullet' => 0];
        $resultAll    = 0;
        $community_id = self::checkScope();
        $userId       = \Yii::$app->user->id;
        $values       = [];

        $sqlMyCommunities = "SELECT community_id id FROM `community_user_mm` WHERE `user_id` = ".\Yii::$app->user->id." AND status = 'ACTIVE'";
        $myCommunities    = \yii\helpers\ArrayHelper::map(\Yii::$app->db->createCommand()->setSql($sqlMyCommunities)->queryAll(),
                'id', 'id');
        $myCommunities[0] = 0;
        switch ($type) {
            case self::BULLET_TYPE_ALL:
                if ($community_id > 0 && $general == false) {
                    $queryOwnA = new Query();
                    $queryOwnA->select(new Expression("MAX(U.updated_at)"))
                        ->from("notification_update U")
                        ->andWhere(new \yii\db\Expression("U.module like '{$tableName}'"))
                        ->andWhere(['U.deleted_at' => null])
                        ->andWhere(['U.community_id' => $myCommunities]);

                    $queryOwnB = new Query();
                    $queryOwnB->select(new Expression("B.user_id"))
                        ->from("notification_user B")
                        ->andWhere(new \yii\db\Expression("B.module like '{$tableName}'"))
                        ->andWhere(['B.deleted_at' => null])
                        ->andWhere(['B.user_id' => $userId])
                        ->andWhere(['B.community_id' => $myCommunities]);

                    $queryOwnC = new Query();
                    $queryOwnC->select(new Expression("B.user_id"))
                        ->from("notification_user B")
                        ->andWhere(new \yii\db\Expression("B.module like '{$tableName}'"))
                        ->andWhere(['B.deleted_at' => null])
                        ->andWhere(['B.user_id' => $userId])
                        ->andWhere(['B.community_id' => $myCommunities]);

                    $queryOwnA->andWhere(['U.community_id' => $community_id]);
                    $queryOwnB->andWhere(['B.community_id' => $community_id]);
                    $queryOwnC->andWhere(['B.community_id' => $community_id])->limit(1);

                    $resultA = $queryOwnA->column();

                    if (!empty($resultA[0])) {
                        $queryOwnB->andWhere(['<', 'B.updated_at', $resultA[0]])->limit(1);

                        $count  = (empty($queryOwnB->limit(1)->scalar()) ? 0 : 1);
                        $countC = (empty($queryOwnC->limit(1)->scalar()) ? 0 : 1);
                        if ($count == 0 && $countC == 0) {
                            $result = ['bullet' => 1];
                        } else $result = ['bullet' => $count];
                    }
                } else {

                    $queryOwnA = new Query();
                    $queryOwnA->select(new Expression("MAX(U.updated_at)"))
                        ->from("notification_update U")
                        ->andWhere(new \yii\db\Expression("U.module like '{$tableName}'"))
                        ->andWhere(['U.deleted_at' => null])
                        ->andWhere(['U.community_id' => $myCommunities]);

                    $queryOwnB = new Query();
                    $queryOwnB->select(new Expression("B.user_id"))
                        ->from("notification_user B")
                        ->andWhere(new \yii\db\Expression("B.module like '{$tableName}'"))
                        ->andWhere(['B.deleted_at' => null])
                        ->andWhere(['B.user_id' => $userId])
                        ->andWhere(['B.community_id' => $myCommunities]);

                    $queryOwnC = new Query();
                    $queryOwnC->select(new Expression("B.user_id"))
                        ->from("notification_user B")
                        ->andWhere(new \yii\db\Expression("B.module like '{$tableName}'"))
                        ->andWhere(['B.deleted_at' => null])
                        ->andWhere(['B.user_id' => $userId])
                        ->andWhere(['B.community_id' => $myCommunities])
                        ->limit(1);

                    $resultA = $queryOwnA->column();

                    if (!empty($resultA[0])) {
                        $queryOwnB->andWhere(['<', 'B.updated_at', $resultA[0]])->limit(1);

                        $count  = (empty($queryOwnB->limit(1)->scalar()) ? 0 : 1);
                        $countC = (empty($queryOwnC->limit(1)->scalar()) ? 0 : 1);
                        if ($count == 0 && $countC == 0) {
                            $result = ['bullet' => 1];
                        } else $result = ['bullet' => $count];
                    }
                }

                if ($reset) {
                    if (!empty($community_id)) {
                        \Yii::$app->db->createCommand()->setSql(
                            "INSERT INTO
                                    `notification_user`
                                    (`user_id`,`module`, `publication_rule`, `community_id`, `created_at`,
                                    `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`)
                                VALUES
                                    ({$userId}, '{$tableName}', 3, {$community_id}, now(),
                                        '{$userId}', now(), '{$userId}', null, null),
                                    ({$userId}, '{$tableName}', 4, {$community_id}, now(),
                                        '{$userId}', now(), '{$userId}', null, null)
                                ON DUPLICATE KEY UPDATE
                                    `updated_at` = now()"
                        )->execute();
                    } else {
                        foreach ($myCommunities as $comm) {
                            $values[] = "({$userId}, '{$tableName}', 1, {$comm}, now(), '{$userId}', now(), '{$userId}', null, null)";
                            $values[] = "({$userId}, '{$tableName}', 2, {$comm}, now(), '{$userId}', now(), '{$userId}', null, null)";
                            $values[] = "({$userId}, '{$tableName}', 3, {$comm}, now(), '{$userId}', now(), '{$userId}', null, null)";
                            $values[] = "({$userId}, '{$tableName}', 4, {$comm}, now(), '{$userId}', now(), '{$userId}', null, null)";
                        }

                        \Yii::$app->db->createCommand()->setSql(
                            "INSERT INTO
                                    `notification_user`
                                    (`user_id`,`module`, `publication_rule`, `community_id`, `created_at`,
                                    `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`)
                                VALUES
                                     ".implode(',', $values)."
                                ON DUPLICATE KEY UPDATE
                                    `updated_at` = now()"
                        )->execute();
                    }
                    $query     = (new \yii\db\Query())->from('notification_update')
                        ->andWhere(['module' => $tableName])
                        ->andFilterWhere(['community_id' => $community_id])
                        ->select('content_id AS id');
                    $classname = get_called_class();
                    self::setOffChannelRead($userId, $classname, $query);
                }
                break;
            case self::BULLET_TYPE_OWN:
                $allConditions = self::getAllConditionsForQueryByTag($userId);
                $conditions    = $allConditions[0];
                $conditionsNot = $allConditions[1];

                $queryOwnA = new Query();
                $queryOwnA->select(new Expression("MAX(U.updated_at)"))
                    ->from("notification_update U")
                    ->andWhere([
                        'OR',
                        ['U.publication_rule' => [1, 3]],
                        $conditions,
                    ])
                    ->andWhere(new \yii\db\Expression("U.module like '{$tableName}'"))
                    ->andWhere(['U.deleted_at' => null])
                    ->andWhere(['U.community_id' => $myCommunities]);

                $queryOwnB = new Query();
                $queryOwnB->select(new Expression("B.user_id"))
                    ->from("notification_user B")
                    ->andWhere(new \yii\db\Expression("B.module like '{$tableName}'"))
                    ->andWhere(['B.deleted_at' => null])
                    ->andWhere(['B.user_id' => $userId])
                    ->andWhere(['B.community_id' => $myCommunities]);

                $queryAllA = new Query();
                $queryAllA->select(new Expression("MAX(U.updated_at)"))
                    ->from("notification_update U")
                    ->andWhere($conditionsNot)
                    ->andWhere(new \yii\db\Expression("U.module like '{$tableName}'"))
                    ->andWhere(['U.deleted_at' => null])
                    ->andWhere(['U.community_id' => $myCommunities]);

                $queryAllB = new Query();
                $queryAllB->select(new Expression("B.user_id"))
                    ->from("notification_user B")
                    ->andWhere(new \yii\db\Expression("B.module like '{$tableName}'"))
                    ->andWhere(['B.deleted_at' => null])
                    ->andWhere(['B.publication_rule' => [1, 3]])
                    ->andWhere(['B.user_id' => $userId])
                    ->andWhere(['B.community_id' => $myCommunities]);

                $queryOwnC = new Query();
                $queryOwnC->select(new Expression("B.user_id"))
                    ->from("notification_user B")
                    ->andWhere(new \yii\db\Expression("B.module like '{$tableName}'"))
                    ->andWhere(['B.deleted_at' => null])
                    ->andWhere(['B.user_id' => $userId])
                    ->andWhere(['B.community_id' => $myCommunities])
                    ->limit(1);

                if ($general == false) {
                    $queryOwnA->andFilterWhere(['U.community_id' => $community_id]);
                    $queryOwnB->andFilterWhere(['B.community_id' => $community_id]);
                    $queryOwnC->andFilterWhere(['B.community_id' => $community_id]);
                    $queryAllA->andFilterWhere(['U.community_id' => $community_id]);
                    $queryAllB->andFilterWhere(['B.community_id' => $community_id]);
                }

                $resultA    = $queryOwnA->column();
                $resultAllA = $queryAllA->column();
                if (!empty($resultA[0])) {
                    $queryOwnB->andWhere(['<', 'B.updated_at', $resultA[0]])->limit(1);

                    $count  = (empty($queryOwnB->limit(1)->scalar()) ? 0 : 1);
                    $countC = (empty($queryOwnC->limit(1)->scalar()) ? 0 : 1);
                    if ($count == 0 && $countC == 0) {
                        $result = ['bullet' => 1];
                    } else $result = ['bullet' => $count];
                }

                if (!empty($resultAllA[0])) {
                    $queryAllB->andWhere(['<', 'B.updated_at', $resultAllA[0]]);
                    $resultAll = (empty($queryAllB->limit(1)->scalar()) ? 0 : 1);
                }


                if ($reset) {
                    if (!empty($community_id)) {
                        \Yii::$app->db->createCommand()->setSql(
                            "INSERT INTO
                                    `notification_user`
                                    (`user_id`,`module`, `publication_rule`, `community_id`, `created_at`,
                                    `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`)
                                VALUES
                                    ({$userId}, '{$tableName}', 3, {$community_id}, now(),
                                        '{$userId}', now(), '{$userId}', null, null),
                                    ({$userId}, '{$tableName}', 4, {$community_id}, now(),
                                        '{$userId}', now(), '{$userId}', null, null)
                                ON DUPLICATE KEY UPDATE
                                    `updated_at` = now()"
                        )->execute();
                    } else {
                        if ($resultAll == 0) {

                            foreach ($myCommunities as $comm) {
                                $values[] = "({$userId}, '{$tableName}', 1, {$comm}, now(), '{$userId}', now(), '{$userId}', null, null)";
                                $values[] = "({$userId}, '{$tableName}', 2, {$comm}, now(), '{$userId}', now(), '{$userId}', null, null)";
                                $values[] = "({$userId}, '{$tableName}', 3, {$comm}, now(), '{$userId}', now(), '{$userId}', null, null)";
                                $values[] = "({$userId}, '{$tableName}', 4, {$comm}, now(), '{$userId}', now(), '{$userId}', null, null)";
                            }

                            \Yii::$app->db->createCommand()->setSql(
                                "INSERT INTO
                                    `notification_user`
                                    (`user_id`,`module`, `publication_rule`, `community_id`, `created_at`,
                                    `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`)
                                VALUES
                                     ".implode(',', $values)."
                                ON DUPLICATE KEY UPDATE
                                    `updated_at` = now()"
                            )->execute();
                        } else {
                            foreach ($myCommunities as $comm) {
                                $values[] = "({$userId}, '{$tableName}', 2, {$comm}, now(), '{$userId}', now(), '{$userId}', null, null)";
                                $values[] = "({$userId}, '{$tableName}', 4, {$comm}, now(), '{$userId}', now(), '{$userId}', null, null)";
                            }
                            \Yii::$app->db->createCommand()->setSql(
                                "INSERT INTO
                                    `notification_user`
                                    (`user_id`,`module`, `publication_rule`, `community_id`, `created_at`,
                                    `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`)
                                VALUES
                                    ".implode(',', $values)."
                                ON DUPLICATE KEY UPDATE
                                    `updated_at` = now()"
                            )->execute();
                        }
                    }
                    $query     = (new \yii\db\Query())->from('notification_update')
                        ->andWhere(['module' => $tableName])
                        ->andFilterWhere(['community_id' => $community_id])
                        ->select('content_id AS id');
                    $classname = get_called_class();
                    self::setOffChannelRead($userId, $classname, $query);
                }

                break;
        }

        return $result;
    }

    /**
     *
     * @param type $tableName
     */
    public function updateBullets($tableName)
    {
        if (\Yii::$app instanceof \yii\web\Application && \Yii::$app->user->id && $this->useBullet) {

            /**
             * Something was changed in my own interest areas?
             */
            $whiteList = self::getWhiteListBulletCount();

            if (in_array($tableName, $whiteList)) {

                $classname = $this->className();

                $attributes = $this->getAttributes();
                $created_by = null;
                $updated_by = null;

                if (isset($attributes['created_by'])) {
                    $created_by = $attributes['created_by'];
                }

                if (isset($attributes['updated_by'])) {
                    $updated_by = $attributes['updated_by'];
                }

                if (is_null($updated_by)) {
                    $updated_by = \Yii::$app->user->id;
                    $updated_by = 1;
                }

                if (is_null($created_by)) {
                    $created_by = \Yii::$app->user->id;
                }


                $moduleCwh = \Yii::$app->getModule('cwh');

                $formName = $this->formName();
                $post     = \Yii::$app->request->post();

                $community_id = self::checkScope();
                $tags         = [];
                $network      = (property_exists($this, 'regola_pubblicazione') ? $this->regola_pubblicazione : 0);
                if ($network == null) {
                    $network = 0;
                }

                if (!empty($formName) && !empty($post) && !empty($post[$formName])) {
                    $tags = $post[$formName]['tagValues'];
                }
                $saveTag      = false;
                $allTagByRoot = [];
                if (method_exists($this, 'getValidatedStatus')) {
                    if ($this->getValidatedStatus() == $this->status) {
                        if (!empty($tags) && is_array($tags)) {
                            foreach ($tags as $tag) {
                                if (!empty($tag)) {
                                    $allTagByRoot[] = $tag;
                                }
                            }
                            if (!empty($allTagByRoot)) {
                                $saveTag = true;
                                $tagText = implode(',', $allTagByRoot);
                                \Yii::$app->db->createCommand()->setSql(
                                    "INSERT INTO
                                            `notification_update`
                                            (`module`,`content_id`, `publication_rule`, `tags`, `community_id`, `created_at`,
                                            `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`)
                                        VALUES ('{$tableName}', {$this->id} , {$network}, '{$tagText}', {$community_id}, now(),
                                            '{$created_by}', now(), '{$updated_by}', null, null)
                                        ON DUPLICATE KEY UPDATE
                                            `updated_at` = now(),
                                            `updated_by` = '{$updated_by}'"
                                )->execute();
                            }
                        }
                        if ($saveTag == false) {
                            \Yii::$app->db->createCommand()->setSql(
                                "INSERT INTO
                                            `notification_update`
                                            (`module`,`content_id`, `publication_rule`, `tags`, `community_id`, `created_at`,
                                            `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`)
                                        VALUES ('{$tableName}', {$this->id} , {$network}, null, {$community_id}, now(),
                                            '{$created_by}', now(), '{$updated_by}', null, null)
                                        ON DUPLICATE KEY UPDATE
                                            `updated_at` = now(),
                                            `updated_by` = '{$updated_by}'"
                            )->execute();
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * @return integer
     */
    public static function checkScope()
    {
        $community_id = 0;
        $moduleCwh    = \Yii::$app->getModule('cwh');

        if (!empty($moduleCwh)) {
            /** @var \open20\amos\cwh\AmosCwh $moduleCwh */
            $scope = $moduleCwh->getCwhScope();
            if (!empty($scope)) {
                if (!empty($scope['community'])) {
                    $community_id = $scope['community'];
                }
            }
        }
        return $community_id;
    }

    /**
     * @return array
     */
    public static function getWhiteListBulletCount()
    {
        return [
            'user', //admin
            'community',
            'discussioni_topic', //discussioni
            'documenti',
            'een',
            'event', //events
            'news',
            'organizations',
            'partnership_profiles', //partnershipprofiles
            'projects',
            'result',
            'showcase_project',
            'sondaggi',
            'profilo'
        ];
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
        $ret                = false;
        $demoModelBlackList = isset(\Yii::$app->params['demoModelBlackList']) ? \Yii::$app->params['demoModelBlackList']
                : [];
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
            $key     = 'success';
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

        $enablePurifyDataParam = true;

        if (isset(Yii::$app->params['forms-purify-data']) && (Yii::$app->params['forms-purify-data'] == false)) {
            $enablePurifyDataParam = false;
        }

        if ($enablePurifyDataParam == true) {

            if (isset(Yii::$app->params['forms-purify-data-white-models'])) {
                $listClassModels = Yii::$app->params['forms-purify-data-white-models'];
                if (in_array($this->className(), $listClassModels)) {
                    return parent::beforeValidate();
                }
            }

            $listAttributes = $this->attributes;
            foreach ($listAttributes as $key => $attribute) {
                if (is_string($this->$key)) {
                    $this->$key = StringHelper::purifyString($this->$key);
                }
            }
        }

        /** @var SimpleWorkflowBehavior $workflowBehavior */
        $workflowBehavior = $this->findBehaviorByClassName(SimpleWorkflowBehavior::className());
        if (!$this->isNewRecord && !is_null($workflowBehavior)) {
            $statusAttribute = $workflowBehavior->statusAttribute;
            $thisStatus      = $this->{$statusAttribute};
            try {
                $ok = WorkflowHelper::isValidNextStatus($this, $thisStatus);
            } catch (WorkflowException $exception) {
                $this->addError($statusAttribute, BaseAmosModule::t('amoscore', '#workflow_status_error_wrong_status'));
                return false;
            }
            if (!$ok) {
                $this->addError($statusAttribute,
                    BaseAmosModule::t('amoscore', '#workflow_status_error_status_not_valid'));
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
                $users = \open20\amos\cwh\models\CwhAuthAssignment::find()->andWhere([
                            'item_name' => $moduleCwh->permissionPrefix.'_VALIDATE_'.$this->className(),
                        ])->andWhere(['in', 'cwh_nodi_id', $this->validatori])
                        ->select('user_id')->groupBy('user_id')->asArray()->column();
            }
            if (empty($users) && $this instanceof WorkflowModelInterface) {
                $validatorRole = $this->getValidatorRole();
                $authManager   = \Yii::$app->authManager;
                $users         = $authManager->getUserIdsByRole($validatorRole);
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
        /*         * @var $behavior Behavior */
        foreach ($behaviors as $behavior) {
            if ($behavior->hasMethod(__FUNCTION__)) {
                $panelsAttributes = $behavior->{__FUNCTION__}($disableLink);
                $panels           = ArrayHelper::merge($panels, $panelsAttributes);
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
        $behaviors        = $this->getBehaviors();
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
     * This method find the behavior array index by his classname.
     * @param string $className
     * @return mixed
     */
    public function findBehaviorIndexByClassName($className)
    {
        $behaviors     = $this->getBehaviors();
        $behaviorIndex = null;
        foreach ($behaviors as $index => $behavior) {
            /** @var Behavior $behavior */
            if ($behavior->className() == $className) {
                $behaviorIndex = $index;
                break;
            }
        }
        return $behaviorIndex;
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
                'name' => $this->formName().$formNameSuffix.'['.$attribute.']',
                'id' => strtolower($this->formName()).'-'.strtolower($formNameSuffix).'-'.$attribute,
            ];
        }
        return $newFormFieldNamesAndIds;
    }

    /**
     * This method return the base workflow status label. It checks if the workflow behavior is present,
     * then checks if the model has a workflow status and return the base label.
     * @return string
     */
    public function getWorkflowBaseStatusLabel()
    {
        $label       = '';
        $hasWorkflow = false;
        if ($this->getBehavior('workflow') || $this->findBehaviorByClassName(SimpleWorkflowBehavior::className())) {
            $hasWorkflow = true;
        }
        if ($hasWorkflow && $this->hasWorkflowStatus()) {
            /** @var Status $status */
            $status = $this->getWorkflowStatus();
            if ($status) {
                $label = $status->getLabel();
            }
        }
        return $label;
    }

    /**
     * This method return the correct workflow status label. It checks if the workflow behavior is present,
     * then checks if the model has a workflow status and return the correct label.
     * @return string
     */
    public function getWorkflowStatusLabel()
    {
        $label       = '';
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
        $moduleTag       = Yii::$app->getModule('tag');
        $enableTagSearch = (isset($moduleTag) && in_array($className, $moduleTag->modelsEnabled));

        $searchModels = [];
        if ($enableTagSearch) {
            $dataProvider->query->leftJoin('entitys_tags_mm e_tag',
                "e_tag.record_id= ".$tableName.".id AND e_tag.deleted_at IS NULL AND e_tag.classname='".addslashes($className)."'");

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
        $namespaceName  = $this->reflectionClass->getNamespaceName();
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
        $modelShortName      = $this->reflectionClass->getShortName();
        $pieces              = StringUtils::splitAtUpperCase($modelShortName);
        $modelControllerName = '';
        $isFirst             = true;
        foreach ($pieces as $piece) {
            if (!$isFirst) {
                $modelControllerName .= '-';
            }
            $modelControllerName .= strtolower($piece);
            $isFirst             = false;
        }
        return $modelControllerName;
    }

    /**
     * @return string
     */
    protected function getBasicUrl()
    {
        return $this->getModelModuleName().'/'.$this->getModelControllerName().'/';
    }

    /**
     * Returns the full url to the action with the model id.
     * @param $url
     * @return null|string
     */
    protected function getBasicFullUrl($url)
    {
        if (!empty($url)) {
            return Url::toRoute(["/".$url, "id" => $this->id]);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getCreateUrl()
    {
        return $this->getBasicUrl().'create';
    }

    /**
     * @inheritdoc
     */
    public function getFullCreateUrl()
    {
        return $this->getCreateUrl();
    }

    /**
     * @inheritdoc
     */
    public function getViewUrl()
    {
        if (!empty($this->usePrettyUrl) && ($this->usePrettyUrl == true) && $this->hasMethod('getPrettyUrl') && !empty($this->getPrettyUrl())) {
            return $this->getBasicUrl();
        } else {
            return $this->getBasicUrl().'view';
        }
    }

    /**
     * @inheritdoc
     */
    public function getFullViewUrl()
    {
        if (!empty($this->usePrettyUrl) && ($this->usePrettyUrl == true) && $this->hasMethod('getPrettyUrl') && !empty($this->getPrettyUrl())) {
            return Url::toRoute(["/".$this->getViewUrl()."/".$this->id."/".$this->getPrettyUrl()]);
        } else if (!empty($this->useFrontendView) && ($this->useFrontendView == true) && method_exists($this,
                'getBackendobjectsUrl')) {
            return $this->getBackendobjectsUrl();
        } else {
            return $this->getBasicFullUrl($this->getViewUrl());
        }
    }

    /**
     * @inheritdoc
     */
    public function getUpdateUrl()
    {
        return $this->getBasicUrl().'update';
    }

    /**
     * @inheritdoc
     */
    public function getFullUpdateUrl()
    {
        return $this->getBasicFullUrl($this->getUpdateUrl());
    }

    /**
     * @inheritdoc
     */
    public function getDeleteUrl()
    {
        return $this->getBasicUrl().'delete';
    }

    /**
     * @inheritdoc
     */
    public function getFullDeleteUrl()
    {
        return $this->getBasicFullUrl($this->getDeleteUrl());
    }

    /**
     * @return |null
     */
    public function getCloseCommentThread()
    {
        return null;
    }

    /**
     * @param $closeCommentThread
     */
    public function setCloseCommentThread($closeCommentThread)
    {

    }

    private function updateFrequentlyCommunityUpdate()
    {
        Url::remember();
        $moduleCwh       = \Yii::$app->getModule('cwh');
        $moduleCommunity = \Yii::$app->getModule('community');

        if (isset($moduleCommunity) && isset($moduleCwh) && !empty($moduleCwh->getCwhScope())) {
            $scope = $moduleCwh->getCwhScope();
            if (isset($scope['community'])) {
                $id        = $scope['community'];
                $community = \open20\amos\community\models\Community::findOne($id);
                if (!empty($community)) {
                    $this->modelSearch->community_id = $id;

                    // salvare l'aggioamento della community
                }
            }
        }
    }

    /**
     * This method duplicates this content row.
     * @return Record|null
     * @throws \raoul2000\workflow\base\WorkflowException
     * @throws \yii\base\InvalidConfigException
     */
    public function duplicateContentRow()
    {
        $loggedUserId  = Yii::$app->user->id;
        $now           = date('Y-m-d H:i:s');
        $thisClassname = $this->className();

        /** @var Record $newContent */
        $newContent             = Yii::createObject($thisClassname);
        $newContent->detachBehavior('cwhBehavior');
        $newContent->setAttributes($this->attributes);
        $newContent->id         = null;
        $newContent->created_by = $loggedUserId;
        $newContent->updated_by = $loggedUserId;
        $newContent->created_at = $now;
        $newContent->updated_at = $now;

        /** @var SimpleWorkflowBehavior $workflowBehavior */
        $workflowBehavior = null;

        if (isset($newContent->behaviors['workflow'])) {
            $workflowBehavior = $newContent->behaviors['workflow'];
        } else {
            $workflowBehaviorIndex = $this->findBehaviorIndexByClassName(SimpleWorkflowBehavior::className());
            if (!is_null($workflowBehaviorIndex)) {
                $workflowBehavior = $newContent->behaviors[$workflowBehaviorIndex];
            }
        }

        if (!is_null($workflowBehavior)) {
            $workflowBehavior->initStatus();
            $newContent->status = $workflowBehavior->getWorkflow()->getInitialStatusId();
        }

        $ok = $newContent->save(false);

        return ($ok ? $newContent : null);
    }

    /**
     *
     * @return bool
     */
    public function getUsePrettyUrl()
    {
        return $this->usePrettyUrl;
    }

    /**
     *
     * @param bool $usePrettyUrl
     */
    public function setUsePrettyUrl($usePrettyUrl)
    {
        $this->usePrettyUrl = $usePrettyUrl;
    }

    /**
     *
     * @return bool
     */
    public function getUseFrontendView()
    {
        return $this->useFrontendView;
    }

    /**
     *
     * @param bool $useFrontendView
     */
    public function setUseFrontendView($useFrontendView)
    {
        $this->useFrontendView = $useFrontendView;
    }

    /**
     *
     * @return bool
     */
    public function getModuleObj()
    {
        return $this->moduleObj;
    }

    /**
     *
     * @param Record $moduleObj
     */
    public function setModuleObj($moduleObj)
    {
        $this->moduleObj = $moduleObj;
    }

    /**
     * Method to get user ID from text for tagged users
     *
     * @param string | text | $text
     *
     * @return array | $ids
     */
    public static function getMentionUserIdFromText($text)
    {

        $ids        = [];
        $occurrence = '>@';
        $count      = substr_count($text, $occurrence);
        $text       = $text;

        // loop for each occurrence found within the text
        for ($i = 0; $i < $count; $i++) {

            $pos = strpos($text, $occurrence);

            if ($pos !== false) {
                $newStr = substr($text, 0, $pos);

                $text = substr($text, $pos + 3);
                $pos2 = strrpos($newStr, '<a href="');

                $newStr2 = substr($newStr, $pos2 + 9);
                $posF    = strpos($newStr2, '"');

                $link     = substr($newStr2, 0, $posF);
                $parseUrl = parse_url($link);
                if (!empty($parseUrl) && !empty($parseUrl['query'])) {
                    $kv = explode('=', $parseUrl['query']);
                    if (!empty($kv[0]) && $kv[0] == 'id' && !empty($kv[1])) {
                        $ids[] = $kv[1];
                    }
                }
            }
        }

        return $ids;
    }

    /**
     * Method to get UserProfile id
     * filtered based on the difference between the old id tagging and the new id tagging from the text field
     * filtered by user_profile -> notify_tagging_user_in_content
     *
     * @return array | $user_profile_ids
     */
    protected function checkNewMentionIds()
    {

        $user_profile_ids = [];

        foreach (\Yii::$app->params['mention-models-enabled'][$this->className()] as $v) {

            if (array_key_exists($v, $this->dirtyAttributes)) {

                $beforeIds = self::getMentionUserIdFromText($this->oldAttributes[$v]);
                $afterIds  = self::getMentionUserIdFromText($this->$v);

                // check if this model has been validated
                $count = \open20\amos\workflow\models\WorkflowTransitionsLog::find()
                    ->andWhere(['classname' => $this->className()])
                    ->andWhere(['owner_primary_key' => $this->id])
                    ->count();

                // create an array of user_profile_id to send an email tag notification
                $user_profile_ids = [];

                if ($count == 0) {

                    // all afterIds
                    $user_profile_ids = $afterIds;
                } else {

                    // extract all ids from $ afterIds where they are not in $ beforeIds
                    foreach ($afterIds as $key => $value) {
                        if (!in_array($value, $beforeIds)) {
                            $user_profile_ids[] = $value;
                        }
                    }
                }

                // filter of user profiles that have set notify_tagging_user_in_content
                $user_profiles = ArrayHelper::getColumn(
                        \open20\amos\admin\models\UserProfile::find()
                            ->select('id')
                            ->andWhere(['id' => $user_profile_ids])
                            ->andWhere(['notify_tagging_user_in_content' => 1])
                            ->andWhere(['deleted_at' => null])
                            ->all(),
                        function ($element) {
                            return $element['id'];
                        }
                );
            }
        }

        return $user_profile_ids;
    }

    /**
     * Method to send email to list UserProfile
     *
     * @param string $modelContext
     * @param string $model
     * @param string $email_assistance
     * @param model | \open20\amos\admin\models\UserProfile | $user_profiles
     *
     * @return void
     */
    public function sendEmailForUserProfiles($user_profiles, $modelContext = null, $model = null)
    {

        // create email for tagging user_profile
        $email_assistance = \Yii::$app->params['email-assistenza'];
        $subject          = BaseAmosModule::t('amoscore', "#you_have_been_tagged");

        $email = new \open20\amos\core\utilities\Email;

        try {

            foreach ($user_profiles as $key => $user_profile) {

                $message = \Yii::$app->controller->renderMailPartial('@vendor/open20/amos-core/views/email/content_tagging_user',
                    [
                        'model' => $model ?? $this,
                        'contextModel' => $modelContext ?? $this,
                        'model_field' => $v,
                        'user' => $user_profile->user
                ]);

                $email->sendMail($email_assistance, [$user_profile->user->email], $subject, $message);
            }
        } catch (\Throwable $th) {
            \Yii::getLogger()->log($th->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }
    }

    /**
     * @param array $tags
     */
    public static function setMyTags($tags)
    {
        if (empty(self::$myTags)) {
            self::$myTags = $tags;
        }
    }

    /**
     *
     * @return array|null
     */
    public static function getMyTags()
    {
        return self::$myTags;
    }

    /**
     * @param integer $userId
     * @return array
     */
    public static function getAllConditionsForQueryByTag($userId)
    {
        if (empty(self::getMyTags())) {
            $sqlTag = "SELECT `tag_id` FROM `cwh_tag_owner_interest_mm` WHERE `record_id` = {$userId} AND `deleted_at` IS NULL";
            $myTags = \yii\helpers\ArrayHelper::map(\Yii::$app->db->createCommand()->setSql($sqlTag)->queryAll(),
                    'tag_id', 'tag_id');
            self::setMyTags($myTags);
        } else {
            $myTags = self::getMyTags();
        }

        $conditions    = [];
        $conditionsNot = [];
        if (!empty($myTags)) {
            foreach ($myTags as $tg) {
                $conditions[]    = new Expression("FIND_IN_SET('$tg',U.tags) > 0");
                $conditionsNot[] = new Expression("FIND_IN_SET('$tg',U.tags) = 0");
            }
        }

        $conditions    = "U.publication_rule in (2,4) ".(!empty($conditions) ? " and (".implode(' or ', $conditions).")"
                : "");
        $conditionsNot = "U.publication_rule in (2,4) ".(!empty($conditionsNot) ? " and (".implode(' or ',
                $conditionsNot).")" : "");

        return [$conditions, $conditionsNot];
    }

    public static function setOffChannelRead($userId, $classname, $query)
    {
        $notifier = Yii::$app->getModule('notify');
        if ($notifier) {
            // Turn off counter
            $notifier->notificationOff(
                $userId, $classname, $query,
                \open20\amos\notificationmanager\models\NotificationChannels::CHANNEL_READ
            );
        }
    }
}