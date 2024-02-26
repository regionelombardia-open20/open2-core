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
        if ($this->authorizedReferrer($url)) {
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
            $url = \yii\helpers\Url::to($url);
        }
        $authorized = true;
        $urlinfo = parse_url(str_replace(['www.', 'www2.'], '', $url));
        if (empty($urlinfo)) {
            $authorized = false;
        } else if (!empty($urlinfo['scheme']) && empty($urlinfo['host'])) {
            $authorized = false;
        } else {
            if (!isset($urlinfo['host'])) {
                $url = \Yii::$app->params['platform']['frontendUrl'] . '/' . $url;
                $urlinfo = parse_url(str_replace(['www.', 'www2.'], '', $url));
            }
            if (isset($urlinfo['host'])) {
                $authorizedUrls = [];
                // Frontend host
                $frontendHost = parse_url(str_replace(['www.', 'www2.'], '', \Yii::$app->params['platform']['frontendUrl']))['host'];
                // Backend host
                $backendHost = parse_url(str_replace(['www.', 'www2.'], '', \Yii::$app->params['platform']['backendUrl']))['host'];
                // Authorized referrers
                $authorizedReferrers = isset(\Yii::$app->params['platform']['authorizedReferrer']) ? \Yii::$app->params['platform']['authorizedReferrer'] : [];
                // Oauth2 clients
                /** @var \open20\amos\socialauth\Module $moduleSocialAuth */
                $moduleSocialAuth = \Yii::$app->getModule('socialauth');
                if ($moduleSocialAuth && isset($moduleSocialAuth->authorizeReferrersFromOauth2Client) && $moduleSocialAuth->authorizeReferrersFromOauth2Client) {
                    $oauth2ClientsRedirectUri = \open20\amos\socialauth\models\Oauth2Client::find()->select('redirect_uri')->column();
                    foreach ($oauth2ClientsRedirectUri as $oauth2ClientRedirectUri) {
                        $authorizedReferrers[] = parse_url(str_replace(['www.', 'www2.'], '', $oauth2ClientRedirectUri))['host'];
                    }
                }

                $urlsToCheck = array_unique(ArrayHelper::merge([$frontendHost, $backendHost], $authorizedReferrers));

                foreach ($urlsToCheck as $checkUrl) {
                    $authorizedUrls[] = parse_url(str_replace(['www.', 'www2.'], '', $checkUrl))['path'];
                }

                if (!in_array($urlinfo['host'], $authorizedUrls)) {
                    $authorized = false;
                }
            }
        }
        return $authorized;
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

        return $url;
    }
}
