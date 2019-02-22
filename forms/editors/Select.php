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

use lispa\amos\core\module\BaseAmosModule;

/**
 * Class Select
 * @package lispa\amos\core\forms\editors
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
    }
}