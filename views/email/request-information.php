<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\email
 * @category   CategoryName
 */

/**
 * @var string $message
 * @var string $nameUser
 */
use lispa\amos\core\module\BaseAmosModule;
if(!isset($email)){
    $email = Yii::$app->user->email;
}
?>

<?= $nameUser . ' '. BaseAmosModule::t('amoscore', '#request_information_mail') ?>
<?= $message ?>
<?= BaseAmosModule::t('amoscore', '#request_information_mail_footer') . ' '. $email ?>

