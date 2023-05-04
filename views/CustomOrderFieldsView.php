<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\organizzazioni\views\profilo
 * @category   CategoryName
 */

use open20\amos\core\module\BaseAmosModule;

use yii\helpers\Url;
use kartik\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var open20\amos\organizzazioni\models\search\ProfiloSearch $model
 */

$this->title = BaseAmosModule::t('amoscore', 'manage_order_fields');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col-xs-12">
    <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        
        'label'=>[
            'header'=>BaseAmosModule::t('amoscore', 'field'),
            'value'=>function($model){
                return $model['label'];
            }
        ],
        'visible'=>[ 
            'header'=>BaseAmosModule::t('amoscore', 'order_fields_visible'),
            'class' => 'open20\amos\core\views\grid\CheckboxColumn',
            'name' => $moduleName,
            'checkboxOptions' => function ($model, $key, $index, $column) use($updateUrl) {
                           
                return [
                    'style' => 'cursor:pointer',
                    'checked' => $model['visible'],
                    'data-column'=>$model['field'],
                    /*'onclick' => '
                        $.post("'.Url::to($updateUrl).'",{rows:[{field:this.dataset.column,visible:this.checked | 0}]},function(data){
                            if(data.error)
                                location.reload();
                        },"json").error(function(xhr, ajaxOptions, thrownError) {                           
                            alert( xhr.status + thrownError );
                        });
                    ',*/
                ];
            }
    
        ],
        
       
    ],
]) ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 wrap-button">
        <?= \open20\amos\core\helpers\Html::button(\Yii::t('amoscore', 'Salva'), ['id'=>'saveCustom','class' => 'btn btn-navigation-primary pull-right']); ?>
    </div>
</div>

<?php
$jsurl = Url::to($updateUrl); 
$name = $moduleName.'[]';
$js = <<<JS
    
    $('#saveCustom').click(function(){
        
        var rows = [];
        
        var input = $('input[name="$name"]').each(function (index, obj) {
            rows.push({field: obj.dataset.column, visible:obj.checked | 0});                     
        });

        $.post("$jsurl",{rows:rows},function(data){           
            location.reload();
        },"json").error(function(xhr, ajaxOptions, thrownError) {                           
            alert( xhr.status + thrownError );
        });
        
    });
JS;
$this->registerJs($js);
?>




