<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\giiamos\crud\wizard
 * @category   CategoryName
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;
use yii\helpers\Inflector;

/**
 * This is the template for generating a CRUD controller class file.
 *
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/** @var ActiveRecordInterface $class */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();
$arrClassModel = explode('\\', $generator->modelClass);
$classModel = end($arrClassModel);
$ajaxController = FALSE;
foreach ((array) $generator->getMmRelations() as $Relation){
    if(Inflector::id2camel($Relation['toEntity'], '_') == $classModel):
        $ajaxController = TRUE;
        continue;
    endif;
}

echo "<?php\n";
?>
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) . '\base'?> 
 */

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) . '\base' ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
    use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
    use yii\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use lispa\amos\core\module\BaseAmosModule;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\helpers\T;
use yii\helpers\Url;


/**
 * Class <?= $controllerClass ?> 
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 *
 * @property \<?=$generator->modelClass?> $model
 * @property \<?=$generator->searchModelClass?> $modelSearch 
 *
 * @package <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) . '\base' ?> 
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{

/**
 * @var string $layout
 */
public $layout = 'main';

public function init() {
        $this->setModelObj(new <?= $modelClass ?>());
        $this->setModelSearch(new <?= $modelClass ?>Search());

        $this->setAvailableViews([
            'grid' => [
                'name' => 'grid',
                'label' => AmosIcons::show('view-list-alt') . Html::tag('p', BaseAmosModule::tHtml('amoscore', 'Table')),                
                'url' => '?currentView=grid'
            ],
            /*'list' => [
                'name' => 'list',
                'label' => AmosIcons::show('view-list') . Html::tag('p', BaseAmosModule::tHtml('amoscore', 'List')),         
                'url' => '?currentView=list'
            ],
            'icon' => [
                'name' => 'icon',
                'label' => AmosIcons::show('grid') . Html::tag('p', BaseAmosModule::tHtml('amoscore', 'Icons')),           
                'url' => '?currentView=icon'
            ],
            'map' => [
                'name' => 'map',
                'label' => AmosIcons::show('map') . Html::tag('p', BaseAmosModule::tHtml('amoscore', 'Map')),      
                'url' => '?currentView=map'
            ],
            'calendar' => [
                'name' => 'calendar',
                'intestazione' => '', //codice HTML per l'intestazione che verrà caricato prima del calendario,
                                      //per esempio si può inserire una funzione $model->getHtmlIntestazione() creata ad hoc
                'label' => AmosIcons::show('calendar') . Html::tag('p', BaseAmosModule::tHtml('amoscore', 'Calendari')),                                            
                'url' => '?currentView=calendar'
            ],*/
        ]);

        parent::init();
        $this->setUpLayout();
    }

/**
* Lists all <?= $modelClass ?> models.
* @return mixed
*/        
public function actionIndex($layout = NULL)
{    
        Url::remember();
        $this->setDataProvider($this->modelSearch->search(Yii::$app->request->getQueryParams()));
        return parent::actionIndex($layout);
}

/**
* Displays a single <?= $modelClass ?> model.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return mixed
*/
public function actionView(<?= $actionParams ?>)
{
$this->model = $this->findModel(<?= $actionParams ?>);

if ($this->model->load(Yii::$app->request->post()) && $this->model->save()) {
return $this->redirect(['view', 'id' => $this->model-><?= $generator->getTableSchema()->primaryKey[0] ?>]);
} else {
return $this->render('view', ['model' => $this->model]);
}
}

