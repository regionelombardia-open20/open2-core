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

use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;
use open20\amos\core\utilities\SortModelsUtility;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

/**
 * Class SortModelsWidget
 * @package open20\amos\core\forms
 */
class SortModelsWidget extends Widget
{
    /**
     * @var string $layout Layout of the widget
     */
    public $layout = "{sortUp}{sortDown}";
    
    /**
     * @var Record $model
     */
    public $model;
    
    /**
     * @var string|array $urlSort
     */
    public $sortUrl;
    
    /**
     * @var string $sortPermissionToCheck If null no check of permission. If is a string, the string is used as a permission anc checked with the model as param.
     */
    public $sortPermissionToCheck = null;
    
    /**
     * @var bool $isFirst If true hide the up arrow.
     */
    public $isFirst = false;
    
    /**
     * @var bool $isLast If true hide the down arrow.
     */
    public $isLast = false;
    
    /**
     * @var array $optionsUp
     */
    public $optionsUp = [];
    
    /**
     * @var array $optionsDown
     */
    public $optionsDown = [];
    
    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }
    
    /**
     * @inheridoc
     */
    public function run()
    {
        if (!$this->areToShowSortButtons()) {
            return '';
        }
        
        $content = preg_replace_callback("/{\\w+}/",
            function ($matches) {
                $content = $this->renderSection($matches[0]);
                
                return $content === false ? $matches[0] : $content;
            }, $this->layout
        );
        
        return $content;
    }
    
    /**
     * This method checks if there's a permission to check. If there's no permission it returns directly true;
     * If there's a permission it checks the permission with the widget model as a param.
     * @return bool
     */
    protected function areToShowSortButtons()
    {
        if (is_null($this->sortPermissionToCheck)) {
            return true;
        }
        return Yii::$app->user->can($this->sortPermissionToCheck, ['model' => $this->model]);
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
            case '{sortUp}':
                return $this->renderButtonUp();
            case '{sortDown}':
                return $this->renderButtonDown();
            default:
                return false;
        }
    }
    
    /**
     * This method render the up arrow button.
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function renderButtonUp()
    {
        if ($this->isFirst) {
            return '';
        }
        
        $buttonText = AmosIcons::show('long-arrow-up');
        
        if (is_array($this->sortUrl)) {
            $btnUrl = $this->sortUrl;
            $btnUrl['id'] = $this->model->id;
            $btnUrl['direction'] = SortModelsUtility::DIRECTION_UP;
        } else {
            $btnUrl = [
                $this->sortUrl,
                'id' => $this->model->id,
                'direction' => SortModelsUtility::DIRECTION_UP
            ];
        }
        
        $options = ArrayHelper::merge([
            'title' => BaseAmosModule::t('amoscore', '#model_list_move_up_title'),
            'data-confirm' => BaseAmosModule::t('amoscore', '#model_list_move_up_data_confirm'),
            'class' => 'btn btn-tools-secondary'
        ], $this->optionsUp);
        
        return Html::a($buttonText, $btnUrl, $options);
    }
    
    /**
     * This method render the down arrow button.
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function renderButtonDown()
    {
        if ($this->isLast) {
            return '';
        }
        
        $buttonText = AmosIcons::show('long-arrow-down');
        
        if (is_array($this->sortUrl)) {
            $btnUrl = $this->sortUrl;
            $btnUrl['id'] = $this->model->id;
            $btnUrl['direction'] = SortModelsUtility::DIRECTION_DOWN;
        } else {
            $btnUrl = [
                $this->sortUrl,
                'id' => $this->model->id,
                'direction' => SortModelsUtility::DIRECTION_DOWN
            ];
        }
        
        $options = ArrayHelper::merge([
            'title' => BaseAmosModule::t('amoscore', '#model_list_move_down_title'),
            'data-confirm' => BaseAmosModule::t('amoscore', '#model_list_move_down_data_confirm'),
            'class' => 'btn btn-tools-secondary'
        ], $this->optionsDown);
        
        return Html::a($buttonText, $btnUrl, $options);
    }
}
