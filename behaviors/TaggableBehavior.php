<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\behaviors
 * @category   CategoryName
 */

namespace lispa\amos\core\behaviors;

use creocoder\taggable\TaggableBehavior as YiiTaggable;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\views\toolbars\StatsToolbarPanels;
use lispa\amos\tag\models\EntitysTagsMm;
use lispa\amos\tag\models\Tag;
use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Expression;

/**
 * Class TaggableBehavior
 * @package lispa\amos\core\behaviors
 */
class TaggableBehavior extends YiiTaggable
{
    /**
     * @var string separator for tags
     */
    public $tagValuesSeparatorAttribute = ',';
    public $tagValueNameAttribute = '';

    /**
     * @var string[]
     */
    private $_tagValues;
    private $pivot;
    private $focusRoot = null;

    public function __construct()
    {
        parent::__construct();
        $this->tagValueAttribute = 'id';
        $this->pivot = EntitysTagsMm::className();
        $this->tagFrequencyAttribute = false;
    }

    public function setFocusRoot($root)
    {
        $this->focusRoot = $root;
    }


    /**
     * @param string|\string[] $values
     */
    public function setTagValues($values)
    {
        $this->_tagValues = $values;
    }

    /**
     * @var TaggableBehavior $this
     * Returns tags.
     * @param boolean|null $asArray
     * @return string|string[]
     */
    public function getTagValues($asArray = null)
    {
        if (!$this->owner->getIsNewRecord()) { //&& $this->_tagValues === null
            $this->_tagValues = [];

            /** @var ActiveRecord $pivot */
            $pivot = $this->pivot;
            if ($this->focusRoot) {
                $list = $pivot::findAll(['classname' => get_class($this->owner), 'record_id' => $this->owner->getPrimaryKey(), 'root_id' => $this->focusRoot]);
            } else {
                $list = $pivot::findAll(['classname' => get_class($this->owner), 'record_id' => $this->owner->getPrimaryKey()]);
            }

            foreach ($list as $record) {
                /** @var EntitysTagsMm $record */
                $this->_tagValues[] = $record->tag_id;
            }
        }

        if ($asArray === null) {
            $asArray = $this->tagValuesAsArray;
        }

        if ($asArray) {
            return $this->_tagValues === null ? [] : $this->_tagValues;
        } else {
            return $this->_tagValues === null ? '' : implode($this->tagValuesSeparatorAttribute, $this->_tagValues);
        }
    }

    /**
     * @return array
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_FIND => 'eventFind',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'eventSave',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'eventSave',
            BaseActiveRecord::EVENT_BEFORE_VALIDATE => 'eventBeforeValidate',
        ];
    }

    /**
     * Yet implemented
     */
    public function eventFind()
    {

    }

    /**
     * Yet implemented
     */
    public function eventBeforeValidate()
    {

    }

    /**
     *
     */
    public function eventSave()
    {

        if ((isset($_POST[$this->owner->formName()]) && isset($_POST[$this->owner->formName()]['tagValues']))) {
            $this->_tagValues = $_POST[$this->owner->formName()]['tagValues'];
        }else
        {
            if($this->_tagValues === null)
            {
                return;
            }
        }


        if ($this->_tagValues !== null) {
            if (!$this->owner->getIsNewRecord()) {
                $this->beforeDelete();
            }

            /** @var Tag $class */
            $class = Tag::className();
            $rows = [];

            $user = Yii::$app->get('user', false);
            $timestamp = date('Y-m-d H:i:s');
            $userId = $user && !$user->isGuest ? $user->id : null;

            foreach ($this->_tagValues as $root => $id_tags) {
                $array_tags = $this->filterTagValues($id_tags);
                foreach ($array_tags as $id_tag) {
                    if ($this->tagFrequencyAttribute !== false) {
                        /** @var Tag $tag */
                        $tag = $class::findOne([$this->tagValueAttribute => $id_tag]);
                        if ($tag === null) {
                            $tag = new $class();
                            $tag->setAttribute($this->tagValueAttribute, null);
                        }
                        $frequency = $tag->getAttribute($this->tagFrequencyAttribute);
                        $tag->setAttribute($this->tagFrequencyAttribute, ++$frequency);
                        $tag->save();
                    }
                    $rows[] = [get_class($this->owner), $this->owner->getPrimaryKey(), $id_tag, $root, $timestamp, $timestamp, $userId, $userId];

                }
            }

            if (!empty($rows)) {
                /** @var ActiveRecord $pivot */
                $pivot = $this->pivot;
                $this->owner->getDb()
                    ->createCommand()
                    ->batchInsert($pivot::tableName(), ['classname', 'record_id', 'tag_id', 'root_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], $rows)
                    ->execute();
            }
        }
    }

    /**
     * @return void
     */
    public function beforeDelete()
    {
        /** @var ActiveRecord $pivot */
        $pivot = $this->pivot;
        if ($this->tagFrequencyAttribute !== false) {
            $list = $pivot::findAll(['classname' => get_class($this->owner), 'record_id' => $this->owner->getPrimaryKey()]);
            $class = Tag::className();
            foreach ($list as $record) {
                /** @var EntitysTagsMm $record */
                /** @var ActiveRecord $class */
                $tag = $class::findOne([$this->tagValueAttribute => $record->tag_id]);
                if ($tag) {
                    $frequency = $tag->getAttribute($this->tagFrequencyAttribute);
                    $tag->setAttribute($this->tagFrequencyAttribute, --$frequency);
                    $tag->save();

                }
            }
        }
        $user = Yii::$app->get('user', false);
        $timestamp =date('Y-m-d H:i:s');
        $userId = $user && !$user->isGuest ? $user->id : null;
        $connection = $this->owner->getDb();
        $connection->createCommand()->update($pivot::tableName(), ['deleted_at' => $timestamp, 'deleted_by' => $userId], ['classname' => get_class($this->owner), 'record_id' => $this->owner->getPrimaryKey()])->execute();
    }

    /**
     * @return array
     */
    public function getStatsToolbar($disableLink = false)
    {
        return StatsToolbarPanels::getTagsPanel( $this->owner,count($this->getTagValues(true)),$disableLink);

    }
}
