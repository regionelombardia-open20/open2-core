<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms
 * @category   CategoryName
 */

namespace lispa\amos\core\forms;

use lispa\amos\admin\AmosAdmin;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\tag\AmosTag;
use lispa\amos\tag\models\Tag;
use yii\base\Widget;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Class ListTagsWidget
 * @package lispa\amos\core\forms
 */
class ListTagsWidget extends Widget
{
    public $layout = "@vendor/lispa/amos-core/forms/views/widgets/widget_list_tags.php";
    protected $userProfile;
    protected $className;
    protected $userTagList;

    public $viewFilesCounter = false;
    public $pageSize = 10;
    public $withTitle = false;

    /**
     * @var AmosTag|null $moduleTag
     */
    protected $moduleTag = null;

    /**
     * @var array $rootIdsArray
     */
    public $rootIdsArray = [];

    /**
     * @var array $rootIdsArrayToExclude
     */
    public $rootIdsArrayToExclude = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->moduleTag = \Yii::$app->getModule('tag');

        if (empty($this->rootIdsArray) && !is_null($this->moduleTag)) {
            /** @var ActiveQuery $query */
            $query = Tag::find();
            $query->select(['root'])->distinct()->groupBy('root');
            if (is_array($this->rootIdsArrayToExclude) && !empty($this->rootIdsArrayToExclude)) {
                $query->andWhere(['not in', 'root', $this->rootIdsArrayToExclude]);
            }
            $this->rootIdsArray = $query->column();
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $allTag = null;
        $widget = '';

        $userProfileClass = AmosAdmin::instance()->model('UserProfile');
        // $module = Yii::$app->getModule(AmosAdmin::getModuleName());
        // $userProfileClass = $module::getInstance()->model('UserProfile');

        $classes_enabled = ArrayHelper::merge($this->moduleTag->modelsEnabled, [$userProfileClass]);
        $showTags = isset($this->moduleTag) && in_array($this->className, $classes_enabled) && $this->moduleTag->behaviors;

        if ($showTags) {
            $query = $this->getTags();
            $dataProvider = new ActiveDataProvider([
                'query' => $query
            ]);

            $widget = $this->renderFile($this->getLayout(), [
                'dataProvider' => $dataProvider,
                'viewFilesCounter' => $this->viewFilesCounter,
                'filesQuantity' => $query->count(),
                'pageSize' => $this->pageSize
            ]);

            if ($this->withTitle) {
                $widget = Html::tag('h2', AmosIcons::show('tag', [], 'dash') . BaseAmosModule::t('amoscore', '#tags_title')) . '<div class="col-xs-12">' . $widget . '</div>';
            }
        }

        return $widget;
    }

    /**
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    protected function getTags()
    {
        
        $userProfileClass = AmosAdmin::instance()->model('UserProfile');
        // $module = Yii::$app->getModule(AmosAdmin::getModuleName());
        // $userProfileClass = $module::getInstance()->model('UserProfile');

        if ($this->className == $userProfileClass) {
            $tagsMm = \lispa\amos\cwh\models\CwhTagOwnerInterestMm::find()
                ->innerJoin('tag', 'tag.id = tag_id')
                ->andWhere([
                    'record_id' => $this->userProfile,
                ])
                ->orderBy([
                    'tag.nome' => SORT_DESC
                ])->all();
        } else {
            $tagsMm = \lispa\amos\tag\models\EntitysTagsMm::find()
                ->joinWith('tag')
                ->andWhere([
                    'classname' => $this->className,
                    'record_id' => $this->userProfile,
                ])
                ->orderBy([
                    'tag.nome' => SORT_DESC
                ])->all();
        }

        $tagListId = [];
        foreach ($tagsMm as $elem) {
            $tagListId [] = $elem->tag_id;
        }

        $query = Tag::find()->andWhere(['id' => $tagListId]);
        if (is_array($this->rootIdsArray) && !empty($this->rootIdsArray)) {
            $query->andWhere(['root' => $this->rootIdsArray]);
        }

        return $query;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return mixed
     */
    public function getUserProfile()
    {
        return $this->userProfile;
    }

    /**
     * @param mixed $userProfile
     */
    public function setUserProfile($userProfile)
    {
        $this->userProfile = $userProfile;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param mixed $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }
}
