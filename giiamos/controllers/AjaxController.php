<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\giiamos\controllers
 * @category   CategoryName
 */

namespace lispa\amos\core\giiamos\controllers;

use yii\web\Controller;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\helpers\StringHelper;

class AjaxController extends Controller {

    public function actionWidgetFatherByModule(){
        //\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $ret_array = [];
        $selected = [];

        //addictional path for reach the widget's file
        $addictional_path = 'widgets' . DIRECTORY_SEPARATOR . 'icons';
        $parents = $_POST['depdrop_parents'];
//pr($extra_params, "exrta");
        if (!empty($parents)) {
            $module_name = $parents[0];

            //get the module object
            $ModObj = \Yii::$app->getModule($module_name);

            if( is_object($ModObj) ){
                //get the complete path of the file
                $path = $ModObj->getBasePath();
                //get the namespace of the module (not the clean one, with an addictional subpath e.g \Module )
                $namespace_dirty = get_class($ModObj);
                //get addictional subpath above nominated
                $str_module = StringHelper::baseName($namespace_dirty);
                //remove the addictional path from the namaspace
                $module_namespace = str_replace($str_module, '', $namespace_dirty );
            }

            //pr($module_namespace, "module namespace");
            //pr($path, "path");
            $files = array();
            if(isset($path)){
                //find all the file in the default widget path (#path\widgets\icons\)
                $files = FileHelper::findFiles($path . DIRECTORY_SEPARATOR . $addictional_path);
            }

            foreach ($files as $key => $file){
                //pr($file, "file");
                //get the clean file name without extension
                $widget_filename = str_replace(['.php', '.PHP'], '' , StringHelper::baseName($file) );
                //pr($widget_filename, "widget filename");
                $namespace = str_replace('/', '\\', $module_namespace.$addictional_path).'\\'.$widget_filename;
                //pr($namespace, "namespace");
                $ret_array[] = array('id'=>$namespace, 'name'=> $widget_filename);
            }

            //$selected= 'backend\modules\candidature_allievi\widgets\icons\WidgetIconPcandidature_allievi';
            if(!empty( $_SESSION['widgetFather'] )){
                $selected =  $_SESSION['widgetFather'];
            }
            echo Json::encode(['output'=>$ret_array, 'selected'=>$selected]);
            return;
        }
        return Json::encode(['output'=>'', 'selected'=>'']);
    }

}