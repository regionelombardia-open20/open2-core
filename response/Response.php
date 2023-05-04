<?php
namespace open20\amos\core\response;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response as WebResponse;

class Response extends WebResponse
{
    use ResponseTrait;

    /**
     * 
     * {@inheritDoc}
     */
    public function redirect($url, $statusCode = 302, $checkAjax = true)
    {
        if ($this->authorizedReferrer($url)) {
            return parent::redirect($url, $statusCode, $checkAjax);
        }

        return parent::redirect(Yii::$app->getHomeUrl());
    }

    /**
     *
     * @param string $url
     */
    protected function authorizedReferrer($url)
    {
        $authorized = true;
        $urlinfo = parse_url(str_replace([
            'www.',
            'www2.'
        ], '', $url));
        if (isset($urlinfo['host'])) {
            $authorizedUrls = [];
            foreach (ArrayHelper::merge([
                parse_url(str_replace([
                    'www.',
                    'www2.'
                ], '',\Yii::$app->params['platform']['frontendUrl']))['host']
            ], (isset(\Yii::$app->params['platform']['authorizedReferrer']) ? \Yii::$app->params['platform']['authorizedReferrer'] : [])) as $checkUrl) {
                $authorizedUrls[] = parse_url(str_replace([
                    'www.',
                    'www2.'
                ], '', $checkUrl))['path'];
            }

            if (! in_array($urlinfo['host'], $authorizedUrls)) {
                $authorized = false;
            }
        }
        return $authorized;
    }
}
