<?php

namespace open20\amos\core\components;
use open20\amos\core\helpers\StringHelper;
use yii\web\NotFoundHttpException;

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core
 * @category   CategoryName
 */
class CompositionResolver extends \luya\web\CompositionResolver
{

    public $resolved;

    /**
     * Resolve the current data.
     *
     * @return array
     */
    protected function getInternalResolverArray()
    {
        if ($this->resolved === null) {
            $requestPathInfo = $this->trailingPathInfo();
            $newRegex        = $this->buildRegexPattern();

            // extract the rules from the regex pattern, this means you get array with keys for every rule inside the pattern string
            // example pattern: <langShortCode:[a-z]{2}>-<countryShortCode:[a-z]{2}>
            /* [0]=>
              array(3) {
              [0]=> string(24) "<langShortCode:[a-z]{2}>"
              [1]=> string(13) "langShortCode"
              [2]=> string(8) "[a-z]{2}"
              }
              [1]=>
              array(3) {
              [0]=> string(27) "<countryShortCode:[a-z]{2}>"
              [1]=> string(16) "countryShortCode"
              [2]=> string(8) "[a-z]{2}"
              }
             */
            preg_match_all(static::VAR_MATCH_REGEX, $this->composition->pattern, $patternDefinitions, PREG_SET_ORDER);

            foreach ($patternDefinitions as $definition) {
                $newRegex = str_replace($definition[0], '('.rtrim(ltrim($definition[2], '('), ')').')', $newRegex);
            }

            preg_match_all($newRegex, $requestPathInfo, $matches, PREG_SET_ORDER);

            if (isset($matches[0]) && !empty($matches[0])) {
                $keys    = [];
                $matches = $matches[0];

                $compositionPrefix = $matches[0];
                unset($matches[0]);
                $matches           = array_values($matches);

                foreach ($matches as $k => $v) {
                    $keys[$patternDefinitions[$k][1]] = $v;
                    \Yii::$app->language              = $v;
                    $this->setLanguageCookie(['language' => $v]);
                }
                $route = StringHelper::replaceFirst($compositionPrefix, '', $requestPathInfo);
            } else {
                if (empty(\Yii::$app->language)) {
                    $matches = [];
                    $keys    = $this->composition->default;
                    $route   = $requestPathInfo;
                } else {
                    $matches = [];
                    $keys    = ['langShortCode' => strtok(\Yii::$app->language, '-')];
                    $route   = $requestPathInfo;
                }
            }

            // the validation check for validates composition values is enabled
            if ($this->composition->expectedValues) {
                foreach ($keys as $k => $v) {
                    $possibleValues = $this->composition->expectedValues[$k];

                    if (!in_array($v, $possibleValues)) {
                        throw new NotFoundHttpException("The requested composition key \"{$k}\" with value \"{$v}\" is not in the possible values list.");
                    }
                }
            }

            $this->resolved = [
                'route' => rtrim($route, '/'),
                'values' => $keys,
            ];
        }
        return $this->resolved;
    }

    public function setLanguageCookie($data)
    {
        $module = \Yii::$app->getModule('translation');
        if ($module && $module->secureCookie) {
            $languageCookie = new \yii\web\Cookie([
                'name' => 'language',
                'value' => $data['language'],
                'expire' => time() + 60 * 60 * 24 * 30, // 30 days
            ]);
            \Yii::$app->response->cookies->add($languageCookie);
        }
    }
}