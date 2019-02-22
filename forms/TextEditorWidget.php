<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms
 * @category   CategoryName
 */

namespace lispa\amos\core\forms;

use dosamigos\tinymce\TinyMce;
use dosamigos\tinymce\TinyMceAsset;
use dosamigos\tinymce\TinyMceLangAsset;
use lispa\amos\core\utilities\StringUtils;
use yii\helpers\ArrayHelper;
use yii\web\View;

/**
 * Class TextEditorWidget
 * @package lispa\amos\core\forms
 */
class TextEditorWidget extends TinyMce
{
    const upload_url = '/attachments/file/upload-files';

    public $language = 'en';
    
    public $clientOptions = [
        'menubar' => false,
        'paste_data_images' => true,
        'theme' => 'modern',
        'images_upload_url' => self::upload_url,
        'convert_urls' => false,
        'plugins' => [
            "advlist autolink lists link charmap print preview anchor",
            "searchreplace visualblocks code fullscreen code",
            "insertdatetime media table contextmenu paste textcolor image wordcount insertdatetime",
            "placeholder"
        ],
        'toolbar' => "fullscreen | undo redo code | styleselect | bold italic strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media insertdatetime | removeformat",
        'branding' => false,
    ];

    /**
     * TextEditorWidget constructor.
     * @param array $config
     */
    public function __construct($config = array())
    {

        $config = $this->evaluateConfiguration($config);
        parent::__construct($config);
    }

    /**
     * add plugin placeholder
     * Register additional script for ajax setup
     */
    protected function registerClientScript()
    {

        parent::registerClientScript();

        $view = $this->getView();

        $pluginPlaceholder = <<<JS
            tinymce.PluginManager.add("placeholder",function(a){a.on("init",function(){function d(){!a.settings.readonly==!0&&c.hide(),a.execCommand("mceFocus",!1)}function e(){""==a.getContent()?c.show():c.hide()}function f(){c.hide()}var c=new b;e(),tinymce.DOM.bind(c.el,"click",d),a.on("focus",d),a.on("blur",e),a.on("change",e),a.on("setContent",e),a.on("keydown",f)});var b=function(){var b=a.getElement().getAttribute("placeholder")||a.settings.placeholder,c=a.settings.placeholder_attrs||{style:{position:"absolute",top:"5px",left:0,color:"#888",padding:"1%",width:"98%",overflow:"hidden","white-space":"pre-wrap"}},d=a.getContentAreaContainer();tinymce.DOM.setStyle(d,"position","relative"),this.el=tinymce.DOM.add(d,a.settings.placeholder_tag||"label",c,b)};b.prototype.hide=function(){tinymce.DOM.setStyle(this.el,"display","none")},b.prototype.show=function(){tinymce.DOM.setStyle(this.el,"display","")}});
JS;
        $view->registerJs($pluginPlaceholder);

        $js = [];
        $js[] = ' jQuery.ajaxSetup({
                         data: {"' . \Yii::$app->request->csrfParam . '": "' . \Yii::$app->request->csrfToken . '"},
                         cache:false
                    });';
        $view->registerJs(implode("\n", $js), View::POS_READY );
    }

    /**
     *
     * @return string
     */
    protected function getLanguage()
    {
        return StringUtils::substring(\Yii::$app->language, 0, 2) ;
    }

    /**
     *
     * @param array $config
     * @return array $config
     */
    protected function evaluateConfiguration($config = array())
    {
        if(isset($config['clientOptions']))
        {
            $config['clientOptions'] = ArrayHelper::merge($this->clientOptions, $config['clientOptions']);
        }else{
            $config['clientOptions'] = $this->clientOptions;
        }
        if(!isset($config['language'])){
            $this->language = $this->getLanguage();
            $config['language'] = $this->language;
        }

        if(isset($config['options']['height']))
        {
            $config['clientOptions']['height'] = $config['options']['height'];
        }
        if(isset($config['options']['maxlength']))
        {
            $config['clientOptions']['max_chars'] = $config['options']['maxlength'];
        }

        return $config;
    }
}
