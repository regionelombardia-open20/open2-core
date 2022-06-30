<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\email
 * @category   CategoryName
 */

/**
 * @var string $message
 * @var string $nameUser
 */
use open20\amos\core\module\BaseAmosModule;
if(!isset($email)){
    $email = Yii::$app->user->email;
}
?>

<?= $nameUser . ' '. BaseAmosModule::t('amoscore', '#request_information_mail') ?>
<?= $message ?>
<?= BaseAmosModule::t('amoscore', '#request_information_mail_footer') . ' '. $email ?>

