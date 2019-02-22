<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\common
 * @category   CategoryName
 */

namespace lispa\amos\core\views\common;

use Yii;
use lajax\translatemanager\models\Language;
use lajax\translatemanager\widgets\ToggleTranslate;

class HeaderMenu
{

    /**
     * Return the array will add to header menu
     * @return array
     */
    public function getTranslationField()
    {
        try {
            $voceMenu = [];
            if (\Yii::$app->getModule('translatemanager') && isset(Yii::$app->params['languageSelector']) && Yii::$app->params['languageSelector'] && isset(Yii::$app->language)) {
                $translateManager = new Language();
                $table = $translateManager->getTableSchema()->name;

                $posLang = strpos(Yii::$app->language, '-');
                $labelLang = strtoupper(substr(Yii::$app->language, 0, $posLang));

                $activeLanguage = $this->getActiveLanguages($table);
                $languages = (!empty($activeLanguage) ? $activeLanguage : []); //TODO al momento è così poi sistemiamo con il plugin
                if (!empty($languages)) {
                    $arrayLang = ['<li class="dropdown-header">' . Yii::t('amoscore', 'Seleziona la lingua') . '</li>',
                        '<li class="divider"></li>'];
                    foreach ($languages as $Lang) {
                        $arrayLang[] = [
                            'label' => $Lang['name'],
                            'url' => (!empty(\Yii::$app->getModule('translation')->actionLanguage) ? [\Yii::$app->getModule('translation')->actionLanguage] : ['/site/language']),
                            'linkOptions' => ['data-method' => 'post', 'data-params' => ['language' => $Lang['language_id'], 'url' => \yii\helpers\Url::current()]],
                        ];
                    }
                }
                if (Yii::$app->getUser()->can('TRANSLATION_ADMINISTRATOR')) {
                    $arrayLang[] = (!empty($languages)) ? '<li class="divider"></li>' : '';
                    $arrayLang[] = '<li class="dropdown-header">' . Yii::t('amoscore', 'Amministra le traduzioni') . '</li>';
                    $arrayLang[] = '<li class="divider"></li>';
                    $arrayLang[] = [
                        'label' => Yii::t('amoscore', 'Lista delle lingue'),
                        'url' => ['/translatemanager/language/list'],
                    ];
                    $arrayLang[] = [
                        'label' => Yii::t('amoscore', 'Crea una nuova lingua'),
                        'url' => ['/translatemanager/language/create'],
                    ];
                    $arrayLang[] = [
                        'label' => Yii::t('amoscore', 'Fai una scansione'),
                        'url' => ['/translatemanager/language/scan'],
                    ];
                    $arrayLang[] = [
                        'label' => Yii::t('amoscore', 'Ottimizza le tabelle'),
                        'url' => ['/translatemanager/language/optimizer'],
                    ];
                }

                $voceMenu = [
                    'label' => '<p>' . $labelLang . '</p>',
                    'items' => $arrayLang,
                    'options' => ['class' => 'user-menu'],
                    'linkOptions' => ['title' => 'azioni utente']
                ];
            }

            return $voceMenu;
        } catch (\Exception $e) {
            return NULL;
        }
    }

    /**
     * Echo translate button
     */
    public function getToggleTranslate($addClass = 'btn btn-secondary')
    {
        if (!\Yii::$app->user->isGuest) {

            if (Yii::$app->getUser()->can('TRANSLATION_ADMINISTRATOR')) {
                echo ToggleTranslate::widget([
                    'template' => '<a href="javascript:void(0);" id="toggle-translate" class="{position} ' . $addClass . '" data-language="{language}" data-url="{url}" style="z-index:10000;"> ' . Yii::t('amoscore', 'Traduzioni in linea') . '</a><div id="translate-manager-div"></div>',
                    'position' => ToggleTranslate::POSITION_TOP_LEFT,
                ]);
            }
        }
    }

    /**
     * Return array of languages
     * @param string $table
     * @return array
     */
    protected function getActiveLanguages($table)
    {
        try {
            $language = new \lajax\translatemanager\models\Language;
            $arrayLang = [];

            if (Yii::$app->db->schema->getTableSchema($table, true) != null) {
                $arrayLang = (new \yii\db\Query())->from($table)->andWhere(['status' => 1])->select(['language_id', 'name'])->all();
            }
            return $arrayLang;
        } catch (\Exception $e) {
            return NULL;
        }
    }

    /**
     * Return array of custom menu
     * @return string
     */
    public function getCustomContent()
    {
        try {
            //TO-DO GESTIONE DI ARRAY DI CONFIGURAZIONI
            if (isset(\Yii::$app->params['headerCustomContent'])) {
                $arrItems = [];
                $menu = [];
                if (isset(\Yii::$app->params['headerCustomContent']['class'])) {
                    $headerCustomContentClass = \Yii::$app->params['headerCustomContent']['class'];
                    $class = new $headerCustomContentClass;
                    $method = (isset(\Yii::$app->params['headerCustomContent']['class']['method'])) ? \Yii::$app->params['headerCustomContent']['class']['method'] : 'getHeaderCustomContent';
                    if (method_exists($class, $method)) {
                        $arrItems[] = $class->getHeaderCustomContent();
                        foreach ($arrItems as $value) {
                            if (is_array($value)) {
                                $menu['label'] = '<p style="margin: 6px 0 0 0;">' . ((isset($value['label'])) ? $value['label'] : 'Menu custom') . '</p>';
                                if (isset($value['items'])) {
                                    foreach ($value['items'] as $item)
                                        $menu['items'][] = $item;
                                }
                            } else {
                                $menu['label'] = '<p style="margin: 6px 0 0 0">' . $value . '</p>';
                            }
                        }
                    }
                }
                return $menu;
            }
            return NULL;
        } catch (\Exception $e) {
            return NULL;
        }
    }

    /**
     *
     */
    public function getListLanguages()
    {
        $translateManager = new Language();
        $table = $translateManager->getTableSchema()->name;
        $activeLanguage = $this->getActiveLanguages($table);
        $languages = (!empty($activeLanguage) ? $activeLanguage : []); //TODO al momento è così poi sistemiamo con il plugin
        if (!empty($languages)) {
            $stringLang = '<div class="dropdown">' .
                \lispa\amos\core\helpers\Html::a(
                    Yii::t('amoscore', '#select_language') . \lispa\amos\core\icons\AmosIcons::show('chevron-down', ['title' => Yii::t('amosadmin', '#select_language')]),
                    '#',
                   ['class' => 'dropdown-toggle','data-toggle' => 'dropdown']) .
                '<ul class="dropdown-menu">';
            foreach ($languages as $Lang) {
                $stringLang = $stringLang . '<li>' .
                    \lispa\amos\core\helpers\Html::a(
                        $Lang['name'],
                        (!empty(\Yii::$app->getModule('translation')->actionLanguage) ? [\Yii::$app->getModule('translation')->actionLanguage] : ['/site/language']),
                        ['data-method' => 'post', 'data-params' => ['language' => $Lang['language_id'], 'url' => \yii\helpers\Url::current()]]
                    ) . '</li>';
            }
            $stringLang = $stringLang . '</ul></div>';
        }
        return $stringLang;
    }

}
