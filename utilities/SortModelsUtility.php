<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\utilities
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;

use open20\amos\core\exceptions\SortModelsException;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;
use yii\base\BaseObject;
use yii\log\Logger;

/**
 * Class SortModelsUtility
 * @package open20\amos\core\utilities
 */
class SortModelsUtility extends BaseObject
{
    const DIRECTION_UP = 1;
    const DIRECTION_DOWN = 2;
    
    /**
     * @var Record $model This is the model to be ordered.
     */
    public $model;
    
    /**
     * @var string $modelSortField This is the sort field of the model to be ordered.
     */
    public $modelSortField;
    
    /**
     * @var int $direction This property can be one of the two directions constants.
     */
    public $direction;
    
    /**
     * @var array orderList This array must contains all the elements ids in the actual sort field order.
     */
    public $orderList;
    
    /**
     * @throws SortModelsException
     */
    public function init()
    {
        parent::init();
        
        if (!$this->model) {
            throw new SortModelsException(BaseAmosModule::t('amoscore', '#SortModelsUtility_missing_model'));
        }
        
        if (!$this->modelSortField) {
            throw new SortModelsException(BaseAmosModule::t('amoscore', '#SortModelsUtility_missing_model_sort_field'));
        }
        
        if (!$this->direction) {
            throw new SortModelsException(BaseAmosModule::t('amoscore', '#SortModelsUtility_missing_sort_direction'));
        }
        
        if (!$this->orderList) {
            throw new SortModelsException(BaseAmosModule::t('amoscore', '#SortModelsUtility_missing_order_list'));
        }
        
        if (!in_array($this->direction, [self::DIRECTION_UP, self::DIRECTION_DOWN])) {
            throw new SortModelsException(BaseAmosModule::t('amoscore', '#SortModelsUtility_wrong_direction'));
        }
    }
    
    /**
     * This method sort the models accordingly with the direction provided.
     * @return bool
     */
    public function reorderModels()
    {
        // Find the element in the ids array...
        $indexElemToMove = array_search($this->model->id, $this->orderList);
        
        // ...and move it up or down
        if ($this->direction == self::DIRECTION_UP) {
            $this->orderList = $this->moveUp($this->orderList, $indexElemToMove);
        } elseif ($this->direction == self::DIRECTION_DOWN) {
            $this->orderList = $this->moveDown($this->orderList, $indexElemToMove);
        }
        
        // Save the models with the new order
        return $this->resetModelsOrder($this->orderList);
    }
    
    /**
     * Move the model one position up
     * @param array $array
     * @param int $x
     * @return array
     */
    protected function moveUp($array, $x)
    {
        if ($x > 0 and $x < count($array)) {
            $newArray = array_slice($array, 0, ($x - 1), true);
            $newArray[] = $array[$x];
            $newArray[] = $array[$x - 1];
            $newArray += array_slice($array, ($x + 1), count($array), true);
            return $newArray;
        } else {
            return $array;
        }
    }
    
    /**
     * Move the model one position down
     * @param array $array
     * @param int $x
     * @return array
     */
    protected function moveDown($array, $x)
    {
        if (count($array) - 1 > $x) {
            $newArray = array_slice($array, 0, $x, true);
            $newArray[] = $array[$x + 1];
            $newArray[] = $array[$x];
            $newArray += array_slice($array, $x + 2, count($array), true);
            return ($newArray);
        } else {
            return $array;
        }
    }
    
    /**
     * This method order the models by saving an incremental index in the sort attribute
     * by the elements provided in the model ids array.
     * @param int[] $modelIds
     */
    protected function resetModelsOrder($modelIds)
    {
        $i = 1;
        $model = $this->model;
        /** @var Record $modelClassName */
        $modelClassName = $model::className();
        $sortField = $this->modelSortField;
        $allOk = true;
        foreach ($modelIds as $id) {
            $attachment = $modelClassName::findOne($id);
            $attachment->{$sortField} = $i;
            $ok = $attachment->save(false);
            if (!$ok) {
                $allOk = false;
                \Yii::getLogger()->log('Errore salvataggio model ordinato', Logger::LEVEL_ERROR);
                break;
            }
            $i++;
        }
        return $allOk;
    }
}
