<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\editors\assets\m2mWidget\controllers
 * @category   CategoryName
 */

namespace open20\amos\core\forms\editors\m2mWidget\controllers;

use open20\amos\core\forms\editors\m2mWidget\M2MEventsEnum;
use Yii;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class M2MWidgetControllerTrait
 * @package open20\amos\core\forms\editors\m2mWidget\controllers
 */
trait M2MWidgetControllerTrait
{
    /**
     * @var string $mmTableName
     */
    private $mmTableName = '';

    /**
     * @var string $startObjClassName
     */
    private $startObjClassName = '';

    /**
     * @var string $mmStartKey
     */
    private $mmStartKey = '';

    /**
     * @var string $targetObjClassName
     */
    private $targetObjClassName = '';

    /**
     * @var string $mmTargetKey
     */
    private $mmTargetKey = '';

    /**
     * @var string $redirectAction
     */
    private $redirectAction = '';

    /**
     * @var array|string $redirectArray
     */
    private $redirectArray = null;

    /**
     * @var array $options
     */
    private $options = [];

    /**
     * @var string $targetUrl
     */
    private $targetUrl = '';

    /**
     * @var string $targetUrlInvitation
     */
    private $targetUrlInvitation = '';
    
    /**
     * @var bool $externalInvitationEnabled If true enable the invitation of external users.
     */
    public $externalInvitationEnabled = false;
    
    /**
     * @var array $targetUrlParams
     */
    private $targetUrlParams = null;

    /**
     * @var string $additionalTargetUrl
     */
    private $additionalTargetUrl = '';

    /**
     * @var string $moduleClassName
     */
    private $moduleClassName = '';

    /**
     * @var string $m2mAttributesManageViewPath
     */
    private $m2mAttributesManageViewPath = '';

    /**
     * Array of other mm table fields and relative default values
     * @var array $mmTableAttributesDefault
     */
    private $mmTableAttributesDefault = [];

    /**
     * @var bool $customQuery - true if query to view target record to insert in mmtable is not a standard search but a personalized query
     */
    private $customQuery = false;

    /**
     * @var bool $viewM2MWidgetGenericSearch
     */
    private $viewM2MWidgetGenericSearch = false;

    /**
     * Array of other mm table fields and values to search in the intercect query
     * @var array $mmTableAdditionalAttributesToSearch
     */
    private $mmTableAdditionalAttributesToSearch = [];

