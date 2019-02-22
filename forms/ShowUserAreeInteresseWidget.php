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

use Yii;
use lispa\amos\tag\models\Tag;
use lispa\amos\tag\models\TagModelsAuthItemsMm;
use yii\base\Widget;

class ShowUserAreeInteresseWidget extends Widget
{
    public $layout = "@vendor/lispa/amos-core/forms/views/widgets/widget_show_user_areeinteresse.php";
    private $userProfile;
    private $className;

    public function run()
    {
        $allTag = null;
        $userTagList = $this->getArrayTagsId();
        foreach ($userTagList as $tagInfo) {
            //identifica i dati del tag
            $tagId = $tagInfo['id'];
            $tagInterestClassname = $tagInfo['interest_classname'];

            //recupera il tag
            $tag = $this->getTagById($tagId);

            //identifica il path di nodi parent
            if($tag != null) {
                $pathParents = $tag->parents()->orderBy('lvl ASC')->all();
                $parentsPath = [];
                foreach ($pathParents as $padre) {
                    //esclude la root in quanto è già indicata
                    if ($padre->lvl != 0) {
                        $parentsPath[] = $padre->nome;
                    }
                }

                //costruisce l'array con le info del tag
                $tmpTag = [
                    "id" => $tag->id,
                    "nome" => $tag->nome,
                    "interest_classname" => $tagInterestClassname,
                    "root" => $tag->root,
                    "path" => implode(" / ", $parentsPath),
                ];

                $allTag[] = $tmpTag;
            }
        }

        return $this->renderFile($this->getLayout(), [
            'allRootTags' => $this->getAllTagsRoots(),
            'allTags' => $allTag,
        ]);
    }

    private function getArrayTagsId()
    {
        $listaTagId = \lispa\amos\cwh\models\CwhTagOwnerInterestMm::findAll([
            'classname' => $this->className,
            'record_id' => $this->userProfile,
        ]);
        $ret = [];
        foreach ($listaTagId as $tag) {
            $ret[] = [
                "id" => $tag->tag_id,
                "interest_classname" => $tag->interest_classname
            ];
        }
        return $ret;
    }

    private function getTagById($tagId)
    {
        return \lispa\amos\tag\models\Tag::findOne($tagId);
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    private function getAllTagsRoots()
    {
        $id_user = null;
        if (Yii::$app->getModule('admin')->modelMap['UserProfile'] == $this->className) {
            $id_user = $this->userProfile;
        }
        else {
            $id_user = \Yii::$app->getUser()->getId();
        }

        //identifica le root degli alberi per cui l'utente ha selezionato tags
        $tagIdList = \lispa\amos\cwh\models\CwhTagOwnerInterestMm::find()
            ->andWhere([
                'classname' => $this->className,
                'record_id' => $id_user
            ])
            ->groupBy('root_id')
            ->all();

        $ids_root = [];
        foreach($tagIdList as $tag){
            $ids_root[] = $tag->root_id;
        }

        //se ha identificato delle root
        $contentsTrees = [];
        if(count($ids_root)){
            $contents = \Yii::$app->getModule('cwh')->modelsEnabled;

            foreach ($contents as $content) {
                $refClass = new \ReflectionClass($content);

                //query di recupero dei tags
                $query = Tag::find()
                    ->joinWith('cwhTagInterestMm')
                    ->joinWith('tagModelsAuthItems')
                    ->andWhere(['in', 'id', $ids_root])
                    ->andWhere([
                        Tag::tableName() . '.lvl' => 0,
                        TagModelsAuthItemsMm::tableName() . '.classname' => $content,
                        \lispa\amos\cwh\models\CwhTagInterestMm::tableName() . '.classname' => $this->className,
                        \lispa\amos\cwh\models\CwhTagInterestMm::tableName() . '.auth_item' => array_keys(\Yii::$app->authManager->getRolesByUser($id_user))
                    ])
                ;

                if ($query->count()) {
                    $contentsTree['label'] = $refClass->getShortName();
                    $contentsTree['classnameRef'] = $refClass->getShortName();
                    $contentsTree['classname'] = $content;
                    $contentsTree['trees'] = $query->asArray()->all();
                    $contentsTrees[] = $contentsTree;
                }
            }
        }

        return $contentsTrees;
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