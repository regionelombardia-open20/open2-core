<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\rbac
 * @category   CategoryName
 */

namespace open20\amos\core\rbac;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\log\Logger;
use yii\rbac\Item;

/**
 * Class AuthManager
 * @package open20\amos\core\rbac
 */
class AuthManager extends \yii\rbac\DbManager
{
    /**
     * @param string $roleName
     * @return array|string[]
     */
    public function getUserIdsByRole($roleName)
    {
        $ids = parent::getUserIdsByRole($roleName);
        foreach ($this->getParents($roleName) as $parent) {
            if ($parent['type'] == Item::TYPE_ROLE) {
                $ids = ArrayHelper::merge($ids, $this->getUserIdsByRole($parent['name']));
            }
        }
        return array_unique($ids);
    }

    /**
     * @param string $roleName
     * @return array
     */
    public function getParents($roleName)
    {
        $parents = [];

        try {
            $query = new Query;
            $parents = $query->select('b.*')
                ->from(['a' => $this->itemChildTable, 'b' => $this->itemTable])
                ->where('{{a}}.[[parent]]={{b}}.[[name]]')
                ->andwhere(['child' => $roleName])
                ->all($this->db);
        } catch (\Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
        return $parents;
    }

    /**
     * @param string $roleName
     * @return array|string[]
     */
    public function getUserIdsByRoleDirectlyAssigned($roleName)
    {
        if (empty($roleName)) {
            return [];
        }

        return (new Query)->select('[[user_id]]')
            ->from($this->assignmentTable)
            ->where(['item_name' => $roleName])->column($this->db);
    }
}
