<?php

namespace open20\amos\core\response;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response as WebResponse;

class Response extends WebResponse {

    use ResponseTrait;

    /**
     * 
     * {@inheritDoc}
     */
    public function redirect($url, $statusCode = 302, $checkAjax = true) {
        $url = $this->checkUrlFormat($url);
        if (filter_var($url, FILTER_VALIDATE_URL) && $this->authorizedReferrer($url)) {
            return parent::redirect($url, $statusCode, $checkAjax);
        }

        return parent::redirect(Yii::$app->getHomeUrl());
    }

    /**
     *
     * @param string|array $url
     */
    protected function authorizedReferrer($url) {
        if (is_array($url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!empty($host)) {
            $host = str_replace(['www.', 'www2.'], '', $host);

            $authorizedUrls = [];
            // Frontend host
            $frontendHost = parse_url(\Yii::$app->params['platform']['frontendUrl'], PHP_URL_HOST);
            // Backend host
            $backendHost = parse_url(\Yii::$app->params['platform']['backendUrl'], PHP_URL_HOST);
            // Authorized referrers
            $authorizedReferrers = isset(\Yii::$app->params['platform']['authorizedReferrer']) ? \Yii::$app->params['platform']['authorizedReferrer'] : [];
            // Oauth2 clients
            /** @var \open20\amos\socialauth\Module $moduleSocialAuth */
            $moduleSocialAuth = \Yii::$app->getModule('socialauth');
            if ($moduleSocialAuth && isset($moduleSocialAuth->authorizeReferrersFromOauth2Client) && $moduleSocialAuth->authorizeReferrersFromOauth2Client) {
                $oauth2ClientsRedirectUri = \open20\amos\socialauth\models\Oauth2Client::find()->select('redirect_uri')->column();
                foreach ($oauth2ClientsRedirectUri as $oauth2ClientRedirectUri) {
                    $authorizedReferrers[] = parse_url($oauth2ClientRedirectUri, PHP_URL_HOST);
                }
            }

            $urlsToCheck = array_unique(ArrayHelper::merge([$frontendHost, $backendHost], $authorizedReferrers));

            foreach ($urlsToCheck as $checkUrl) {
                $checkHost = parse_url($checkUrl, PHP_URL_PATH);
                $authorizedUrls[] = str_replace(['www.', 'www2.'], '', $checkHost);
            }

            if (in_array($host, $authorizedUrls)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $url
     * @return array|string|string[]
     */
    protected function checkUrlFormat($url) {
        // replace '\\' with '//'
        if (is_array($url)) {
            $url = \Yii::$app->urlManager->createAbsoluteUrl($url);
        }
        if (strpos($url, '\\\\') !== false) {
            $url = str_replace('\\\\', '//', $url);
        }

        $url = filter_var($url, FILTER_SANITIZE_URL);

        // gestione url relativi e assoluti senza hostname
        $urlinfo = parse_url($url);
        if (empty($urlinfo['scheme']) && empty($urlinfo['host']) && !empty($urlinfo['path'])) {
            if (strpos($urlinfo['path'], '/') === 0) {
                $url = \Yii::$app->params['platform']['frontendUrl'] . $urlinfo['path'];
            } else {
                $url = \Yii::$app->params['platform']['frontendUrl'] . '/' . $urlinfo['path'];
            }
        }

        return $url;
    }
}
