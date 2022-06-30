<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\giiamos\widgets
 * @category   CategoryName
 */

use yii\gii\generators\model\Generator;
use kartik\widgets\DepDrop;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\form\Generator $generator
 */

//widget type
echo $form->field($generator, 'widgetType')->dropDownList([
    'ICON' => 'ICON',
    'GRAPHICS' => 'GRAPHICS',
]);

//widget scope
echo $form->field($generator, 'widgetScope')->radioList(['radio_father'=>'Widget Father','radio_son'=>'Widget Son','radio_standalone'=>'Widget Stand-alone'], ['id' => 'radio_widgetscope']);

//module name
echo $form->field($generator, 'moduleName')->dropDownList( $generator->modulesNames, ['id'=>'module_name', 'prompt'=> 'Select ...']);

//widget label
echo $form->field($generator, 'widgetLabel');

//widget name
echo $form->field($generator, 'widgetNameInput');

//widget description
echo $form->field($generator, 'widgetDescription');

echo $form->field($generator, 'vendorPath');


//widget icon
echo $form->field($generator, 'iconColor')->dropDownList([
    'color-primary' => 'color-primary',
    'color-secondary' => 'color-secondary',
    'color-third' => 'color-third',
    'color-lightPrimary' => 'color-lightPrimary',
    'color-darkPrimary' => 'color-darkPrimary',
    'color-admin' => 'color-admin',
    'color-grey' => 'color-grey',
    'color-darkGrey' =>'color-darkGrey' ,
]);

//icon class
echo $form->field($generator, 'iconClass');

if( empty($generator->iconFramework)){
    $generator->iconFramework = 'dash';
}
echo $form->field($generator, 'iconFramework')->radioList(['dash'=> 'dash', 'am'=>'am']);

// Child # 1
echo $form->field($generator, 'widgetFather')->widget(DepDrop::classname(), [
    //'data' => ['backend\modules\candidature_allievi\widgets\icons\WidgetIconlevel_of_education' => 'WidgetIconlevel_of_education'],
    'options'=>['id'=>'module_father', 'placeholder'=>'Select ...'],
    'select2Options'=>['pluginOptions'=>['allowClear'=>true]],
    'pluginOptions'=>[
        'initialize' => true,
        'depends'=>['module_name'],
        'url'=>Url::to(['/gii/ajax/widget-father-by-module']),
    ],
]);


//URL destination
echo $form->field($generator, 'widgetUrl');

//$generator->migrationName = (empty($generator->migrationName)) ? 'm'.date('ymd_His') : $generator->migrationName;
echo $form->field($generator, 'migrationName')->textInput(['readonly'=> true])->label(false);

echo $form->field($generator, 'rolesSelected')->widget(Select2::classname(), [
    'data' => ArrayHelper::map($generator->allRoles,
        'name', 'name'),
    'language' => 'it',
    'options' => [
        'multiple' => true,
        'id' => 'widget-roles',
        'placeholder' => 'Select ...',
        //'class' => 'dynamicCreation' . $append,
        'data-model' => 'genetor',
        'data-toggle' => 'tooltip'],
    'pluginOptions' => ['allowClear' => true],
    //'pluginEvents' => ["select2:open" => "dynamicInsertOpening"]]
]);

echo $form->field($generator, 'widgetVisible')->checkbox();

/*
echo $form->field($generator, 'tableName');
echo $form->field($generator, 'tablePrefix');
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'ns');
echo $form->field($generator, 'baseClass');
echo $form->field($generator, 'pluginName');
echo $form->field($generator, 'db');
echo $form->field($generator, 'generateRelations')->dropDownList([
    Generator::RELATIONS_NONE => 'No relations',
    Generator::RELATIONS_ALL => 'All relations',
    Generator::RELATIONS_ALL_INVERSE => 'All relations with inverse',
]);
echo $form->field($generator, 'generateLabelsFromComments')->checkbox();
echo $form->field($generator, 'generateModelClass')->checkbox();
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
*/

$script = <<< JS

    var radio_widgetscope = $('#radio_widgetscope'); //it's div
    var radio_father = radio_widgetscope.find(':radio[value=radio_father]');
    var radio_son = radio_widgetscope.find(':radio[value=radio_son]');
    var radio_standalone = radio_widgetscope.find(':radio[value=radio_standalone]');
    var select_wid_father = $('#module_father');
    
    var select_wid_type = $('#generator-widgettype');
    
    //check which radiobutton is selected: ONLY radio 'widget son' will active the select 'widget father'
    function enableSelectFather(){
        var father_selected = radio_father.is(':checked');
        var son_selected = radio_son.is(':checked');
        var standalone_selected = radio_standalone.is(':checked');

        /*console.log(father_selected, "father_selected");   
        console.log(son_selected, "son_selected");   
        console.log(standalone_selected, "standalone_selected");*/

        //only if the radio 'wiget son' is selected the select containing widgets father will be spawn
        if(son_selected){
            select_wid_father.removeAttr('disabled');
            return true;
        }else{
            select_wid_father.attr('disabled', 'disabled' );
            return true;
        }
        return false;
    }
    
    //enable/disable all the radio button 'widget scope' due the select 'widget type' value
    //if type = 'graphics' => DISABLED
    function enableWidgetScope(){
        var widget_type = select_wid_type.val();
        
        if(widget_type && widget_type.toLowerCase() == 'graphics'){
            //before disable all the radio 'widget scope': preselect standalone per disable the select 'widget father'
            radio_standalone.attr('checked', 'checked');
            radio_widgetscope.find(':radio').attr('disabled', 'disabled');
        }else{
            radio_widgetscope.find(':radio').removeAttr('disabled');
        }
    }
    
    String.prototype.toCamelCase = function() {
        return this
            .replace(/\s(.)/g, function($1) { return $1.toUpperCase(); })
            .replace(/\s/g, '')
            .replace(/^(.)/, function($1) { return $1.toUpperCase(); });
    }

    $(document).ready(function(){
        
        var num_radio_checked = radio_widgetscope.find(':radio:checked').length;
        if(num_radio_checked == 0){
            //radio son default selected if no other one is selected
            radio_standalone.attr('checked','checked');
        }
        
        setTimeout(function(){
            //trigger the controls on radio change
            enableSelectFather();
            //eneable/disable radio button on widget type change
            enableWidgetScope();
        }, 1500);
    });
    
    $('#module_name').on('change', function(){
        setTimeout(function(){
            enableSelectFather();
        }, 1500);
    });
    
    //check the input of 'widget name' field
    $('#generator-widgetnameinput').on('change keypress', function(evt) {
        //white space NOT allowed
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode === 32 || charCode === 17) {
             return false;
        }
    
        //convert inserted text into camelCase
        var camelize =  $(this).val().toCamelCase();
        $(this).val(camelize);
    });
    
    radio_widgetscope.on('change', function(){
        enableSelectFather();
    });
    
    select_wid_type.on('change', function(){
        enableWidgetScope();
    });
    
    /*
    radio_son.on('change', function(){
        enableSelectFather();
    });
    radio_standalone.on('change', function(){
        enableSelectFather();
    });*/
JS;

$this->registerJs($script);
