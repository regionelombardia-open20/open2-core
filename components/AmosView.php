<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\components
 * @category   CategoryName
 */

namespace lispa\amos\core\components;

use yii\web\View;
use lispa\amos\core\views\assets\CheckScopeAsset;
use Yii;

/**
 * Class AmosView
 * @package lispa\amos\core\components
 */
class AmosView extends View
{
    /**
     * @event Event an event that is triggered by [[beginViewContent()]].
     */
    const BEFORE_RENDER_CONTENT = 'BEFORE_RENDER_CONTENT';
    
    /**
     * @event Event an event that is triggered by [[endViewContent()]].
     */
    const AFTER_RENDER_CONTENT = 'AFTER_RENDER_CONTENT';
    
    /**
     * Marks the beginning of the HTML content section.
     */
    public function beginViewContent()
    {
        $this->trigger(self::BEFORE_RENDER_CONTENT);
    }
    
    /**
     * Marks the ending of the HTML content section.
     */
    public function endViewContent()
    {
        $this->trigger(self::AFTER_RENDER_CONTENT);
    }
    
    public function init() {
        parent::init();
        if(Yii::$app instanceof \yii\web\Application)
        {
            CheckScopeAsset::register($this);
        }
        
        
    }
    
    /**
     * 
     * @param type $viewFile
     * @param type $params
     * @return type
     */
    public function beforeRender($viewFile, $params) {
        if(Yii::$app instanceof \yii\web\Application)
        {
            $options = [
                'idScope' =>  (isset(Yii::$app->session['cwh-scope']['community']) ? Yii::$app->session['cwh-scope']['community'] : ''),
            ];
            $this->registerJs(
                "var yiiOptions = ".\yii\helpers\Json::htmlEncode($options).";",
               View::POS_HEAD,
                'yiiOptions'
            );
        }
        return parent::beforeRender($viewFile, $params);
        
    }
}