/**
* Creates a new <?= $modelClass ?> model.
* If creation is successful, the browser will be redirected to the 'view' page.
* @return mixed
*/
public function actionCreate()
{
$this->setUpLayout('form');
$this->model = new <?= $modelClass ?>();

if ($this->model->load(Yii::$app->request->post()) && $this->model->validate()) {
<?php 
$arrayAttributi = [];
$indx = [];
$elencoAttributi = [];
foreach ((array) $generator->getMmRelations() as $Relation):
    if ($Relation['type'] == 'mtm') :
        if (Inflector::id2camel($Relation['fromEntity'], '_') == $classModel):
            $attributoEntita = $Relation['toEntity'];
            if (!(in_array($attributoEntita, $arrayAttributi))):
                $arrayAttributi[] = $attributoEntita;
                $indx[$attributoEntita] = 0;
                $elencoAttributi[$attributoEntita]['fromEntity'] = $Relation['fromEntity'];
                $elencoAttributi[$attributoEntita]['toEntity'] = $Relation['toEntity'];
                $elencoAttributi[$attributoEntita]['type'] = $Relation['type'];
                $elencoAttributi[$attributoEntita]['index'] = '';
?>   
                $<?= Inflector::id2camel($attributoEntita, '_') ?>MmPost = \Yii::$app->request->post('<?= Inflector::id2camel($Relation['fromEntity'], '_') ?>')['<?= Inflector::id2camel($attributoEntita, '_') ?>'];
                <?php
            else:
                $indx[$attributoEntita] = $indx[$attributoEntita] + 1;
                $newIndx = $indx[$attributoEntita] - 1;
                $newAttributoEntita = $attributoEntita . $newIndx;
                $elencoAttributi[$newAttributoEntita]['fromEntity'] = $Relation['fromEntity'];
                $elencoAttributi[$newAttributoEntita]['toEntity'] = $Relation['toEntity'];
                $elencoAttributi[$newAttributoEntita]['type'] = $Relation['type'];
                $elencoAttributi[$newAttributoEntita]['index'] = "_$newIndx";
                ?>
                $<?= Inflector::id2camel($newAttributoEntita, '_') ?>MmPost = \Yii::$app->request->post('<?= Inflector::id2camel($Relation['fromEntity'], '_') ?>')['<?= Inflector::id2camel($newAttributoEntita, '_') ?>'];
            <?php
            endif;
        endif;
    endif;
endforeach;
            ?>
if($this->model->save()){
<?php foreach ((array) $elencoAttributi as $key => $Relation):
    ?>   
    <?php if ($Relation['type'] == 'mtm') { ?>
        if(!empty($<?= Inflector::id2camel($key, '_') ?>MmPost)){
        foreach((array) $<?= Inflector::id2camel($key, '_') ?>MmPost as $relazionato){                  
        $newRelazioneMm = new \backend\models\<?= Inflector::id2camel($Relation['fromEntity'] . '_' . $Relation['toEntity'] . '_mm' . $Relation['index'], '_') ?>();  
        $newRelazioneMm-><?= $Relation['fromEntity'] ?>_id = $model->id;
        $newRelazioneMm-><?= $Relation['toEntity'] ?>_id = $relazionato;
        $newRelazioneMm->save(false);
        }
        }
        <?php
    }

endforeach;
?>
Yii::$app->getSession()->addFlash('success', Yii::t('amoscore', 'Item created'));
return $this->redirect(['update', 'id' => $this->model->id]);
} else {
Yii::$app->getSession()->addFlash('danger', Yii::t('amoscore', 'Item not created, check data'));
}
} 

return $this->render('create', [
'model' => $this->model,
'fid' => NULL,
    'dataField' => NULL,
    'dataEntity' => NULL,
]);
}

/**
* Creates a new <?= $modelClass ?> model by ajax request.
* If creation is successful, the browser will be redirected to the 'view' page.
* @return mixed
*/
public function actionCreateAjax($fid, $dataField)
{
$this->setUpLayout('form');
$this->model = new <?= $modelClass ?>();

if (\Yii::$app->request->isAjax && $this->model->load(Yii::$app->request->post()) && $this->model->validate()) {
<?php
$arrayAttributi = [];
$indx = [];
$elencoAttributi = [];
foreach ((array) $generator->getMmRelations() as $Relation):
    if ($Relation['type'] == 'mtm') :
        if (Inflector::id2camel($Relation['fromEntity'], '_') == $classModel):
            $attributoEntita = $Relation['toEntity'];
            if (!(in_array($attributoEntita, $arrayAttributi))):
                $arrayAttributi[] = $attributoEntita;
                $indx[$attributoEntita] = 0;
                $elencoAttributi[$attributoEntita]['fromEntity'] = $Relation['fromEntity'];
                $elencoAttributi[$attributoEntita]['toEntity'] = $Relation['toEntity'];
                $elencoAttributi[$attributoEntita]['type'] = $Relation['type'];
                $elencoAttributi[$attributoEntita]['index'] = '';
                ?>   
                $<?= Inflector::id2camel($attributoEntita, '_') ?>MmPost = \Yii::$app->request->post('<?= Inflector::id2camel($Relation['fromEntity'], '_') ?>')['<?= Inflector::id2camel($attributoEntita, '_') ?>'];
                <?php
            else:
                $indx[$attributoEntita] = $indx[$attributoEntita] + 1;
                $newIndx = $indx[$attributoEntita] - 1;
                $newAttributoEntita = $attributoEntita . $newIndx;
                $elencoAttributi[$newAttributoEntita]['fromEntity'] = $Relation['fromEntity'];
                $elencoAttributi[$newAttributoEntita]['toEntity'] = $Relation['toEntity'];
                $elencoAttributi[$newAttributoEntita]['type'] = $Relation['type'];
                $elencoAttributi[$newAttributoEntita]['index'] = "_$newIndx";
                ?>
                $<?= Inflector::id2camel($newAttributoEntita, '_') ?>MmPost = \Yii::$app->request->post('<?= Inflector::id2camel($Relation['fromEntity'], '_') ?>')['<?= lcfirst(Inflector::id2camel($newAttributoEntita, '_')) ?>'];
            <?php
            endif;
        endif;
    endif;
endforeach;
?>
if($this->model->save()){
<?php foreach ((array) $elencoAttributi as $key => $Relation):        
        ?>   
    <?php if ($Relation['type'] == 'mtm') { ?>
        if(!empty($<?= Inflector::id2camel($key, '_') ?>MmPost)){
        foreach((array) $<?= Inflector::id2camel($key, '_') ?>MmPost as $relazionato){                  
        $newRelazioneMm = new \backend\models\<?= Inflector::id2camel($Relation['fromEntity'] . '_' . $Relation['toEntity'] . '_mm' . $Relation['index'], '_') ?>();  
        $newRelazioneMm-><?= $Relation['fromEntity'] ?>_id = $this->model->id;
        $newRelazioneMm-><?= $Relation['toEntity'] ?>_id = $relazionato;
        $newRelazioneMm->save(false);
        }
        }
        <?php
    }

endforeach;
?>
//Yii::$app->getSession()->addFlash('success', Yii::t('amoscore', 'Item created'));
return json_encode($this->model->toArray());
} else {
//Yii::$app->getSession()->addFlash('danger', Yii::t('amoscore', 'Item not created, check data'));
}
} 

return $this->renderAjax('_formAjax', [
'model' => $this->model,
'fid' => $fid,
'dataField' => $dataField
]);
}

