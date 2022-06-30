<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms
 * @category   CategoryName
 */

namespace open20\amos\core\forms;

use dosamigos\tinymce\TinyMce;
use dosamigos\tinymce\TinyMceAsset;
use dosamigos\tinymce\TinyMceLangAsset;
use open20\amos\core\module\Module;
use open20\amos\core\utilities\StringUtils;
use yii\helpers\ArrayHelper;
use yii\web\View;
use open20\amos\core\assets\TnyMentionAsset;

/**
 * Class TextEditorWidget
 * @package open20\amos\core\forms
 */
class TextEditorWidget extends TinyMce
{
    const upload_url = '/attachments/file/upload-files';

    public $language = 'en';
    private $tinyMCELabel;


    /**
     *
     * MENTIONS - URL to action to search for a user
     *
     * 'mentions' => [
     *      'url' => null
     *  ]
     *
     * MENTIONS - params TextEditor widget to enable mentions
     *
     * 'textEditor' => [
     *      "mentions" => [
     *          'enable' => true,
     *      ]
     * ]
     *
     */

    public $clientOptions = [
        'menubar' => false,
        'paste_data_images' => true,
        'theme' => 'modern',
        'images_upload_url' => self::upload_url,
        'convert_urls' => false,
        'allow_unsafe_link_target' => true,
        'plugins' => [
            "advlist autolink lists link charmap print preview anchor",
            "searchreplace visualblocks code fullscreen code",
            "insertdatetime media table contextmenu paste textcolor image insertdatetime",
            "placeholder"
        ],
        'image_advtab' => true,
        'toolbar' => "fullscreen | undo redo code | styleselect | bold italic strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media insertdatetime | removeformat",
        'branding' => false,

        // URL to action to search for a user
        'mentions' => [
            'url' => null
        ]
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
        TnyMentionAsset::register($this->view);

        $view = $this->getView();

        $pluginPlaceholder = <<<JS
            tinymce.PluginManager.add("placeholder",function(a){a.on("init",function(){function d(){!a.settings.readonly==!0&&c.hide(),a.execCommand("mceFocus",!1)}function e(){""==a.getContent()?c.show():c.hide()}function f(){c.hide()}var c=new b;e(),tinymce.DOM.bind(c.el,"click",d),a.on("focus",d),a.on("blur",e),a.on("change",e),a.on("setContent",e),a.on("keydown",f)});var b=function(){var b=a.getElement().getAttribute("placeholder")||a.settings.placeholder,c=a.settings.placeholder_attrs||{style:{position:"absolute",top:"5px",left:0,color:"#888",padding:"1%",width:"98%",overflow:"hidden","white-space":"pre-wrap"}},d=a.getContentAreaContainer();tinymce.DOM.setStyle(d,"position","relative"),this.el=tinymce.DOM.add(d,a.settings.placeholder_tag||"label",c,b)};b.prototype.hide=function(){tinymce.DOM.setStyle(this.el,"display","none")},b.prototype.show=function(){tinymce.DOM.setStyle(this.el,"display","")}});
            tinymce.PluginManager.add("charactercount",function(e){var n=this;function a(){e.theme.panel.find("#charactercount").text(["$this->tinyMCELabel",n.getCount()])}e.on("init",function(){var t=e.theme.panel&&e.theme.panel.find("#statusbar")[0];t&&window.setTimeout(function(){t.insert({type:"label",name:"charactercount",text:["$this->tinyMCELabel",n.getCount()],classes:"charactercount",disabled:e.settings.readonly},0),e.on("setcontent beforeaddundo",a),e.on("keyup",function(t){a()})},0)}),n.getCount=function(){return function(t){var e=document.createElement("textarea");return e.innerHTML=t,e.value}(e.getContent({format:"raw"})).replace(/(<([^>]+)>)/gi,"").trim().length}});
JS;
        if(!empty($this->tinyMCELabel)) {
            $tinyLabelCharsCounter = <<<JS
                let charsCounterLabel = '$this->tinyMCELabel';
JS;
            $view->registerJs($tinyLabelCharsCounter);
        }

        $view->registerJs($pluginPlaceholder);

        $js = [];
        $js[] = ' jQuery.ajaxSetup({
                         data: {"' . \Yii::$app->request->csrfParam . '": "' . \Yii::$app->request->csrfToken . '"},
                         cache:false
                    });';
        $view->registerJs(implode("\n", $js), View::POS_READY);
    }

    /**
     *
     * @return string
     */
    protected function getLanguage()
    {
        $languageAsset = TinyMceLangAsset::register($this->view);
        $languagePath = $languageAsset->basePath;
        $langCode = StringUtils::substring(\Yii::$app->language, 0, 2);

        if(file_exists("{$languagePath}/langs/{$langCode}.js")) {
            return $langCode;
        } else {
            return str_replace('-','_', \Yii::$app->language);
        }
    }


    /**
     *
     * @param array $config
     * @return array $config
     */
    protected function evaluateConfiguration($config = array())
    {

        try {
            if (class_exists('open20\amos\admin\AmosAdmin')) {

                // default mention action url
                $adminName = \open20\amos\admin\AmosAdmin::getModuleName();
                $mention_action_url = "/$adminName/user-profile/find-name-user-by-cwh";

                // platform params for textEditor mentions
                if( \Yii::$app->params['textEditor']['mentions']['enable'] ){

                    // check if exist mention action url exist from parmas configuration
                    if( null != $config['clientOptions']['mentions']['url'] ){
                        $mention_action_url = $config['clientOptions']['mentions']['url'];
                    }
                    $className = null;
                    if(!is_null(\Yii::$app->controller->getModelObj()))
                    {
                        $className = str_replace('\\', '\\\\', \Yii::$app->controller->getModelObj()->className());
                    }
                    $mentionMethod = <<<JS
                    function(query, process, delimiter) {
                        // Do your ajax call
                        // When using multiple delimiters you can alter the query depending on the delimiter used
                        if (delimiter === '@') {

                            $.getJSON("$mention_action_url", {name: query, className: "$className"}, function (data) {
                                //call process to show the result
                                process(data)
                            });
                        }
                    }
JS;

                    $mentionElementMethod = <<<JS
                        function(item) {
                            console.log(item.user_id);
                            return '<a href="'+item.url+'">@' + item.name + '</a>&nbsp;';
                        }
JS;

                    $this->clientOptions['mentions'] = [
                        'source' => new \yii\web\JsExpression($mentionMethod),
                        'insert' => new \yii\web\JsExpression($mentionElementMethod)
                    ];
                }

            }

        } catch (\Exception $e) {

        }


        if(empty($config['clientOptions']['wordcount'])) {
            $this->clientOptions['plugins'][] = "charactercount";
            $this->tinyMCELabel = Module::t('amoscore', '#tinyMCECharsCount');
        } else {
            $this->clientOptions['plugins'][] = "wordcount";
        }

        // section for configuring mobile options
        $mobile_options = array();
        $mobile_options['mobile']['menubar'] = true;
        $mobile_options['mobile']['plugins'] = ['autosave', 'lists', 'autolink' ];
        $mobile_options['mobile']['theme'] = 'mobile';
        $mobile_options['mobile']['content_style'] = 'body {background-color: white;}';
        $mobile_options['mobile']['toolbar'] = [
            'fullscreen', 'undo redo code' ,'styleselect ',
            'bold italic strikethrough forecolor backcolor',
            'link image media insertdatetime', 'removeformat'
        ];
        $this->clientOptions = ArrayHelper::merge($this->clientOptions, $mobile_options);


        if (isset($config['clientOptions'])) {
            $config['clientOptions'] = ArrayHelper::merge($this->clientOptions, $config['clientOptions']);
        } else {
            $config['clientOptions'] = $this->clientOptions;
        }

        if (!isset($config['language'])) {
            $this->language = $this->getLanguage();
            $config['language'] = $this->language;
        }

        if (isset($config['options']['height'])) {
            $config['clientOptions']['height'] = $config['options']['height'];
        }
        if (isset($config['options']['maxlength'])) {
            $config['clientOptions']['max_chars'] = $config['options']['maxlength'];
        }

        // MENTIONS - plugin enable
        if( \Yii::$app->params['textEditor']['mentions']['enable'] ){
            $config['clientOptions']['plugins'][] = "mention";
        }

        return $config;
    }

}
