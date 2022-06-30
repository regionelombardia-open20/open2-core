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

use open20\amos\admin\AmosAdmin;
use yii\base\Widget;

/**
 * Class ShowUserTagsWidget
 * @package open20\amos\core\forms
 */
class ShowUserTagsWidget extends Widget
{
    public $layout = "@vendor/open20/amos-core/forms/views/widgets/widget_show_user_tags.php";
    protected $userProfile;
    protected $className;
    protected $userTagList;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $allTag = null;
        $this->userTagList = $this->getArrayTagsId();
        foreach ($this->userTagList as $tagId) {
            //recupera il tag
            $tag = $this->getTagById($tagId);

            //identifica il path di nodi parent
            if (!empty($tag)) {
                $pathParents = $tag->parents()->orderBy('lvl ASC')->all();

                $parentsPath = [];
                foreach ($pathParents as $padre) {
                    //esclude la root in quanto è già indicata
                    if ($padre->lvl != 0) {
                        $parentsPath[] = $padre->nome;
                    }
                }

                $allTag[$tag->id]['nome'] = $tag->nome;
                $allTag[$tag->id]['root'] = $tag->root;
                $allTag[$tag->id]['path'] = implode(" / ", $parentsPath);
            }
        }

        return $this->renderFile($this->getLayout(), [
            'allRootTags' => $this->getAllTagsRoots(),
            'allTags' => $allTag,
        ]);
    }

    protected function getArrayTagsId()
    {
        $userProfileClass = AmosAdmin::getInstance()->model('UserProfile');
        if($this->className == $userProfileClass){
            $listaTagId = \open20\amos\cwh\models\CwhTagOwnerInterestMm::find()
                ->innerJoin('tag', 'tag.id = tag_id')
                ->andWhere([
                    'record_id' => $this->userProfile,
                ])
                ->orderBy([
                    'tag.nome' => SORT_DESC
                ])->all();
        } else {
            $listaTagId = \open20\amos\tag\models\EntitysTagsMm::find()
                ->joinWith('tag')
                ->andWhere([
                    'classname' => $this->className,
                    'record_id' => $this->userProfile,
                ])
                ->orderBy([
                    'tag.nome' => SORT_DESC
                ])->all();
        }

        $ret = [];
        foreach ($listaTagId as $tag) {
            $ret[] = $tag->tag_id;
        }
        return $ret;
    }

    protected function getTagById($tagId)
    {
        return \open20\amos\tag\models\Tag::findOne($tagId);
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    protected function getAllTagsRoots()
    {
        $this->userTagList = $this->getArrayTagsId();
        $userProfileClass = AmosAdmin::getInstance()->model('UserProfile');
        if($this->className == $userProfileClass) {
            $tagIdList = \open20\amos\cwh\models\CwhTagOwnerInterestMm::find()
                ->andWhere([
//                    'classname' => $this->className,
                    'record_id' => $this->userProfile,
                ])
                ->groupBy('root_id')
                ->all();
        }else {
            $tagIdList = \open20\amos\tag\models\EntitysTagsMm::find()
                //->select('root_id')
                //->joinWith('tag')
                ->andWhere([
                    'classname' => $this->className,
                    'record_id' => $this->userProfile
                ])
                ->groupBy('root_id')
                ->asArray()
                ->all();
        }

        $tagRoots = [];
        foreach ($tagIdList as $tagRoot) {
            $tagRoots[] = $tagRoot['root_id'];
        }

        $tagIdList = \open20\amos\tag\models\Tag::find()
            ->andWhere([
                'id' => $tagRoots,
            ])
            ->orderBy(['nome' => SORT_DESC])
            ->asArray()
            ->all();

        $ret = [];
        foreach ($tagIdList as $tag) {
            $root = $this->getTagById($tag['id']);
            $ret[$tag['id']]['el'] = $root['nome'];
            $ret[$tag['id']]['level'] = $root['lvl'];
        }

        return $ret;
    }

    /**
     * @return mixed
     */
    public function getUserProfile()
    {
        return $this->userProfile;
    }

    /**
     * @param mixed $userProfile
     */
    public function setUserProfile($userProfile)
    {
        $this->userProfile = $userProfile;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param mixed $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }
}
