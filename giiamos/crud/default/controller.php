<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\giiamos\crud\default
 * @category   CategoryName
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;

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
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\helpers\Html;
use open20\amos\core\helpers\T;
use yii\helpers\Url;
use open20\amos\core\module\BaseAmosModule;

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
                'label' => BaseAmosModule::t('amoscore', '{iconaTabella}'.Html::tag('p',BaseAmosModule::t('amoscore', 'Table')), [
                    'iconaTabella' => AmosIcons::show('view-list-alt')
                ]),                
                'url' => '?currentView=grid'
            ],
            /*'list' => [
                'name' => 'list',
                'label' => BaseAmosModule::t('amoscore', '{iconaLista}'.Html::tag('p',BaseAmosModule::t('amoscore', 'List')), [
                    'iconaLista' => AmosIcons::show('view-list')
                ]),           
                'url' => '?currentView=list'
            ],
            'icon' => [
                'name' => 'icon',
                'label' => BaseAmosModule::t('amoscore', '{iconaElenco}'.Html::tag('p',BaseAmosModule::t('amoscore', 'Icons')), [
                    'iconaElenco' => AmosIcons::show('grid')
                ]),           
                'url' => '?currentView=icon'
            ],
            'map' => [
                'name' => 'map',
                'label' => BaseAmosModule::t('amoscore', '{iconaMappa}'.Html::tag('p',BaseAmosModule::t('amoscore', 'Map')), [
                    'iconaMappa' => AmosIcons::show('map')
                ]),       
                'url' => '?currentView=map'
            ],
            'calendar' => [
                'name' => 'calendar',
                'intestazione' => '', //codice HTML per l'intestazione che verrà caricato prima del calendario,
                                      //per esempio si può inserire una funzione $model->getHtmlIntestazione() creata ad hoc
                'label' => BaseAmosModule::t('amoscore', '{iconaCalendario}'.Html::tag('p',BaseAmosModule::t('amoscore', 'Calendar')), [
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
$this->layout = "@vendor/open20/amos-core/views/layouts/form";
$model = new <?= $modelClass ?>;

if ($model->load(Yii::$app->request->post()) && $model->validate()) {
if($model->save()){
Yii::$app->getSession()->addFlash('success', BaseAmosModule::t('amoscore', 'Item created'));
return $this->redirect(['index']);
} else {
Yii::$app->getSession()->addFlash('danger', BaseAmosModule::t('amoscore', 'Item not created, check data'));
return $this->render('create', [
'model' => $model,
]);
}
} else {
return $this->render('create', [
'model' => $model,
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
$this->layout = "@vendor/open20/amos-core/views/layouts/form";
$model = $this->findModel(<?= $actionParams ?>);

if ($model->load(Yii::$app->request->post()) && $model->validate()) {
if($model->save()){
Yii::$app->getSession()->addFlash('success', BaseAmosModule::t('amoscore', 'Item updated'));
return $this->redirect(['index']);
} else {
Yii::$app->getSession()->addFlash('danger', BaseAmosModule::t('amoscore', 'Item not updated, check data'));
return $this->render('update', [
'model' => $model,
]);
}
} else {
return $this->render('update', [
'model' => $model,
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
if($model->delete()){
Yii::$app->getSession()->addFlash('success', BaseAmosModule::t('amoscore', 'Item deleted'));
} else {
Yii::$app->getSession()->addFlash('danger', BaseAmosModule::t('amoscore', 'Item not deleted because of dependency'));
}
} else {
Yii::$app->getSession()->addFlash('danger', BaseAmosModule::t('amoscore', 'Item not found'));
}
return $this->redirect(['index']);
}
}