    /**
     * @param int $id
     * @return string
     */
    public function actionAssociateOneToMany($id)
    {
        $this->trigger(M2MEventsEnum::EVENT_BEFORE_ASSOCIATE_ONE2MANY);

        /** @var ActiveRecord $model */
        $startObj = Yii::createObject($this->startObjClassName);
        $model = $startObj->findOne($id);
        $targetKey = $this->mmTargetKey;

        /** @var ActiveRecord $targetObjClassName */
        $targetObjClassName = $this->targetObjClassName;

        if (Yii::$app->request->getIsPost()) {
            $post = Yii::$app->request->post();
            $model->load($post);
            $save = isset($post['save']) ? ($post['save']) : true;
            if (isset($post['selected']) && $save) {
                $countSelection = count($post['selected']);
                if ($countSelection == 1) {
                    /** @var ActiveRecord $target */
                    $target = $targetObjClassName::findOne(['id' => $post['selected'][0]]);
                    if (!is_null($target)) {
                        $model->$targetKey = $target->id;
                        $model->save(false);

                        // Force Auto confirm for facilitator enabled?
                        if ((isset(\Yii::$app->params['forceAutoConfirmForFacilitator']))
                            && (\Yii::$app->params['forceAutoConfirmForFacilitator'] == true)) {
                            \Yii::$app->runAction('/admin/user-contact/connect', [
                                'contactId' => $id,
                                'userId' => $target->user_id,
                                'accept' => 1
                            ]);
                        }

                    }
                }
            }
            
            $event = new Event;
            $event->sender = $model;
            $this->trigger(M2MEventsEnum::EVENT_AFTER_ASSOCIATE_ONE2MANY, $event);

            if (!Yii::$app->getRequest()->getIsAjax() && (!isset($post['fromGenericSearch']) || (isset($post['fromGenericSearch']) && !$post['fromGenericSearch']))) {
                $this->redirect($this->getRedirectArray($id));
            }
        }

        $get = Yii::$app->getRequest()->get();
        if (isset($get['viewM2MWidgetGenericSearch'])) {
            $this->setViewM2MWidgetGenericSearch(true);
        }

        $this->trigger(M2MEventsEnum::EVENT_BEFORE_RENDER_ASSOCIATE_ONE2MANY);

        $renderOptions = ['model' => $model, 'viewM2MWidgetGenericSearch' => $this->getViewM2MWidgetGenericSearch()];
        if (Yii::$app->getRequest()->getIsAjax()) {
            return $this->renderAjax($this->targetUrl, $renderOptions);
        } else {
            return $this->render($this->targetUrl, $renderOptions);
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionAssociaM2m($id)
    {
        $this->trigger(M2MEventsEnum::EVENT_BEFORE_ASSOCIATE_M2M);

        /** @var ActiveRecord $targetObjClassName */
        $targetObjClassName = $this->targetObjClassName;
        $startKey = $this->mmStartKey;
        $targetKey = $this->mmTargetKey;
        /** @var ActiveRecord $mmTableName */
        $mmTableName = $this->mmTableName;

        /** @var ActiveRecord $model */
        $startObj = Yii::createObject($this->startObjClassName);
        $model = $startObj->findOne($id);

        $event = new Event();
        $event->sender = [
            'startObj' => $model
        ];
        $this->trigger(M2MEventsEnum::EVENT_AFTER_FIND_START_OBJ_M2M, $event);

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->load($post);
            $notInTargets = [];
            if (isset($post['selected'])) {
                $save = isset($post['save']) ? ($post['save']) : true;
                if ($save) {
                    for ($index = 0; $index < count($post['selected']); $index++) {
                        /** @var ActiveRecord $target */
                        $target = $targetObjClassName::findOne(['id' => Yii::$app->request->post()['selected'][$index]]);
                        if (!is_null($target)) {
                            /**
                             * Vedere se è possibile fare una sola query che cerca tutti gli oggetti e li ritorna in un unico array
                             * di oggetti così si risparmia il tempo di fare una query ad ogni ciclo. Dev'essere ritornato per forza
                             * l'oggetto perché può servire a chi intercetta l'evento.
                             * Vedere se è facile sostituire il for col foreach che così a occhio sembra inutile.
                             * Sistemare la cosa del post selected usato 200 volte a caso.
                             */
                            $intercect = $this->getAssociaM2mIntercect($model->id, $target->id);
                            if (is_null($intercect)) {
                                $event = new Event();
                                $event->sender = [
                                    'startObj' => $model,
                                    'targetObj' => $target
                                ];
                                $this->trigger(M2MEventsEnum::EVENT_BEFORE_INTERCECT_M2M, $event);
                                /** @var ActiveRecord $intercect */
                                $intercect = new $mmTableName();
                                $intercect->$startKey = $model->id;
                                $intercect->$targetKey = $target->id;
                                if (isset($this->mmTableAttributesDefault)) {
                                    foreach ($this->mmTableAttributesDefault as $field => $value) {
                                        $intercect->$field = $value;
                                    }
                                }
                                $intercect->save(false);
                                $event->sender['intercect'] = $intercect;
                                $this->trigger(M2MEventsEnum::EVENT_AFTER_INTERCECT_M2M, $event);
                            }
                            $notInTargets[] = $target->id;
                        }
                    }
                }
            }
            if (!$this->isCustomQuery()) {
                $targets = $mmTableName::find()->andWhere([$startKey => $id])
                    ->andWhere([$this->mmStartKey => $model->id])
                    ->andWhere(['not in', $this->mmTargetKey, $notInTargets])->all();
                foreach ($targets as $singleTarget) {
                    $singleTarget->delete();
                }
            }

            $event = new Event();
            $event->sender = [
                'notInTargets' => $notInTargets
            ];
            $this->trigger(M2MEventsEnum::EVENT_AFTER_ASSOCIATE_M2M, $event);

            $post = Yii::$app->getRequest()->post();
            if (!Yii::$app->getRequest()->getIsAjax() && (!isset($post['fromGenericSearch']) || (isset($post['fromGenericSearch']) && !$post['fromGenericSearch']))) {
                $this->redirect($this->getRedirectArray($id));
            }
        }

        $get = Yii::$app->getRequest()->get();
        if (isset($get['viewM2MWidgetGenericSearch'])) {
            $this->setViewM2MWidgetGenericSearch(true);
        }

        $renderOptions = ['model' => $model, 'viewM2MWidgetGenericSearch' => $this->getViewM2MWidgetGenericSearch()];
        if (Yii::$app->getRequest()->getIsAjax()) {
            $this->layout = false;

            return $this->renderAjax($this->targetUrl, $renderOptions);
        } else {
            return $this->render($this->targetUrl, $renderOptions);
        }
    }

    /**
     * @param int $startId
     * @param int $targetId
     * @return array|ActiveRecord|null
     */
    protected function getAssociaM2mIntercect($startId, $targetId)
    {
        /** @var ActiveRecord $mmTableName */
        $mmTableName = $this->mmTableName;
        /** @var ActiveQuery $query */
        $query = $mmTableName::find();
        $query->andWhere([$this->mmStartKey => $startId])
            ->andWhere([$this->mmTargetKey => $targetId]);
        if (is_array($this->mmTableAdditionalAttributesToSearch) && !empty($this->mmTableAdditionalAttributesToSearch)) {
            foreach ($this->mmTableAdditionalAttributesToSearch as $field => $value) {
                $query->andWhere([$field => $value]);
            }
        }
        $intercect = $query->one();
        return $intercect;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function actionAdditionalAssociateM2m($id)
    {
        $this->trigger(M2MEventsEnum::EVENT_BEFORE_ASSOCIATE_M2M);

        /** @var ActiveRecord $targetObjClassName */
        $targetObjClassName = $this->targetObjClassName;
        $startKey = $this->mmStartKey;
        $targetKey = $this->mmTargetKey;
        /** @var ActiveRecord $mmTableName */
        $mmTableName = $this->mmTableName;

        /** @var ActiveRecord $model */
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $notInTargets = [];
            if (isset(Yii::$app->request->post()['selected'])) {
                for ($index = 0; $index < count(Yii::$app->request->post()['selected']); $index++) {
                    /** @var ActiveRecord $target */
                    $target = $targetObjClassName::findOne(['id' => Yii::$app->request->post()['selected'][$index]]);
                    if (!is_null($target)) {
                        $intercect = $mmTableName::find()->andWhere([$this->mmStartKey => $model->id])
                            ->andWhere([$this->mmTargetKey => $target->id])->one();
                        if (is_null($intercect)) {
                            /** @var ActiveRecord $intercect */
                            $intercect = new $mmTableName();
                            $intercect->$startKey = $model->id;
                            $intercect->$targetKey = $target->id;
                            if (isset($this->mmTableAttributesDefault)) {
                                foreach ($this->mmTableAttributesDefault as $field => $value) {
                                    $intercect->$field = $value;
                                }
                            }
                            $intercect->save(false);
                        }
                        $notInTargets[] = $target->id;
                    }
                }
            }
            if (!$this->isCustomQuery()) {
                $targets = $mmTableName::find()->andWhere([$startKey => $id])
                    ->andWhere([$this->mmStartKey => $model->id])
                    ->andWhere(['not in', $this->mmTargetKey, $notInTargets])->all();
                foreach ($targets as $singleTarget) {
                    $singleTarget->delete();
                }
            }

            $this->trigger(M2MEventsEnum::EVENT_AFTER_ASSOCIATE_M2M);

            $this->redirect($this->getRedirectArray($id));
        }
        if (Yii::$app->getRequest()->getIsAjax()) {
            $this->layout = false;

            return $this->renderAjax($this->additionalTargetUrl, ['model' => $model]);
        }
        return $this->render($this->additionalTargetUrl, ['model' => $model]);
    }

    /**
     * @param $id
     * @return array
     */
    protected function getRedirectArray($id = null)
    {
        if (!is_null($this->redirectArray)) {
            return $this->redirectArray;
        }
        $redirectArray = [
            $this->redirectAction,
            'id' => $id
        ];
        if (!empty($this->options)) {
            $redirectArray = ArrayHelper::merge($redirectArray, $this->options);
        }
        return $redirectArray;
    }

    /**
     * @param array|string $redirectArray
     */
    public function setRedirectArray($redirectArray)
    {
        $this->redirectArray = $redirectArray;
    }

    /**
     * @param int $id
     */
    public function actionAnnullaM2m($id)
    {
        $this->trigger(M2MEventsEnum::EVENT_BEFORE_CANCEL_ASSOCIATE_M2M);
        return $this->redirect($this->getRedirectArray($id));
    }

    /**
     * @param $id
     * @param $targetId
     */
    public function actionEliminaM2m($id, $targetId)
    {
        $this->trigger(M2MEventsEnum::EVENT_BEFORE_DELETE_M2M);

        $mmTableClassName = "\\" . $this->mmTableName;

        /** @var ActiveRecord $model */
        $model = $this->findModel($id);
        if ($model) {
            /** @var ActiveRecord $target */
            $target = $mmTableClassName::findOne([$this->mmStartKey => $id, $this->mmTargetKey => $targetId]);
            if ($target) {
                $target->delete();

                $this->trigger(M2MEventsEnum::EVENT_AFTER_DELETE_M2M);

                $this->redirect($this->getRedirectArray($id));
            }
        }
    }


    /**
     * @param $id
     * @param $targetId
     * @return mixed
     */
    public function actionManageM2mAttributes($id, $targetId)
    {
        $this->trigger(M2MEventsEnum::EVENT_BEFORE_MANAGE_ATTRIBUTES_M2M);
        /** @var ActiveRecord $model */
//        $model = $this->genericFindModel([$this->mmStartKey => $id, $this->mmTargetKey => $targetId]);
        $model = $this->genericFindModel(['id' => $targetId]);
//        $model = $this->findModel($targetId);

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($model->validate()) {
                $ok = $model->save();
                if ($ok) {
                    // TODO fargli fare qualcosa in caso di successo durante il salvataggio
                } else {
                    // TODO fargli fare qualcosa in caso di errore durante il salvataggio
                }
            } else {
                // TODO fargli fare qualcosa in caso di errore durante la validazione
            }
            $this->trigger(M2MEventsEnum::EVENT_AFTER_MANAGE_ATTRIBUTES_M2M);
            $this->redirect($this->getRedirectArray($id));
        }

        return $this->render($this->getM2mAttributesManageViewPath(), ['model' => $model]);
    }

