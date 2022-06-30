<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\interfaces
 * @category   CategoryName
 */

namespace open20\amos\core\interfaces;

use yii\data\BaseDataProvider;
use yii\db\ActiveQuery;

/**
 * Interface ContentModelSearchInterface
 * @package open20\amos\core\interfaces
 */
interface ContentModelSearchInterface
{
    /**
     * This method define the search default order.
     * @param BaseDataProvider $dataProvider
     * @return BaseDataProvider
     */
    public function searchDefaultOrder($dataProvider);

    /**
     * This method returns the ActiveQuery object that contains the query to retrieve logged user own interest contents.
     * @param array $params
     * @return ActiveQuery
     */
    public function searchOwnInterestsQuery($params);

    /**
     * This method returns the ActiveQuery object that contains the query to retrieve logged user all contents.
     * @param array $params
     * @return ActiveQuery
     */
    public function searchAllQuery($params);

    /**
     * This method returns the ActiveQuery object that contains the query to retrieve created by logged user contents.
     * @param array $params
     * @return ActiveQuery
     */
    public function searchCreatedByMeQuery($params);

    /**
     * This method returns the ActiveQuery object that contains the query to retrieve logged user to validate contents.
     * @param array $params
     * @return ActiveQuery
     */
    public function searchToValidateQuery($params);
}
