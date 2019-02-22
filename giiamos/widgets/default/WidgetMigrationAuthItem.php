<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\giiamos\widgets\default
 * @category   CategoryName
 */

echo "<?php\n";
?>
use lispa\amos\core\migration\AmosMigrationPermissions;
use yii\rbac\Permission;
<?php
$el_count = count($data_obj->rolesSelected);
$str_role = '[';
if (!empty($data_obj->rolesSelected)){

    foreach ($data_obj->rolesSelected as $key => $role){
        $str_role .= "'".$role."'";
        if($key+1 < $el_count ){
            $str_role .= ',';
        }
    }
}
$str_role .= ']';
//pr($str_role, "STR ROLE");
?>


/**
* Class <?= $data_obj->migration_auth_filename; ?>
*/
class <?= $data_obj->migration_auth_filename; ?> extends AmosMigrationPermissions
{

    /**
    * @inheritdoc
    */
    protected function setRBACConfigurations()
    {
        $prefixStr = 'Permissions for the dashboard for the widget ';

        return [
                [
                    'name' =>  \<?= $data_obj->ns_4class. '\\' .$data_obj->widgetName; ?>::className(),
                    'type' => Permission::TYPE_PERMISSION,
                    'description' => $prefixStr . '<?= $data_obj->widgetName; ?>',
                    'ruleName' => null,
                    'parent' => <?= $str_role; ?>

                ]

            ];
    }
}
