<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\editors
 * @category   CategoryName
 */

namespace open20\amos\core\forms\editors;

use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\helpers\Html;
use Yii;

/**
 * Class Select
 * @package open20\amos\core\forms\editors
 */
class Select extends \kartik\select2\Select2
{
    public $auto_fill = false;
    public $boolean = false;

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();

        /**
         * controlli di coerenza
         */
        if(is_null($this->name)) {
            if (is_null($this->model)) {
                throw new \Exception(BaseAmosModule::t('amoscore', 'Model mancante'));
            }
            if (is_null($this->attribute)) {
                throw new \Exception(BaseAmosModule::t('amoscore', 'Attributo mancante'));
            }
        }

        if($this->boolean){
            $this->data = [
                0 => BaseAmosModule::t('amoscore', 'No'),
                1 => BaseAmosModule::t('amoscore', 'Sì')
            ];
        }

        /**
         * se viene passato l'opzione auto_fill procedo a forzare la selezione
         */
        if ($this->auto_fill) {
            /**
             * il numero di elementi della select deve essere 1
             */
            if (sizeof($this->data) == 1) {
                /**
                 * se il campo è obbligatorio proseguiamo
                 */
                if ($this->model->isAttributeRequired($this->attribute)) {
                    /**
                     * nascondo la barra di ricerca di select2
                     */
                    $this->hideSearch = true;
                    /**
                     * forzo la selezione sull'unico valore disponibile
                     */
                    $ids = array_keys($this->data);
                    $this->model->{$this->attribute} = $ids[0];
                }
            }
        }

        $this->renderToggleAll();
    }

    protected function renderToggleAll()
   {
       if (!$this->options['multiple'] || !$this->showToggleAll) {
           return;
       }
       $settings = array_replace_recursive([
           'selectLabel' => '<i class="glyphicon glyphicon-unchecked"></i>' . BaseAmosModule::t('amoscore', 'Select All'),
           'unselectLabel' => '<i class="glyphicon glyphicon-check"></i>' . BaseAmosModule::t('amoscore', 'Unselect All'),
           'selectOptions' => [],
           'unselectOptions' => [],
           'options' => ['class' => 's2-togall-button']
       ], $this->toggleAllSettings);
       $sOptions = $settings['selectOptions'];
       $uOptions = $settings['unselectOptions'];
       $options = $settings['options'];
       $prefix = 's2-togall-';
       Html::addCssClass($options, "{$prefix}select");
       Html::addCssClass($sOptions, "s2-select-label");
       Html::addCssClass($uOptions, "s2-unselect-label");
       $options['id'] = $prefix . $this->options['id'];
       $labels = Html::tag('span', $settings['selectLabel'], $sOptions) .
           Html::tag('span', $settings['unselectLabel'], $uOptions);
       $out = Html::tag('span', $labels, $options);
       echo Html::tag('span', $out, ['id' => 'parent-' . $options['id'], 'style' => 'display:none']);
   }

}
