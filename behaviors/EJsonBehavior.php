<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\behaviors
 * @category   CategoryName
 */

namespace open20\amos\core\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * Class EJsonBehavior
 * @package open20\amos\core\behaviors
 */
class EJsonBehavior extends Behavior
{
    /**
     * Get related attributes in JSON format
     * @return string
     */
    public function toJSON()
	{
		$jsonDataSource = $this->getRelated($this->owner);

		return Json::encode($jsonDataSource);
	}

    /**
     * Get related attributes in array format
     * @param $record
     * @return array
     */
	private function getRelated($record)
	{
		$related = array();
		$obj = null;

		$attributes = $record->getAttributes();

		$related['record'] = get_class($record);
		$related['attributes'] = $attributes;
		$related['relations'] = array();

		$relations = $record->getRelatedRecords();

		foreach ($relations as $name => $relation) {
			if(is_array($relation)) {
				foreach($relation as $single) {
					$related['relations'][] = $this->getRelated($single);
				}
			} else {
				$related['relations'][] = $this->getRelated($relation);
			}
		}

		return $related;
	}
}