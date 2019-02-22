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
foreach ((array) $generator->mmRelations as $Relation){
    if(Inflector::id2camel($Relation['toEntity'], '_') == $classModel):
        $ajaxController = TRUE;
        continue;
    endif;
}

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) . '\base' ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
    use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
    use yii\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\helpers\T;
use yii\helpers\Url;

/**
* <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
*/
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
public function init() {
        $this->setModelObj(new <?= $modelClass ?>());
        $this->setModelSearch(new <?= $modelClass ?>Search());

        $this->setAvailableViews([
            'grid' => [
                'name' => 'grid',
                'label' => Yii::t('amoscore', '{iconaTabella}'.Html::tag('p',Yii::t('amoscore', 'Table')), [
                    'iconaTabella' => AmosIcons::show('view-list-alt')
                ]),                
                'url' => '?currentView=grid'
            ],
            /*'list' => [
                'name' => 'list',
                'label' => Yii::t('amoscore', '{iconaLista}'.Html::tag('p',Yii::t('amoscore', 'List')), [
                    'iconaLista' => AmosIcons::show('view-list')
                ]),           
                'url' => '?currentView=list'
            ],
            'icon' => [
                'name' => 'icon',
                'label' => Yii::t('amoscore', '{iconaElenco}'.Html::tag('p',Yii::t('amoscore', 'Icons')), [
                    'iconaElenco' => AmosIcons::show('grid')
                ]),           
                'url' => '?currentView=icon'
            ],
            'map' => [
                'name' => 'map',
                'label' => Yii::t('amoscore', '{iconaMappa}'.Html::tag('p',Yii::t('amoscore', 'Map')), [
                    'iconaMappa' => AmosIcons::show('map')
                ]),       
                'url' => '?currentView=map'
            ],
            'calendar' => [
                'name' => 'calendar',
                'intestazione' => '', //codice HTML per l'intestazione che verrà caricato prima del calendario,
                                      //per esempio si può inserire una funzione $model->getHtmlIntestazione() creata ad hoc
                'label' => Yii::t('amoscore', '{iconaCalendario}'.Html::tag('p',Yii::t('amoscore', 'Calendari')), [
                    'iconaMappa' => AmosIcons::show('calendar')
                ]),       
                'url' => '?currentView=calendar'
            ],*/
        ]);

        parent::init();
    }

/**
* Lists all <?= $modelClass ?> models.
* @return mixed
*/        
public function actionIndex($layout = NULL)
{    
        Url::remember();
        $this->setDataProvider($this->getModelSearch()->search(Yii::$app->request->getQueryParams()));
        return parent::actionIndex();
}

/**
* Displays a single <?= $modelClass ?> model.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return mixed
*/
public function actionView(<?= $actionParams ?>)
{
$model = $this->findModel(<?= $actionParams ?>);

if ($model->load(Yii::$app->request->post()) && $model->save()) {
return $this->redirect(['view', 'id' => $model-><?= $generator->getTableSchema()->primaryKey[0] ?>]);
} else {
return $this->render('view', ['model' => $model]);
}
}

