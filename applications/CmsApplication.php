<?php

namespace open20\amos\core\applications;

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    [NAMESPACE_HERE]
 * @category   CategoryName
 */
use luya\web\Application as WebApplication;
use Yii;

class CmsApplication extends WebApplication
{

    public function getHomeUrl()
    {
        return self::toUrl(parent::getHomeUrl());
    }

    /**
     *
     * @param string $url
     * @return string
     */
    public static function toUrl($url)
    {
        $languageString = '/'.Yii::$app->composition['langShortCode'];
        if (strncmp($url, $languageString, strlen($languageString)) === 0) {
            $languageString = "";
        }
        $url = (strcmp($url, '/') === 0)  ? "": $url;
        return $languageString.'/'.$url;
    }
}