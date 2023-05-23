<?php
namespace open20\amos\core\applications;

use yii\web\Application;

/**
 * Add security on password in config files based on prefix in Key Name.
 *
 * export ENC_KEY=1234567890 // This is just a sample key, obviously
 *
 * <VirtualHost *:80>
 * ServerAdmin webmaster@localhost
 * DocumentRoot /var/www/html
 *
 * ErrorLog ${APACHE_LOG_DIR}/error.log
 * CustomLog ${APACHE_LOG_DIR}/access.log combine
 *
 * SetEnv ENC_KEY ${ENC_KEY}
 * </VirtualHost>
 *
 * nel file di configurazione per esempio main.local:
 * 'components' => [
 * 'db' => [
 * 'class' => 'yii\db\Connection',
 * 'dsn' => 'mysql:host=localhost;dbname=db_name',
 * 'ECRY_username' => 'zxbcvYundms909',
 * 'ECRY_password' => 'azert78999989jy',
 * 'charset' => 'utf8',
 * 'enableSchemaCache' => true,
 * 'schemaCacheDuration' => 88000,
 * 'schemaCache' => 'schemaCache',
 * 'attributes' => [PDO::ATTR_CASE => PDO::CASE_LOWER],//Enable on MySQL 8.X
 * ],
 * 'mailer' => [
 * 'class' => 'yii\swiftmailer\Mailer',
 * 'viewPath' => '@common/mail',
 * // send all mails to a file by default. You have to set
 * // 'useFileTransport' to false and configure a transport
 * // for the mailer to send real emails.
 * 'useFileTransport' => true,
 * ],
 * ],
 */
class WebBoot extends AbstractBoot
{

    protected function createApplication($config)
    {
        return new Application($config);
    }
}

