<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\giiamos\crud\wizard
 * @category   CategoryName
 */

/**
 * Customizable controller class.
 */
echo "<?php\n";
?>

namespace <?= \yii\helpers\StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

/**
* This is the class for controller "<?= $controllerClassName ?>".
*/
class <?= $controllerClassName ?> extends <?= 'base\\'.$controllerClassName."\n" ?>
{

}