/**
* Creates a new <?= $modelClass ?> model.
* If creation is successful, the browser will be redirected to the 'view' page.
* @return mixed
*/
public function actionCreate()
{
$this->layout = "@vendor/lispa/amos-core/views/layouts/form";
$model = new <?= $modelClass ?>;

if ($model->load(Yii::$app->request->post()) && $model->validate()) {
<?php 
$arrayAttributi = [];
$indx = [];
$elencoAttributi = [];
foreach ((array) $generator->mmRelations as $Relation): 
        if(Inflector::id2camel($Relation['fromEntity'], '_') == $classModel):
            $attributoEntita = $Relation['toEntity'];                        
            if (!(in_array($attributoEntita, $arrayAttributi))):            
            $arrayAttributi[] = $attributoEntita;
            $indx[$attributoEntita] = 0;
            $elencoAttributi[$attributoEntita]['fromEntity'] = $Relation['fromEntity'];            
            $elencoAttributi[$attributoEntita]['toEntity'] = $Relation['toEntity']; 
            $elencoAttributi[$attributoEntita]['type'] = $Relation['type'];
            $elencoAttributi[$attributoEntita]['index'] = '';
        ?>   
$<?= Inflector::id2camel($attributoEntita, '_') ?>MmPost = \Yii::$app->request->post('<?= Inflector::id2camel($Relation['fromEntity'], '_') ?>')['attr<?= Inflector::id2camel($attributoEntita, '_') ?>Mm'];
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
$<?= Inflector::id2camel($newAttributoEntita, '_') ?>MmPost = \Yii::$app->request->post('<?= Inflector::id2camel($Relation['fromEntity'], '_') ?>')['attr<?= Inflector::id2camel($newAttributoEntita, '_') ?>Mm'];
<?php
endif;
        endif;
    endforeach; 
    ?>
if($model->save()){
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
} else { ?>
    if(!empty($<?= Inflector::id2camel($key, '_') ?>MmPost)){
    $newRelazioneMm = new \backend\models\<?= Inflector::id2camel($Relation['fromEntity'] . '_' . $Relation['toEntity'] . '_mm' . $Relation['index'], '_') ?>();  
            $newRelazioneMm-><?= $Relation['fromEntity'] ?>_id = $model->id;
            $newRelazioneMm-><?= $Relation['toEntity'] ?>_id = $<?= Inflector::id2camel($key, '_') ?>MmPost;
            $newRelazioneMm->save(false);
            }
    <?php
}
   
    endforeach; 
    ?>
Yii::$app->getSession()->addFlash('success', Yii::t('amoscore', 'Item created'));
return $this->redirect(['index']);
} else {
Yii::$app->getSession()->addFlash('danger', Yii::t('amoscore', 'Item not created, check data'));
return $this->render('create', [
'model' => $model,
'fid' => NULL,
    'dataField' => NULL,
    'dataEntity' => NULL,
]);
}
} else {
return $this->render('create', [
'model' => $model,
'fid' => NULL,
    'dataField' => NULL,
    'dataEntity' => NULL,
]);
}
}

/**
* Creates a new <?= $modelClass ?> model by ajax request.
* If creation is successful, the browser will be redirected to the 'view' page.
* @return mixed
*/
public function actionCreateAjax($fid, $dataField)
{
$this->layout = "@vendor/lispa/amos-core/views/layouts/form";
$model = new <?= $modelClass ?>;

if (\Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()) && $model->validate()) {
<?php 
$arrayAttributi = [];
$indx = [];
$elencoAttributi = [];
foreach ((array) $generator->mmRelations as $Relation): 
        if(Inflector::id2camel($Relation['fromEntity'], '_') == $classModel):
            $attributoEntita = $Relation['toEntity'];                        
            if (!(in_array($attributoEntita, $arrayAttributi))):            
            $arrayAttributi[] = $attributoEntita;
            $indx[$attributoEntita] = 0;
            $elencoAttributi[$attributoEntita]['fromEntity'] = $Relation['fromEntity'];            
            $elencoAttributi[$attributoEntita]['toEntity'] = $Relation['toEntity']; 
            $elencoAttributi[$attributoEntita]['type'] = $Relation['type'];
            $elencoAttributi[$attributoEntita]['index'] = '';
        ?>   
$<?= Inflector::id2camel($attributoEntita, '_') ?>MmPost = \Yii::$app->request->post('<?= Inflector::id2camel($Relation['fromEntity'], '_') ?>')['attr<?= Inflector::id2camel($attributoEntita, '_') ?>Mm'];
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
$<?= Inflector::id2camel($newAttributoEntita, '_') ?>MmPost = \Yii::$app->request->post('<?= Inflector::id2camel($Relation['fromEntity'], '_') ?>')['attr<?= Inflector::id2camel($newAttributoEntita, '_') ?>Mm'];
<?php
endif;
        endif;
    endforeach; 
    ?>
if($model->save()){
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
} else { ?>
    if(!empty($<?= Inflector::id2camel($key, '_') ?>MmPost)){
    $newRelazioneMm = new \backend\models\<?= Inflector::id2camel($Relation['fromEntity'] . '_' . $Relation['toEntity'] . '_mm' . $Relation['index'], '_') ?>();  
            $newRelazioneMm-><?= $Relation['fromEntity'] ?>_id = $model->id;
            $newRelazioneMm-><?= $Relation['toEntity'] ?>_id = $<?= Inflector::id2camel($key, '_') ?>MmPost;
            $newRelazioneMm->save(false);
            }
    <?php
}
   
    endforeach; 
    ?>
//Yii::$app->getSession()->addFlash('success', Yii::t('amoscore', 'Item created'));
return json_encode($model->toArray());
} else {
//Yii::$app->getSession()->addFlash('danger', Yii::t('amoscore', 'Item not created, check data'));
return $this->renderAjax('_formAjax', [
'model' => $model,
'fid' => $fid,
'dataField' => $dataField
]);
}
} else {
return $this->renderAjax('_formAjax', [
'model' => $model,
'fid' => $fid,
'dataField' => $dataField
]);
}
}