/**
* Updates an existing <?= $modelClass ?> model.
* If update is successful, the browser will be redirected to the 'view' page.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return mixed
*/
public function actionUpdate(<?= $actionParams ?>)
{
$this->setUpLayout('form');
$this->model = $this->findModel(<?= $actionParams ?>);

if ($this->model->load(Yii::$app->request->post()) && $this->model->validate()) {
<?php foreach ((array) $elencoAttributi as $key => $Relation):        
        ?>   
$<?= Inflector::id2camel($key, '_') ?>MmPost = \Yii::$app->request->post('<?= Inflector::id2camel($Relation['fromEntity'], '_') ?>')['<?= Inflector::id2camel($key, '_') ?>'];
    <?php     
    endforeach; 
    ?>
if($this->model->save()){
<?php foreach ((array) $elencoAttributi as $key => $Relation):     
        ?>  
        \backend\models\<?= Inflector::id2camel($Relation['fromEntity'] . '_' . $Relation['toEntity'] . '_mm' . $Relation['index'], '_') ?>::deleteAll(['<?= $Relation['fromEntity'] ?>_id' => $id]);
        <?php if ($Relation['type'] == 'mtm') { ?>
        if(!empty($<?= Inflector::id2camel($key, '_') ?>MmPost)){
        foreach($<?= Inflector::id2camel($key, '_') ?>MmPost as $relazionato){                  
            $newRelazioneMm = new \backend\models\<?= Inflector::id2camel($Relation['fromEntity'] . '_' . $Relation['toEntity'] . '_mm' . $Relation['index'], '_') ?>();  
            $newRelazioneMm-><?= $Relation['fromEntity'] ?>_id = $id;
            $newRelazioneMm-><?= $Relation['toEntity'] ?>_id = $relazionato;
            $newRelazioneMm->save(false);
    }
    }
    <?php
        } 
    endforeach; 
    ?>
Yii::$app->getSession()->addFlash('success', Yii::t('amoscore', 'Item updated'));
return $this->redirect(['update', 'id' => $this->model->id]);
} else {
Yii::$app->getSession()->addFlash('danger', Yii::t('amoscore', 'Item not updated, check data'));
}
} 

return $this->render('update', [
'model' => $this->model,
'fid' => NULL,
    'dataField' => NULL,
    'dataEntity' => NULL,
]);
}

/**
* Deletes an existing <?= $modelClass ?> model.
* If deletion is successful, the browser will be redirected to the 'index' page.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return mixed
*/
public function actionDelete(<?= $actionParams ?>)
{
$this->model = $this->findModel(<?= $actionParams ?>);
if($this->model){
$this->model->delete();
if (!$this->model->hasErrors()) { 
Yii::$app->getSession()->addFlash('success', BaseAmosModule::t('amoscore', 'Element deleted successfully.'));
} else {
Yii::$app->getSession()->addFlash('danger', BaseAmosModule::t('amoscore', 'You are not authorized to delete this element.'));
}
} else {
Yii::$app->getSession()->addFlash('danger', BaseAmosModule::tHtml('amoscore', 'Element not found.'));
}
return $this->redirect(['index']);
}
}
