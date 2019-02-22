<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\layouts\parts
 * @category   CategoryName
 */

use yii\bootstrap\Alert;

?>

<div class="container-messages">
    <?php
    $FlashMsg = Yii::$app->session->getAllFlashes();
    foreach ($FlashMsg as $type => $message):

        if (!is_array($message)) :
            echo Alert::widget([
                'options' => [
                    'class' => 'alert-' . $type,
                    'role' => 'alert'
                ],
                'body' => $message,
            ]);
        else:
            foreach ($message as $ty => $msg):
                echo Alert::widget([
                    'options' => [
                        'class' => 'alert-' . $type,
                        'role' => 'alert'
                    ],
                    'body' => $msg,
                ]);;
            endforeach;
        endif;
    endforeach;
    ?>
</div>