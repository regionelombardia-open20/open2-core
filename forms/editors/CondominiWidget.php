<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms\editors
 * @category   CategoryName
 */

namespace lispa\amos\core\forms\editors;

use lispa\amos\core\views\AmosGridView;
use lispa\amos\core\views\grid\ActionColumn;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class CondominiWidget extends Widget {

    //model di partenza
    public $model = null;
    //id del model record
    public $modelId = null;
    public $modelData = null;
    public $modelDataArr = null;
    public $permissions = [
        'add' => null
    ];
    //url della pagina di associazione
    public $targetUrl = null;
    //model di scelta associazioni
    public $modelTarget = null;
    //classe di ricerca per il model
    public $modelTargetSearch = [];
    public $modelTargetData = null;
    public $layout = null;
    public $layoutMittente = "{toolbarMittente}\n{itemsMittente}\n{footerMittente}";
    public $layoutTarget = "{toolbarTarget}\n{hiddenInputTarget}\n{itemsTarget}\n{footerTarget}";
    //variabili usate per identificare gli oggetti da salvare nel target
    public $postName = "UserProfile";
    public $postKey = "condomini";

    protected function throwErrorMessage($field) {
        return \Yii::t('amoscore', 'Configurazione widget non corretta, {campo} mancante', [
                    'campo' => $field
        ]);
    }

    public function init() {
        parent::init();
        $this->layout = $this->layoutMittente;
        //se sono il widget di destinazione per le scelte delle associazioni
        if (!$this->targetUrl) {
            $this->layout = $this->layoutTarget;

            if (!$this->modelTargetSearch) {
                throw new InvalidConfigException($this->throwErrorMessage('modelTargetSearch'));
            }

            if (!$this->modelTargetSearch['class']) {
                throw new InvalidConfigException($this->throwErrorMessage('modelTargetSearch[class]'));
            }
            if (!$this->modelTargetSearch['action']) {
                throw new InvalidConfigException($this->throwErrorMessage('modelTargetSearch[action]'));
            }

            $this->modelTarget = \Yii::createObject($this->modelTargetSearch['class']);

            $this->modelTargetData = $this->modelTarget->{$this->modelTargetSearch['action']}(\Yii::$app->request->getQueryParams());
        }

        if (!$this->modelData) {
            throw new InvalidConfigException($this->throwErrorMessage('modelData'));
        }

        if (!$this->modelId) {
            throw new InvalidConfigException($this->throwErrorMessage('modelId'));
        }

        if (!$this->model) {
            throw new InvalidConfigException($this->throwErrorMessage('model'));
        }

        $this->modelDataArr = ArrayHelper::map($this->modelData->all(), 'id', 'id');
    }

    public function run() {
        $content = preg_replace_callback("/{\\w+}/", function ($matches) {
            $content = $this->renderSection($matches[0]);

            return $content === false ? $matches[0] : $content;
        }, $this->layout);

        return $content;
    }

    /**
     * Renders a section of the specified name.
     * If the named section is not supported, false will be returned.
     * @param string $name the section name, e.g., `{summary}`, `{items}`.
     * @return string|boolean the rendering result of the section, or false if the named section is not supported.
     */
    public function renderSection($name) {
        switch ($name) {
            case '{toolbarMittente}':
                return $this->renderToolbarMittente();
            case '{itemsMittente}':
                return $this->renderItemsMittente();
            case '{toolbarTarget}':
                return $this->renderToolbarTarget();
            case '{hiddenInputTarget}':
                return $this->renderHiddenInputTarget();
            case '{itemsTarget}':
                return $this->renderItemsTarget();
            case '{footerTarget}':
                return $this->renderFooterTarget();
            case '{footerMittente}':
                return $this->renderFooterMittente();
            default:
                return false;
        }
    }

    /**
     * Renders the data models for the grid view.
     */
    public function renderItemsMittente() {
        return AmosGridView::widget([
                    'dataProvider' => new \yii\data\ActiveDataProvider([
                        'query' => $this->modelData
                            ]),
                    'columns' => [
                        /* [
                          'class' => 'yii\grid\SerialColumn',
                          ], */
                         'filemanager_mediafile_id' =>
                          [
                          'label' => '',
                          'format' => 'html',
                          'value' => function ($model) {

                          $mediafile = \pendalf89\filemanager\models\Mediafile::findOne($model['filemanager_mediafile_id']);
                          $url = '/img/img_default.jpg';
                          if ($mediafile) {
                          $url = $mediafile->getThumbUrl('small');
                          }
                          return Html::img($url, ['width' => '50']);
                          }
                          ], 
                        'nome',
                        'codice_fiscale',
                        'via' => [
                          'attribute' => 'via',
                          'label' => 'Indirizzo',
                          'value' => 'indirizzoCompleto',
                        ],
                        [
                            'class' => ActionColumn::className(),
                            'template' => '{condominio}',
                            'buttons' => [
                                'condominio' => function ($url, $model) {
                                    //$url = \yii\helpers\Url::current();
                                    return Html::a('<p class="btn" title="Visualizza i dettagli del condominio"><span id="bk-btnAssociaCondominio" class="ti ti-file"></span></p>', Yii::$app->urlManager->createUrl(['admin/user-profile/vedi-condominio', 'id' => $model['id'], 'utente' => $this->model['id'], 'url' => 'tab-condominio']), [
                                                    'title' => Yii::t('amoscore', 'Visualizza i dettagli del condominio'),
                                        ]);                                    
                                },
                                    ],
                                ],
                            ]
                ]);
            }

            /**
             * Renders the data models for the grid view.
             */
            public function renderItemsTarget() {
                $this->getView()->params['modelDataArr'] = $this->modelDataArr;
                $this->getView()->params['modelTargetData'] = $this->modelTargetData;
                $this->getView()->params['postKey'] = $this->postKey;
                $this->getView()->params['postName'] = $this->postName;
                $Grid = $this->getView()->renderFile('@backend/modules/admin/views/user-profile/associa_condomini_grid.php', [
                    'searchModel' => $this->modelTarget
                ]);
                return $Grid;
            }

            /**
             * Renders the toolbar
             */
            public function renderToolbarMittente() {
                $buttons = '';
                if (\Yii::$app->getUser()->can($this->permissions['add'], ['model' => $this->model])) {
                    $buttons = Html::a('Associa', [$this->targetUrl, 'id' => $this->modelId], [
                                'class' => 'btn btn-primary btn-associa',
                    ]);
                }
                return Html::tag('div', $buttons, ['class' => 'row']);
            }

            /**
             * Renders the toolbar
             */
            public function renderToolbarTarget() {
                $buttons = Html::submitButton('Salva', [
                            'class' => 'btn btn-primary btn-associa-salva']);

                return Html::beginForm() . Html::tag('div', $buttons, ['class' => 'row']);
            }

            /**
             * Renders the toolbar
             */
            public function renderFooterTarget() {
                return Html::endForm();
            }

            /**
             * Renders the input hidden section
             */
            public function renderHiddenInputTarget() {
                $hiddenInputSection = "";

                foreach ($this->modelDataArr as $id => $label) {
                    $hiddenInputSection .= Html::tag("input", null, ['value' => $id, 'type' => 'hidden', 'name' => $this->postName . '[' . $this->postKey . '][]']);
                }

                return Html::tag('div', $hiddenInputSection, ['class' => 'hiddenInputContainer']);
            }

            /**
             * Renders the toolbar
             */
            public function renderFooterMittente() {
                return '';
            }

        }
        