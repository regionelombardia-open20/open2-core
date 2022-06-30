<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\giiamos\model
 * @category   CategoryName
 */
use yii\gii\generators\model\Generator;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\form\Generator $generator
 */
echo $form->field($generator, 'tableName');
echo $form->field($generator, 'tablePrefix');
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'ns');
echo $form->field($generator, 'vendorPath');

//baseClass names
echo $form->field($generator, 'baseClassName')->dropDownList($generator->baseClassNames, ['id' => 'baseclass_name', 'prompt' => 'Personalizzato']);
echo $form->field($generator, 'baseClass')->label(false);


foreach ($generator->baseClassNames as $key => $value) {
    $arrayname = $key.'_sel';
 
    echo $form->field($generator, 'baseclassDynamic['.$arrayname.']')->checkboxList($generator->baseInterfaceNames, [$generator->baseclassDynamic[$arrayname]], [
        'item' => function($index, $label, $name, $checked, $value) {
            $checked = $checked ? 'checked' : '';
            $checkbox = Html::checkbox($name, $checked, ['value' => $value]);
            return Html::tag('div', Html::label($checkbox . $label), ['class' => 'checkbox']);
        }
    ]);
}

echo $form->field($generator, 'baseInterfaceNames_sel')->checkboxList($generator->baseInterfaceNames, [$generator->baseInterfaceNames_sel], [
    'item' => function($index, $label, $name, $checked, $value) {
        $checked = $checked ? 'checked' : '';
        $checkbox = Html::checkbox($name, $checked, ['value' => $value]);
        return Html::tag('div', Html::label($checkbox . $label), ['class' => 'checkbox']);
    }
]);

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

echo $form->field($generator, 'migrationName')->textInput(['readonly' => true])->label(false);

$script = <<< JS

// Remove error message to the parent object of the object (param obj)
    function removeErrorMessage(obj) {
        if($(obj).parent().hasClass("has-error")){
            $(obj).parent().removeClass("has-error");
            $(".errore-"+$(obj)[0].id).remove();
        
            $('div.field-generator-baseclass div.help-block').hide();
        }
    }
        
// Hide all baseclassdynamic checkboxList  
   function hideInterfaceCheck() {
        $("[class^='form-group field-generator-baseclassdynamic']").hide();
    }
        
$('#baseclass_name')
    .change(function () {
       var str = "";
       str = $("#baseclass_name").val();
       text = $("#baseclass_name option:selected").text(); 
     
    if(str){
        $('div.field-generator-baseclass div.sticky-value').hide();
        $('input#generator-baseclass').show();
        $('input#generator-baseclass').val(text);
//        $('input#generator-baseclass').prop('readonly', true);
        $('input#generator-baseclass').hide()
        $('div.field-generator-baseclass div.sticky-value').text(str);
        
        removeErrorMessage($('input#generator-baseclass'));
        
        $('.field-generator-baseinterfacenames_sel').hide();
        hideInterfaceCheck();
        
        $('.field-generator-baseclassdynamic-'.concat(str.toLowerCase()).concat('_sel')).show();
    } else {
        hideInterfaceCheck();
            
        $('.field-generator-baseinterfacenames_sel').show();
        $('div.field-generator-baseclass div.sticky-value').text(' - ');     
        $('div.field-generator-baseclass div.sticky-value').show();
        $('input#generator-baseclass').val('');
        $('input#generator-baseclass').prop('readonly', false);
        $('input#generator-baseclass').hide();
    }
});
        
       
 $(document).ready(function(){ 
    str = $("#baseclass_name").val();
    if(!str){
        $('.field-generator-baseinterfacenames_sel').show();
        hideInterfaceCheck();
    } else {
        $('.field-generator-baseinterfacenames_sel').hide();
        hideInterfaceCheck();
        $('.field-generator-baseclassdynamic-'.concat(str.toLowerCase()).concat('_sel')).show();
    }   
 });

JS;

$this->registerJs($script);
