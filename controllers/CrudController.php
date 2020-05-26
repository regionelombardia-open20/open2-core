<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\controllers
 * @category   CategoryName
 */

namespace open20\amos\core\controllers;

use open20\amos\core\forms\EmailForm;
use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\interfaces\ModelLabelsInterface;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\user\User;
use open20\amos\core\utilities\Email;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;


/**
 * Class CrudController
 *
 * @property \open20\amos\core\record\Record $model
 * @property \open20\amos\core\record\Record $modelSearch
 *
 * @package open20\amos\core\controllers
 */
abstract class CrudController extends BaseController
{
    const BEFORE_FINDMODEL_EVENT = "beforeFindModel";
    const AFTER_FINDMODEL_EVENT = "afterFindModel";

    public $otherViewAvailable = false;

    public $dataProvider;
    public $gridViewColumns = null;
    public $modelSearch;
    public $currentView;
    public $availableViews;
    public $url;
    public $parametro;

    /** 
     * Used by direct community invitation 
     * 
     * @var type 
     */
    public 
        $moduleName = null,
        $contextModelId = null
    ;

    /**
     * @var array $exportConfig Configurations to export data. DON'T SET IN Yii::$app->request->queryParams!
     */
    public $exportConfig;

    /**
     * @var array $viewGrid
     */
    public $viewGrid;

    /**
     * @var array $viewList
     */
    public $viewList;

    /**
     * @var array $viewIcon
     */
    public $viewIcon;