/**
* Updates an existing <?= $modelClass ?> model.
* If update is successful, the browser will be redirected to the 'view' page.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return mixed
*/
public function actionUpdate(<?= $actionParams ?>)
{
$this->layout = "@vendor/lispa/amos-core/views/layouts/form";
$model = $this->findModel(<?= $actionParams ?>);
<?php foreach ((array) $elencoAttributi as $key => $Relation):        
        ?>   
$model->attr<?= Inflector::id2camel($key, '_') ?>Mm = $model-><?= lcfirst(Inflector::id2camel($key, '_')) ?>;
    <?php      
    endforeach; 
    ?>

if ($model->load(Yii::$app->request->post()) && $model->validate()) {
<?php foreach ((array) $elencoAttributi as $key => $Relation):        
        ?>   
$<?= Inflector::id2camel($key, '_') ?>MmPost = \Yii::$app->request->post('<?= Inflector::id2camel($Relation['fromEntity'], '_') ?>')['attr<?= Inflector::id2camel($key, '_') ?>Mm'];
    <?php     
    endforeach; 
    ?>
if($model->save()){
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
        } else { ?>
    if(!empty($<?= Inflector::id2camel($key, '_') ?>MmPost)){
            $newRelazioneMm = new \backend\models\<?= Inflector::id2camel($Relation['fromEntity'] . '_' . $Relation['toEntity'] . '_mm' . $Relation['index'], '_') ?>();  
            $newRelazioneMm-><?= $Relation['fromEntity'] ?>_id = $model->id;
            $newRelazioneMm-><?= $Relation['toEntity'] ?>_id = $<?= Inflector::id2camel($key, '_') ?>MmPost;
            $newRelazioneMm->save(false);
            }
    <?php
        } 
    endforeach; 
    ?>
Yii::$app->getSession()->addFlash('success', Yii::t('amoscore', 'Item updated'));
return $this->redirect(['index']);
} else {
Yii::$app->getSession()->addFlash('danger', Yii::t('amoscore', 'Item not updated, check data'));
return $this->render('update', [
'model' => $model,
'fid' => NULL,
    'dataField' => NULL,
    'dataEntity' => NULL,
]);
}
} else {
return $this->render('update', [
'model' => $model,
'fid' => NULL,
    'dataField' => NULL,
    'dataEntity' => NULL,
]);
}
}

/**
* Deletes an existing <?= $modelClass ?> model.
* If deletion is successful, the browser will be redirected to the 'index' page.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return mixed
*/
public function actionDelete(<?= $actionParams ?>)
{
$model = $this->findModel(<?= $actionParams ?>);
if($model){
//si può sostituire il  delete() con forceDelete() in caso di SOFT DELETE attiva 
//In caso di soft delete attiva e usando la funzione delete() non sarà bloccata
//la cancellazione del record in presenza di foreign key quindi 
//il record sarà cancelleto comunque anche in presenza di tabelle collegate a questo record
//e non saranno cancellate le dipendenze e non avremo nemmeno evidenza della loro presenza
//In caso di soft delete attiva è consigliato modificare la funzione oppure utilizzare il forceDelete() che non andrà 
//mai a buon fine in caso di dipendenze presenti sul record da cancellare
$model->delete();
Yii::$app->getSession()->addFlash('success', Yii::t('amoscore', 'Item deleted'));
} else {
Yii::$app->getSession()->addFlash('danger', Yii::t('amoscore', 'Item not found'));
}
return $this->redirect(['index']);
}

}
