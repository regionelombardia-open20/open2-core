<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms
 * @category   CategoryName
 */

namespace open20\amos\core\forms;

use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use Yii;
use yii\base\Widget;
use yii\data\ActiveDataProvider;

/**
 * Class PublishedContentsWidget
 * @package open20\amos\core\forms
 */
class PublishedContentsWidget extends Widget
{
    /**
     * @var string classname of the model of the listed objects
     */
    public $modelClass;

    /**
     * @var string label for the model of the listed objects
     */
    public $modelLabel;

    /**
     * @var string icon for the model of the listed objects
     */
    public $modelIcon;
    public $modelIcons = [
        'open20\amos\news\models\News' => 'feed',
        'open20\amos\discussioni\models\DiscussioniTopic' => 'comment',
        'open20\amos\documenti\models\Documenti' => 'file-text-o',
        'open20\amos\partnershipprofiles\models\PartnershipProfiles' => 'lightbulb-o',
        'open20\amos\risultati\models\Risultati' => 'gears',
        'open20\amos\showcaseprojects\models\ShowcaseProjectProposal' => 'gears',
    ];

    /**
     * @var array $scope Id  of the scope in which it is published the listed objects (eg. a community ID)
     */
    public $scope;

    /**
     * @var string $layout Widget view
     */
    public $layout = "@vendor/open20/amos-core/forms/views/widgets/published_contents_widget.php";

    /**
     * @var ActiveDataProvider $data Ihe data list to show
     */
    public $data;

    /**
     * @var array $gridViewColumns The column list used in object gridview
     */
    public $gridViewColumns = [];

    /**
     * @var bool|false true if we are in edit mode
     */
    public $isUpdate = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $object = Yii::createObject($this->modelClass);

        $this->gridViewColumns = $object->getGridViewColumns();
        $this->modelLabel = $object->getGrammar()->getModelLabel();
        $this->modelIcon = !empty($this->modelIcons[$this->modelClass]) ? AmosIcons::show($this->modelIcons[$this->modelClass], [], 'dash') : '';

        $moduleCwh = \Yii::$app->getModule('cwh');

        $query = null;

        if (!is_null($moduleCwh)) {
            /** @var \open20\amos\cwh\AmosCwh $moduleCwh */

            // Save old cwh scope
            $oldScope = $moduleCwh->getCwhScope();

            // Set new cwh scope
            $moduleCwh->resetCwhScopeInSession();
            $moduleCwh->setCwhScopeInSession($this->scope);
            $moduleCwh->setCwhScopeFromSession();
            $cwhActiveQuery = new \open20\amos\cwh\query\CwhActiveQuery(
                $this->modelClass, [
                'queryBase' => $object::find()->distinct()
            ]);
            $query = $cwhActiveQuery->getQueryCwhAll();

            // Reset cwh scope to old scope
            $moduleCwh->resetCwhScopeInSession();
            $moduleCwh->setCwhScopeInSession($oldScope);
            $moduleCwh->setCwhScopeFromSession();
        }

        $this->data = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 5],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_ASC
                ]
            ]
        ]);

        $this->gridViewColumns[] = [
            'class' => 'open20\amos\core\views\grid\ActionColumn',
            'headerOptions' => [
                'id' => 'favourite'
            ],
            'contentOptions' => [
                'headers' => 'favourite'
            ],
            'template' => '{view}{update}{delete}{favourite}',
            'buttons' => [
                'view' => function ($url, $model) {
                    $createUrlParams = [
                        $model->getViewUrl(),
                        'id' => $model['id'],
                    ];
                    $btn = Html::a(
                        AmosIcons::show('file', ['class' => '']),
                        Yii::$app->urlManager->createUrl($createUrlParams),
                        [
                            'title' => Yii::t('amoscore', 'Leggi'),
                            'class' => 'btn btn-tool-secondary',
                            'target' => '_blank'
                        ],
                        true    // Verifica i permessi col nuovo metodo nella classe Html
                    );
                    return $btn;
                },
                'update' => function ($url, $model) {
                    if (!$this->isUpdate) {
                        return '';
                    }
                    $createUrlParams = [
                        substr_replace($model->getViewUrl(), '/update', strrpos($model->getViewUrl(), '/')),
                        'id' => $model['id'],
                    ];
                    $btn = Html::a(
                        AmosIcons::show('edit', ['class' => 'btn btn-tool-secondary']),
                        Yii::$app->urlManager->createUrl($createUrlParams),
                        [
                            'title' => Yii::t('amoscore', 'Edit'),
                            'class' => 'bk-btnEdit',
                            'target' => '_blank'
                        ],
                        true    // Verifica i permessi col nuovo metodo nella classe Html
                    );
                    return $btn;
                },
                'delete' => function ($url, $model) {
                    if (!$this->isUpdate) {
                        return '';
                    }
                    $createUrlParams = [
                        substr_replace($model->getViewUrl(), '/delete', strrpos($model->getViewUrl(), '/')),
                        'id' => $model['id'],
                    ];
                    $btn = Html::a(
                        AmosIcons::show('delete', ['class' => 'btn btn-tool-secondary']),
                        Yii::$app->urlManager->createUrl($createUrlParams),
                        [
                            'title' => Yii::t('amoscore', 'Delete'),
                            'class' => 'bk-btnDelete',
                            'target' => '_blank'
                        ],
                        true    // Verifica i permessi col nuovo metodo nella classe Html
                    );
                    return $btn;
                },
                'favourite' => function ($url, $model) {
//                    $btn = Html::a(
//                        AmosIcons::show('star', ['class' => 'btn btn-tool-secondary']),
//                                    'javascript:void(0)',
//                                    [
//                                        'title' => Yii::t('amoscore', 'Aggiungi/rimuovi preferito'),
//                                        'class' => 'bk-btnEdit',
//                                    ],
//                        false  //TODO replace with true once url is fine  // Verifica i permessi col nuovo metodo nella classe Html
//                    );
                    return '';
//                    return $btn;
                },
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->renderFile($this->layout, [
            'widget' => $this
        ]);
    }
}