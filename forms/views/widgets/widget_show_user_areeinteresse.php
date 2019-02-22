<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms\views\widgets
 * @category   CategoryName
 */

/**
 * @var \lispa\amos\tag\models\Tag $root
 * @var \yii\base\View $this
 */
?>

<?php
foreach ($allRootTags as $root){
    $classname = $root['classname'];
    ?>
    <h3><?= $root['label'] ?></h3>
    <?php
    foreach($root['trees'] as $root_tree) {
        //dati del nodo
        $label_root_tree = $root_tree['nome'];
        $id_root_tree = $root_tree['root'];

        $label_print = false;
        ?>
        <ul class="taglist">
            <?php
            foreach ($allTags as $tag){
                //se ci sono i dati minimi per il confronto del tag
                if (isset($tag['root']) && isset($tag['interest_classname'])){
                    //se corrispondono root e contesto
                    if ($id_root_tree == $tag['root'] && $classname == $tag['interest_classname']){
                        if(!$label_print){
                            ?>
                            <h4><?= $label_root_tree ?></h4>
                            <?php
                            $label_print = true;
                        }
                        ?>
                        <li class="tag-item">
                            <div>
                                <p class="bold uppercase tag-label"><?= $tag['nome'] ?></p>
                                <?php if(!($tag['path'] == NULL)): ?>
                                    <span><small class="italic"><?= $tag['path'] ?></small></span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php
                    }
                }
            }
            ?>
        </ul>
        <?php
    }
}
?>