    /**
     * @var bool $forceDefaultViewType
     */
    public $forceDefaultViewType = false;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!isset($this->modelSearch)) {
            throw new InvalidConfigException("{modelSearch} must be set in your init function");
        }
        if (!isset($this->availableViews)) {
            throw new InvalidConfigException("{availableViews}: gridView,listView,mapView,calendarView.. must be set");
        }

        $this->moduleName = Yii::$app->request->get('moduleName');
        $this->contextModelId = Yii::$app->request->get('contextModelId');

        $this->initCurrentView();

        $this->initAvailableViews();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['request-information'],
                        'allow' => true,
                        'roles' => ['@']
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'request-information' => ['post', 'get']
                ]
            ]
        ]);

        //pr(array_keys($behaviors));die;

        return $behaviors;
    }

    public function initCurrentView()
    {
        $currentView = $this->getDefaultCurrentView($this->getModelClassName());
        $this->setCurrentView($currentView);

        if ($currentViewName = Yii::$app->request->getQueryParam('currentView')) {
            $views = array_keys($this->getAvailableViews());
            $viewToSet = (in_array($currentViewName, $views) ? $currentViewName : $currentView['name']);
            $this->setCurrentView($this->getAvailableView($viewToSet));
        }
    }

    protected function getDefaultCurrentView($modelClass)
    {
        $this->initAvailableViews();
        $views = array_keys($this->getAvailableViews());
        if ($this->forceDefaultViewType) {
            $defaultView = $views[0];
        } else {
            $defaultView = (in_array('icon', $views) ? 'icon' : $views[0]);
        }
        return $this->getAvailableView($defaultView);
    }

    public function initAvailableViews()
    {
        if (!$this->getAvailableViews()) {
            $this->setAvailableViews([
                'grid' => [
                    'name' => 'grid',
                    'label' => Yii::t('amoscore', '{iconaTabella}' . Html::tag('p', Yii::t('amoscore', 'Table')), [
                        'iconaTabella' => AmosIcons::show('view-list-alt')
                    ]),
                    'url' => '?currentView=grid'
                ],
            ]);
        }
    }

    /**
     * @return mixed
     */
    public function getAvailableViews()
    {
        return $this->availableViews;
    }

    /**
     * @param mixed $availableViews
     */
    public function setAvailableViews($availableViews)
    {
        $this->availableViews = $availableViews;
    }

    public function getAvailableView($name)
    {
        return $this->getAvailableViews()[$name];
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getParametro()
    {
        return $this->parametro;
    }

    public function setParametro($parametro)
    {
        $this->parametro = $parametro;
    }

    public function can($strPermission)
    {
        return (Yii::$app->user->can(strtoupper($this->getModelName() . '_' . $strPermission))
            ||
            Yii::$app->user->can(get_class($this->getModel()) . '_' . strtoupper($strPermission))
        );
    }

    /**
     * Finds the ModelClass model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return string the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionIndex($layout = null)
    {
        $this->setUpLayout('list');

        //se il layout di default non dovesse andar bene si può aggiuntere il layout desiderato
        //in questo modo nel controller "return parent::actionIndex($this->layout);"
        if ($layout) {
            $this->setUpLayout($layout);
        }
        
        return $this->render(
            'index', 
            [
                'dataProvider' => $this->getDataProvider(),
                'model' => $this->getModelSearch(),
                'currentView' => $this->getCurrentView(),
                'availableViews' => $this->getAvailableViews(),
                'url' => ($this->url) ? $this->url : null,
                'parametro' => ($this->parametro) ? $this->parametro : null,
                'moduleName' => ($this->moduleName) ? $this->moduleName : null,
                'contextModelId' => ($this->contextModelId) ? $this->contextModelId : null,
            ]
        );
    }

    /**
     * @return mixed
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * @param mixed $dataProvider
     */
    public function setDataProvider(ActiveDataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @return mixed
     */
    public function getModelSearch()
    {
        return $this->modelSearch;
    }

    /**
     * @param mixed $modelSearch
     */
    public function setModelSearch($modelSearch)
    {
        $this->modelSearch = $modelSearch;
    }

    /**
     * @return string
     */
    public function getCurrentView()
    {
        return $this->currentView;
    }

    /**
     * @param string $currentView
     */
    public function setCurrentView($currentView)
    {
        $this->currentView = $currentView;
    }

    /**
     * Finds the modelObj model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return string the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        /** @var \open20\amos\core\record\Record $model */
        $model = null;
        $modelObj = $this->getModelObj();

        $modelObj->id = $id;
        $modelObj->trigger(self::BEFORE_FINDMODEL_EVENT);
        if (($model = $modelObj->findOne($id)) === null) {
            throw new NotFoundHttpException(BaseAmosModule::t('amoscore', 'The requested page does not exist.'));
        }
        $this->setModelObj($model);
        $model->trigger(self::AFTER_FINDMODEL_EVENT);
        return $model;
    }

    /**
     * @return array
     */
    public function getGridViewColumns()
    {
        return $this->gridViewColumns;
    }

    /**
     * @param array $gridViewColumns
     */
    public function setGridViewColumns($gridViewColumns)
    {
        $this->gridViewColumns = $gridViewColumns;
    }

    /**
     * Used by information request widget to send email about a model (from modal)
     * @param integer $id - the model id
     * @return bool|string
     */
    public function actionRequestInformation($id)
    {
        $this->model = $this->findModel($id);
        $view = '@vendor/open20/amos-core/forms/views/information_request';
        $infoRequest = new EmailForm();
        $this->layout = false;
        if (Yii::$app->getRequest()->isAjax && Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($infoRequest->load($post) && $infoRequest->validate($post)) {
                $fromUser = User::findOne(Yii::$app->user->id);
                if (!is_null($fromUser)) {
                    $fromMail = $fromUser->email;
                    $fromName = $fromUser->getProfile()->getNomeCognome();
                    if (!empty($infoRequest->userIdTo)) {
                        //user Id of mail recipient
                        $to = User::findOne($infoRequest->userIdTo)->email;
                    } elseif (isset($infoRequest->attributeTo) && $this->model->hasAttribute($infoRequest->attributeTo)) {
                        //model attribute directly specifying recipient email
                        $to = $this->model->{$infoRequest->attributeTo};
                    } else {
                        //information request sent to model creator
                        $to = User::findOne($this->model->created_by)->email;
                    }
                    $tos = [$to];
                    if (!empty($infoRequest->subject)) {
                        $subject = $infoRequest->subject;
                    } else {
                        $subject = BaseAmosModule::t('amoscore', '#info_request_subject');
                        if ($this->model instanceof ModelLabelsInterface) {
                            $subject .= $this->model->getGrammar()->getArticleSingular() . ' ' . $this->model->getGrammar()->getModelSingularLabel() . ' ';
                        }
                        $subject .= '"' . $this->model->getTitle() . '"';
                    }
                    $templatePath = !empty($infoRequest->templatePath) ? $infoRequest->templatePath : '@vendor/open20/amos-core/views/email/request-information';
                    $text = $this->renderMailPartial($templatePath,
                        ['message' => $infoRequest->message, 'email' => $fromMail, 'nameUser' => $fromName]);
                    if (isset(Yii::$app->params['email-assistenza'])) {
                        //use default platform email assistance
                        $from = Yii::$app->params['email-assistenza'];
                    } else {
                        $from = null;
                    }
                    $sent = Email::sendMail($from, $tos, $subject, $text, [], [], [], 0, false);
                    if ($sent) {
                        return BaseAmosModule::t('amoscore', '#info_request_success');
                    }
                }
                return BaseAmosModule::t('amoscore', '#info_request_error');
            }
        }
        return $this->renderAjax($view, ['infoRequest' => $infoRequest, 'model' => $this->model]);
    }
}
