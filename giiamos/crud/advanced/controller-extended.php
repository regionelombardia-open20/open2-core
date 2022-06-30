<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\giiamos\crud\wizard
 * @category   CategoryName
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;
use yii\helpers\Inflector;

/**
 * Customizable controller class.
 */
echo "<?php\n";
?>

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\'))?> 
 */
 
namespace <?= \yii\helpers\StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

/**
 * Class <?= $controllerClassName ?> 
 * This is the class for controller "<?= $controllerClassName ?>".
 * @package <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?> 
 */
class <?= $controllerClassName ?> extends \<?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')).'\base\\'.$controllerClassName."\n" ?>
{

}
