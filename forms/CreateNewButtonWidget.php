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

use lispa\amos\core\helpers\Html;
use lispa\amos\core\record\Record;
use Yii;
use yii\base\Widget;

/**
 * Class CreateNewButtonWidget
 * Renders the "create new" button also according to the permissions that the user has.
 *
 * @package lispa\amos\core\forms
 */
class CreateNewButtonWidget extends Widget
{
    public $layout = "{buttonCreateNew}";
    
    /**
     * @var string $createNewBtnLabel Label for create button. Default to "Crea nuovo".
     */
    private $createNewBtnLabel;
    
    /**
     * @var array $urlCreateNew Create button default action
     */
    private $urlCreateNew = ['create'];
    
    /**
     * @var bool $checkPermWithNewMethod If true the Html::a check permissions with new method.
     */
    private $checkPermWithNewMethod = false;
    
    /**
     * @var string $createButtonId
     */
    private $createButtonId = '';
    
    /**
     * @var Record $model
     */
    private $model = null;
    
    /**
     * @var string $btnClasses
     */
    private $btnClasses = null;
    
    /**
     * @var string $otherBtnClasses
     */
    private $otherBtnClasses = null;
    
    /**
     * @var array|null
     */
    private $otherOptions = null;
    
    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }
    
    public function run()
    {
        if (!isset($this->createNewBtnLabel)) {
            $this->createNewBtnLabel = Yii::t('amoscore', 'Crea nuovo');
        }
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
    public function renderSection($name)
    {
        switch ($name) {
            case '{buttonCreateNew}':
                return $this->renderButtonCreate();
            default:
                return false;
        }
    }
    
    public function renderButtonCreate()
    {
        return Html::a(
            $this->getCreateNewBtnLabel(),      // Text
            $this->getUrlCreateNew(),           // Url
            $this->configCreateButtonOptions(), // Options array
            $this->getCheckPermWithNewMethod()  // Set true if you want to check permission with new method
        );
    }
    
    private function configCreateButtonOptions()
    {
        $options = [
            'class' => 'btn btn-administration-primary',
        ];
        if (strlen($this->getCreateButtonId())) {
            $options['id'] = $this->getCreateButtonId();
        }
        if (isset($this->model)) {
            $options['model'] = $this->getModel();
        }
        if (isset($this->btnClasses) && is_string($this->getBtnClasses())) {
            $options['class'] = $this->getBtnClasses();
        }
        if (isset($this->otherBtnClasses) && is_string($this->getOtherBtnClasses())) {
            $options['class'] .= ' ' . $this->getOtherBtnClasses();
        }
        if (isset($this->otherOptions) && is_array($this->otherOptions)) {
            foreach ($this->otherOptions as $otherOptionName => $otherOptionValue) {
                $options[$otherOptionName] = $otherOptionValue;
            }
        }
        return $options;
    }
    
    /**
     * @return mixed
     */
    public function getUrlCreateNew()
    {
        return $this->urlCreateNew;
    }
    
    /**
     * @param mixed $urlCreateNew
     */
    public function setUrlCreateNew($urlCreateNew)
    {
        $this->urlCreateNew = $urlCreateNew;
    }
    
    /**
     * @return string
     */
    public function getCreateNewBtnLabel()
    {
        return $this->createNewBtnLabel;
    }
    
    /**
     * @param string $createNewBtnLabel
     */
    public function setCreateNewBtnLabel($createNewBtnLabel)
    {
        $this->createNewBtnLabel = $createNewBtnLabel;
    }
    
    /**
     * @return boolean
     */
    public function getCheckPermWithNewMethod()
    {
        return $this->checkPermWithNewMethod;
    }
    
    /**
     * @param boolean $checkPermWithNewMethod
     */
    public function setCheckPermWithNewMethod($checkPermWithNewMethod)
    {
        $this->checkPermWithNewMethod = $checkPermWithNewMethod;
    }
    
    /**
     * @return string
     */
    public function getCreateButtonId()
    {
        return $this->createButtonId;
    }
    
    /**
     * @param string $createButtonId
     */
    public function setCreateButtonId($createButtonId)
    {
        $this->createButtonId = $createButtonId;
    }
    
    /**
     * @return Record
     */
    public function getModel()
    {
        return $this->model;
    }
    
    /**
     * @param Record $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }
    
    /**
     * @return string
     */
    public function getBtnClasses()
    {
        return $this->btnClasses;
    }
    
    /**
     * @param string $btnClasses
     */
    public function setBtnClasses($btnClasses)
    {
        $this->btnClasses = $btnClasses;
    }
    
    /**
     * @return string
     */
    public function getOtherBtnClasses()
    {
        return $this->otherBtnClasses;
    }
    
    /**
     * @param string $otherBtnClasses
     */
    public function setOtherBtnClasses($otherBtnClasses)
    {
        $this->otherBtnClasses = $otherBtnClasses;
    }
    
    /**
     * @return array|null
     */
    public function getOtherOptions()
    {
        return $this->otherOptions;
    }
    
    /**
     * @param array $otherOptions
     */
    public function setOtherOptions($otherOptions)
    {
        $this->otherOptions = $otherOptions;
    }
}
