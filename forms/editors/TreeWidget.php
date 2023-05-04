<?php

namespace open20\amos\core\forms\editors;


use open20\amos\core\forms\editors\assets\TreeViewAsset;

class TreeWidget extends \yii\base\Widget
{
    /**
     * @var
     *
     *  $treeStructure = [
            1 => ['parent_id' => null, 'title' => 'Italia', 'description' => '.....' , 'link' => ['/news/news/view','id' => 1234]],
            2 => ['parent_id' => 1, 'title' => 'Sicilia', 'description' => '.....', 'link' => ['/news/news/view','id' => 1234],'model' => $model],
            3 => ['parent_id' => 1, 'title' => 'Lombardia', 'description' => '.....', 'link' => ['/news/news/view','id' => 1234]],
            4 => ['parent_id' => 1, 'title' => 'Emilia romagna', 'description' => '.....','link' => ['/news/news/view','id' => 1234]],
            5 => ['parent_id' => 2, 'title' => 'Trapani', 'description' => '.....','link' => ['/news/news/view','id' => 1234]],
            6 => ['parent_id' => 2, 'title' => 'Palermo', 'description' => '.....','link' => ['/news/news/view','id' => 1234]],
            7 => ['parent_id' => 5, 'title' => 'Marsala', 'description' => '.....','link' => ['/news/news/view','id' => 1234]],
    ]
     *
     *  se vuoi usare il model è consigliabile sovrascrivere la vista e passare i model al treetructures
     *   *
     *  $treeStructure = [
            1 => ['parent_id' => null, 'title' => 'Italia', 'description' => '.....', 'model' => $model1],
            2 => ['parent_id' => 1, 'title' => 'Sicilia', 'description' => '.....', 'model' => $model2],
            3 => ['parent_id' => 1, 'title' => 'Lombardia', 'description' => '.....', 'model' => $model3],
            4 => ['parent_id' => 1, 'title' => 'Emilia romagna', 'description' => '.....', 'model' => $model4],
            5 => ['parent_id' => 2, 'title' => 'Trapani', 'description' => '.....','model' => $model5],
            6 => ['parent_id' => 2, 'title' => 'Palermo', 'description' => '.....','model' => $model6],
            7 => ['parent_id' => 5, 'title' => 'Marsala', 'description' => '.....','model' => $model7],
    ]
     *
     *
     */
    public $treeStructure;
    public $itemView = '_item_tree';
    public $id;
    public $viewParams = [];


    /**
     *  echo \open20\amos\core\forms\editors\TreeWidget::widget([
                'treeStructure' => $treeStructure,
                'viewParams' => [
                    'exampleParam' => $exampleParams
                ]
    ]);
     */
    public function init()
    {
        parent::init();
        if(empty($this->id)){
            $this->id = $this->getId();
        }
    }


    /**
     * @return mixed
     */
    public function run()
    {
        TreeViewAsset::register($this->view);
        return $this->renderTree($this->treeStructure);
    }



    /**
     * @param $tree
     * @param null $root
     */
    public function renderTree($tree, $root = null)
    {
        $res = $this->parseTree($tree, $root);
        echo '<div id='.$this->id.' class="tree-core-widget">';
            $this->viewTree($res, $root, $root);
        echo '</div>';
    }

    /**
     * @param $tree
     * @param null $root
     * @return array|null
     */
    public function parseTree($tree, $root = null)
    {
        $return = [];
        # Traverse the tree and search for direct children of the root
        foreach ($tree as $child => $parent) {
            # A direct child is found
            if ($parent['parent_id'] == $root) {
                # Remove item from tree (we don't need to traverse this again)
                unset($tree[$child]);
                # Append the child into result array and parse its children
                $return[] = array(
                    'name' => $child,
                    'title' =>  $parent['title'],
                    'description' =>  $parent['description'],
                    'link' =>  $parent['link'],
                    'model' => $parent['model'],
                    'children' => $this->parseTree($tree, $child)
                );
            }
        }
        return empty($return) ? null : $return;
    }



    /**
     * @param $tree
     */
    public function viewTree($tree, $parent_id, $root = null)
    {
        if (!is_null($tree) && count($tree) > 0) {
            $collapseid = $parent_id;
            if ($root == $parent_id) {
                $collapseid = 'root';
            }
            $completeCollapseId = "collapse-" . $collapseid .'-'.$this->id;
            echo '<ul class="link-list collapse in" id="'.$completeCollapseId.'" aria-expanded="true">';
            foreach ($tree as $b) {
                $id = $b['name'];
                $completeCollapseId = "collapse-" . $id .'-'.$this->id;
                echo '<li class="li-link-list">';
                echo $this->render($this->itemView, [
                    'b' => $b,
                    'id' => $id,
                    'completeCollapseId' => $completeCollapseId,
                    'viewParams' => $this->viewParams
                ]);
                $this->viewTree($b['children'], $id, $root);
                echo '</li>';
            }
            echo '</ul>';

        }
    }

}