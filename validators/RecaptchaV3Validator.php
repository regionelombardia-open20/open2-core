<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\validators
 * @category   CategoryName
 */

namespace lispa\amos\core\validators;

use yii\base\InvalidConfigException;
use yii\validators\Validator;
use yii\httpclient\Request as HttpClientRequest;
use yii\httpclient\Client as HttpClient;
use Yii;

class RecaptchaV3Validator extends Validator
{

    const MAX = 0.9;
    const SECURE = 0.8;
    const DEFAULT = 0.5;
    const WEAK = 0.2;
    const ALLOWALL = 0.0;

    const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /** @var string */
    public $secretKey;

    /** @var string */
    public $secKey;

    /** @var float */
    public $score;

    /** @var string */
    public $errorMessage;

    /** @var \yii\httpclient\Request */
    public $httpClientRequest;

    public function init()
    {
        parent::init();

        /**
         * Checking if the recaptcha secret keys exist and are correctly set.
         */
        if(!array_key_exists('recaptchaV3Secret', \Yii::$app->params) || is_null(\Yii::$app->params['recaptchaV3Secret']) || empty(\Yii::$app->params['recaptchaV3Secret'])) {
            if(is_null($this->secretKey) || empty($this->secretKey)) {
                throw new InvalidConfigException("You haven't set any recaptcha v3 secret key.\nTo do so, add a new 'recaptchaV3Secret' parameter in your params.php file with your secret recaptcha site key or set it directly in the validator options.\n\ne.g. 'recaptchaV3Secret' => 'your_recaptcha_secret_key'");
            }
        }

        /**
         * Creating an HttpClientRequest if not already created
         */
        if (empty($this->httpClientRequest) || !($this->httpClientRequest instanceof HttpClientRequest)) {
            $this->httpClientRequest = (new HttpClient())->createRequest();
        }

        /**
         * Checking if the recaptcha secret key is set.
         * THE "CUSTOM-SET" PRIVATE KEY SET IN THE OPTION OF THE VALIDATOR WILL OVERRIDE THE PRIVATE KEY SET IN THE PARAMS!
         */
        if(!is_null(\Yii::$app->params['recaptchaV3Secret']) && !empty(\Yii::$app->params['recaptchaV3Secret'])) {
            $this->secKey = \Yii::$app->params['recaptchaV3Secret'];
        }
        if(!is_null($this->secretKey) && !empty($this->secretKey)) {
            $this->secKey = $this->secretKey;
        }

        /**
         * Setting a default error message if not explicitly set
         */
        if($this->errorMessage === null) {
            $this->errorMessage = Yii::t('app', 'The antibot analysis returned errors. Please try to submit again your form.');
        }

        /**
         * Setting a default score value if not explicitly set.
         * If a custom score is set, it will be checked if is an effective float value.
         */
        if($this->score === null) {
            $this->score = self::DEFAULT;
        } else {
            if(!is_float($this->score)) {
                throw new InvalidConfigException("Your {$this->score} score is invalid.");
            }
        }

    }

    public function validateAttribute($model, $attribute)
    {

        /**
         * @var string $responseToken
         * Stores the response token.
         */
        $responseToken = $model->$attribute;

        if(is_null($responseToken) && $responseToken == '') {
            $this->addError($model, $attribute, $this->errorMessage);
            return false;
        }

        /**
         * @var HttpClientRequest $response
         * Stores the http response got from Google.
         */
        $response = $this->getHttpResponse($responseToken);

        /**
         * Checking if the request is genuine
         */
        if(!$response->data['success']) {
            $this->addError($model, $attribute, $this->errorMessage);
            return false;
        }

        /**
         * Checking if the action of the validation is the same of the form that's validating
         */
        if(!$this->checkAction($response->data['action'])) {
            $this->addError($model, $attribute, $this->errorMessage);
            return false;
        }

        /**
         * Checking if the hostname of the request is the same of the current application
         */
        if(!$this->checkHostname($response->data['hostname'])) {
            $this->addError($model, $attribute, $this->errorMessage);
            return false;
        }

        /**
         * Checking if the score of the request is higher or equal than the score limit set in this validator
         */
        if(!$this->checkScore($response->data['score'])) {
            $this->addError($model, $attribute, $this->errorMessage);
            return false;
        }

        return true;

    }

    /**
     * @param $responseToken
     * @return mixed
     * The http response got from Google
     */
    private function getHttpResponse($responseToken) {
        $response = $this->httpClientRequest
            ->setMethod('GET')
            ->setUrl(self::RECAPTCHA_VERIFY_URL)
            ->setData(['secret' => $this->secKey, 'response' => $responseToken, 'remoteip' => Yii::$app->request->userIP])
            ->send();
        if (!$response->isOk) {
            throw new Exception('Unable connection to the captcha server. Status code ' . $response->statusCode);
        }
        return $response;
    }

    /**
     * @param $action
     * @return bool
     * Checks if the action sent back from Google is the same of the current action that required the validation
     */
    private function checkAction($action) {
        return $action === str_replace(' ', '_', str_replace('-', '_', \Yii::$app->controller->action->controller->action->id));
    }

    /**
     * @param $hostname
     * @return bool
     * Checks if the hostname sent from Google is the same of the current hostname that required the validation
     */
    private function checkHostname($hostname) {
        return $hostname === \Yii::$app->request->hostName;
    }

    /**
     * @param $score
     * @return bool
     * Checks if the score sent from Google is higher or equal than the score limit set in this validator
     */
    private function checkScore($score) {
        return (floatval($this->score) <= floatval($score));
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param \yii\web\View $view
     * @return null|string
     * Client side validation
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $errorMessage = Yii::t(
            'app',
            'The hidden captcha is empty. Please, refresh your page.',
            ['attribute' => $model->getAttributeLabel($attribute)]
        );

        return <<<JS
if (value === undefined || value === '' || !value) {
     messages.push("{$errorMessage}");
     return true;
}
JS;
    }

    /**
     * @return bool whether this widget is associated with a data model.
     */
    protected function hasModel()
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }

}