    /**
     * @param int $ids
     * @return ActiveRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function genericFindModel($ids)
    {
        /** @var ActiveRecord $mmTableName */
        $mmTableClassName = "\\" . $this->mmTableName;
        /** @var ActiveRecord $mmTableClassName */
        if (($model = $mmTableClassName::findOne($ids)) !== null) {
            $this->model = $model;
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return string
     */
    public function getM2mAttributesManageViewPath()
    {
        return $this->m2mAttributesManageViewPath;
    }

    /**
     * @param string $m2mAttributesManageViewPath
     */
    public function setM2mAttributesManageViewPath($m2mAttributesManageViewPath)
    {
        $this->m2mAttributesManageViewPath = $m2mAttributesManageViewPath;
    }

    /**
     * @return null
     */
    public function getMmTableName()
    {
        return $this->mmTableName;
    }

    /**
     * @param null $mmTableName
     */
    public function setMmTableName($mmTableName)
    {
        $this->mmTableName = $mmTableName;
    }

    /**
     * @return string
     */
    public function getStartObjClassName()
    {
        return $this->startObjClassName;
    }

    /**
     * @param string $startObjClassName
     */
    public function setStartObjClassName($startObjClassName)
    {
        $this->startObjClassName = $startObjClassName;
    }

    /**
     * @return null
     */
    public function getMmStartKey()
    {
        return $this->mmStartKey;
    }

    /**
     * @param null $mmStartKey
     */
    public function setMmStartKey($mmStartKey)
    {
        $this->mmStartKey = $mmStartKey;
    }

    /**
     * @return string
     */
    public function getTargetObjClassName()
    {
        return $this->targetObjClassName;
    }

    /**
     * @param string $targetObjClassName
     */
    public function setTargetObjClassName($targetObjClassName)
    {
        $this->targetObjClassName = $targetObjClassName;
    }

    /**
     * @return null
     */
    public function getMmTargetKey()
    {
        return $this->mmTargetKey;
    }

    /**
     * @param null $mmTargetKey
     */
    public function setMmTargetKey($mmTargetKey)
    {
        $this->mmTargetKey = $mmTargetKey;
    }

    /**
     * @return null
     */
    public function getRedirectAction()
    {
        return $this->redirectAction;
    }

    /**
     * @param null $redirectAction
     */
    public function setRedirectAction($redirectAction)
    {
        $this->redirectAction = $redirectAction;
    }

    /**
     * @return null
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param null $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * @param string $targetUrl
     */
    public function setTargetUrl($targetUrl)
    {
        $this->targetUrl = $targetUrl;
    }

    /**
     * @return string
     */
    public function getModuleClassName()
    {
        return $this->moduleClassName;
    }

    /**
     * @param string $moduleClassName
     */
    public function setModuleClassName($moduleClassName)
    {
        $this->moduleClassName = $moduleClassName;
    }

    /**
     * @return array
     */
    public function getMmTableAttributesDefault()
    {
        return $this->mmTableAttributesDefault;
    }

    /**
     * @param array $mmTableAttributesDefault
     */
    public function setMmTableAttributesDefault($mmTableAttributesDefault)
    {
        $this->mmTableAttributesDefault = $mmTableAttributesDefault;
    }

    /**
     * @return boolean
     */
    public function isCustomQuery()
    {
        return $this->customQuery;
    }

    /**
     * @param boolean $customQuery
     */
    public function setCustomQuery($customQuery)
    {
        $this->customQuery = $customQuery;
    }

    /**
     * @return string
     */
    public function getAdditionalTargetUrl()
    {
        return $this->additionalTargetUrl;
    }

    /**
     * @param string $additionalTargetUrl
     */
    public function setAdditionalTargetUrl($additionalTargetUrl)
    {
        $this->additionalTargetUrl = $additionalTargetUrl;
    }

    /**
     * @return bool
     */
    public function getViewM2MWidgetGenericSearch()
    {
        return $this->viewM2MWidgetGenericSearch;
    }

    /**
     * @param bool $viewM2MWidgetGenericSearch
     */
    public function setViewM2MWidgetGenericSearch($viewM2MWidgetGenericSearch)
    {
        $this->viewM2MWidgetGenericSearch = $viewM2MWidgetGenericSearch;
    }

    /**
     * @return array
     */
    public function getTargetUrlParams()
    {
        return $this->targetUrlParams;
    }

    /**
     * @param array $targetUrlParams
     */
    public function setTargetUrlParams($targetUrlParams)
    {
        $this->targetUrlParams = $targetUrlParams;
    }
    
    /**
     * @return array
     */
    public function getTargetUrlInvitation()
    {
        return $this->targetUrlInvitation;
    }

    /**
     * @param array $targetUrlInvitation
     */
    public function setTargetUrlInvitation($targetUrlInvitation)
    {
        $this->targetUrlInvitation = $targetUrlInvitation;
    }

    /**
     * @return array
     */
    public function getInvitationModule()
    {
        return $this->invitationModule;
    }

    /**
     * @param array $targetUrlInvitation
     */
    public function setInvitationModule($invitationModule)
    {
        $this->invitationModule = $invitationModule;
    }

    /**
     * @return array
     */
    public function getMmTableAdditionalAttributesToSearch()
    {
        return $this->mmTableAdditionalAttributesToSearch;
    }

    /**
     * @param array $mmTableAdditionalAttributesToSearch
     */
    public function setMmTableAdditionalAttributesToSearch($mmTableAdditionalAttributesToSearch)
    {
        $this->mmTableAdditionalAttributesToSearch = $mmTableAdditionalAttributesToSearch;
    }
}
