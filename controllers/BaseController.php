<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\controllers
 * @category   CategoryName
 */

namespace lispa\amos\core\controllers;

use lispa\amos\core\module\BaseAmosModule;
use ReflectionClass;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\log\Logger;
use yii\web\ForbiddenHttpException;

/**
 * Class BaseController
 * @package lispa\amos\core\controllers
 */
abstract class BaseController extends BackendController
{
    private $modelObj;
    private $modelClass;
    private $modelClassName;
    private $modelName;
    private $actionsPermissions = array(
        'index' => 'read',
        'view' => 'read',
        'create' => 'create',
        'create-ajax' => 'create',
        'delete' => 'delete',
        'update' => 'update',
    );

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->modelObj;
    }

    /**
     * @param mixed $modelObj
     */
    public function setModel($modelObj)
    {
        $this->modelObj = $modelObj;
    }

    /**
     * @return mixed
     */
    public function getModelObj()
    {
        return $this->modelObj;
    }

    /**
     * @param mixed $modelObj
     */
    public function setModelObj($modelObj)
    {
        $this->modelObj = $modelObj;
    }

    /**
     * @return mixed
     */
    public function getModelClassName()
    {
        return $this->modelClassName;
    }

    /**
     * @param mixed $modelClassName
     */
    public function setModelClassName($modelClassName)
    {
        $this->modelClassName = $modelClassName;
    }

    public function initModelName()
    {
        $refClass = new ReflectionClass($this->getModelObj());
        $this->setModelClassName($refClass->getName());
        $this->setModelName($refClass->getShortName());
    }

    /**
     * @return mixed
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @param mixed $modelName
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    /**
     * @return Model
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @param Model $modelClass
     */
    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (!isset($this->modelObj)) {
            throw new InvalidConfigException("{modelObj} must be set in your init function");
        }
        $this->initModelName();
        $this->setUpLayout();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $rules = [];
        if ($this->module != null && $this->module instanceof BaseAmosModule) {
            if ($this->module->rbacEnabled === false) {
                $rules = [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ];
            }
        }

        $rules = array_merge($rules, $this->getRules());

        $behaviors = \yii\helpers\ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => $rules,
                'denyCallback' => function ($rule, $action) {
                    if (Yii::$app->getUser()->isGuest) {
                        $this->addFlash('warning', BaseAmosModule::t('amoscore', 'La sessione è scaduta, effettua il login'));
                        return Yii::$app->getUser()->loginRequired();
                    }
                    throw new ForbiddenHttpException(Yii::t('amoscore',
                        'Non sei autorizzato a visualizzare questa pagina'));
                }
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post']
                ]
            ]
        ]);

        return $behaviors;
    }

    /**
     * @return array
     */
    protected function getRules()
    {
        $rules = $params = [];

        try {
            if (isset($this->modelObj)) {
                $params = [
                    'model' => $this->modelObj
                ];
            }

            foreach ($this->actionsPermissions as $act => $perm) {
                if (Yii::$app->user->can(strtoupper($this->modelName . '_' . $perm), $params) ||
                    Yii::$app->user->can(get_class($this->modelObj) . '_' . strtoupper($perm), $params)
                ) {
                    $rules[] = [
                        'actions' => [$act],
                        'allow' => true,
                        'roles' => ['@']
                    ];
                }
            }
        } catch (\Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }

        return $rules;
    }


}
