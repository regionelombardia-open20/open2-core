<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\components
 * @category   CategoryName
 */

namespace lispa\amos\core\components;

use Yii;

/**
 * Class PartQuestionarioAbstract
 * @package lispa\amos\core\components
 */
abstract class PartQuestionarioAbstract extends \yii\base\Component
{
    const STATUS_COMPLETED = 'completed';
    const STATUS_COMPLETED_LABEL = 'Completato';
    const STATUS_CURRENT = 'warning';
    const STATUS_CURRENT_LABEL = 'In Esame';
    const STATUS_ERROR = 'danger';
    const STATUS_ERROR_LABEL = 'Con errori';
    const STATUS_COMPILED = '';
    const STATUS_UNDONE = '';
    const STATUS_UNDONE_LABEL = 'Da completare';

    public static $map;
    public static $HAS_ERROR = false;
    public $domanda;
    public $active;
    public $activeChild;
    public $current;
    public $currentChild;

    public function init()
    {
        parent::init();

        $this->current = \Yii::$app->controller->action->id;

        $this->initMap();

        $childMap = $this->getChildMap();

        if ($this->current && array_key_exists($this->current, self::$map)) {
            $this->active = self::$map[$this->current];
        } elseif ($this->current && array_key_exists($this->current, $childMap)) {
            $this->activeChild = $childMap[$this->current];
            $this->active = self::$map[$childMap[$this->current]['parent']];
            $this->currentChild = $this->current;
            $this->current = $childMap[$this->current]['parent'];
        }

        $this->initMap();
    }

    protected abstract function initMap();

    public function getChildMap($parent = null)
    {
        $childrens = [];

        if ($parent) {
            if (array_key_exists('children', self::$map[$parent])) {
                foreach (self::$map[$parent]['children'] as $k => $child) {
                    $childrens[$k] = $child;
                }
            }
        } else {
            foreach (self::$map as $item) {
                if (array_key_exists('children', $item)) {
                    foreach ($item['children'] as $k => $child) {
                        $childrens[$k] = $child;
                    }
                }
            }
        }

        return $childrens;
    }

    public function createUrl($params)
    {
        $controller = Yii::$app->controller->id;
        $module = Yii::$app->controller->module->id;

        if ($this->isCompleted($params[0])) {
            $params[0] = $module . '/' . $controller . '/' . $params[0];
            return \Yii::$app->getUrlManager()->createUrl($params);
        }

        return null;
    }

    protected abstract function isCompleted($part);

    public function getStatus($part)
    {
        if ($part == $this->current || $part == $this->currentChild) {
            if (Yii::$app->getRequest()->getIsPost() && !$this->isCompleted($part)) {
                self::$HAS_ERROR = true;
                return self::STATUS_ERROR;
            }
            return self::STATUS_CURRENT;
        } elseif ($this->isCompleted($part)) {
            return self::STATUS_COMPLETED;
        }
        return self::STATUS_UNDONE;
    }
    
    /**
     * Check whether a part is post to the actual current part.
     * This method suppose that the index in the map array is numeric.
     * @param string $part The part index
     * @return bool
     */
    public function partIsPostCurrent($part)
    {
        if (!self::$map) {
            return false;
        }
        return (self::$map[$part]['index'] > self::$map[$this->current]['index']);
    }

    public function getTitle($part)
    {
        if ($part == $this->current || $part == $this->currentChild) {
            if (!$this->isCompleted($part)) {
                return self::STATUS_ERROR_LABEL;
            }
            return self::STATUS_CURRENT_LABEL;
        } elseif ($this->isCompleted($part)) {
            return self::STATUS_COMPLETED_LABEL;
        }

        return self::STATUS_UNDONE_LABEL;
    }

    public function getNext()
    {
        if ($this->isCompleted($this->current) || $this->isCompleted($this->currentChild)) {
            $keys = array_keys(self::$map);
            $childMap = array_keys($this->getChildMap($this->current));
            if ($this->current) {
                $position = array_search($this->current, $keys);
                $positionChild = array_search($this->currentChild, $childMap);

                if ($this->current && $this->currentChild && isset($positionChild) && isset($childMap[$positionChild + 1])) {
                    return $childMap[$positionChild + 1];
                }

                if (isset($position) && isset($keys[$position + 1])) {
                    return $keys[$position + 1];
                }
            }
            return $keys[0];
        } else {

            if ($this->currentChild) {
                return $this->currentChild;
            }
            return $this->current;
        }
    }

    public function getParts()
    {
        return self::$map;
    }

    public function getSubParts()
    {
        $parts = [];
        if ($this->current && array_key_exists($this->current, self::$map) && array_key_exists('children', self::$map[$this->current])) {
            $parts = self::$map[$this->current]['children'];
        }
        return $parts;
    }
}
