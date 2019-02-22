<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\helpers
 * @category   CategoryName
 */

namespace lispa\amos\core\helpers;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

class Html extends \yii\helpers\Html {

    private static $actionMap = [
        'CREATE' => 'CREATE',
        'UPDATE' => 'UPDATE',
        'DELETE' => 'DELETE',
        'VIEW' => 'READ',
        'INDEX' => 'READ',
        'DOWNLOAD' => 'READ',
    ];

    public static function submitButton($content = 'Submit', $options = [], $permissions = null, $permissionsParams = []) {
        if (\Yii::$app->getAuthManager()) {
            if (isset($permissions)) {
                if (is_array($permissions)) {
                    throw new InvalidConfigException("{permissions} must be set as String");
                }
                if (\Yii::$app->getUser()->can($permissions, $permissionsParams)) {

                    return parent::submitButton($content, $options);
                } else {
                    return '';
                }
            }
        }
        return parent::submitButton($content, $options);
    }

    public static function button($content = 'Button', $options = [], $permissions = null, $permissionsParams = []) {
        if (\Yii::$app->getAuthManager()) {
            if (isset($permissions)) {
                if (is_array($permissions)) {
                    throw new InvalidConfigException("{permissions} must be set as String");
                }
                if (\Yii::$app->getUser()->can($permissions, $permissionsParams)) {

                    return parent::button($content, $options);
                } else {
                    return '';
                }
            }
        }
        return parent::button($content, $options);
    }

    public static function a($text, $url = null, $options = [], $checkPermNew = false) {
        if (\Yii::$app->getAuthManager()) {
            if (isset($url)) {
                $safeUrl = '';
                $safeUrlParams = [];
                if (is_array($url)) {
                    $tempUrl = $url;
                    $safeUrl = $url[0];
                    unset($tempUrl[0]);
                    $safeUrlParams = $tempUrl;
                } else {
                    $parsedUrl = parse_url($url);
                    if (isset($parsedUrl['path'])) {
                        $safeUrl = $parsedUrl['path'];
                        parse_str(isset($parsedUrl['query']) ? $parsedUrl['query'] : NULL, $safeUrlParams);
                    }
                }

                // Vecchia maniera con le route
                $isValidPermission = \Yii::$app->getAuthManager()->getPermission($safeUrl);

                // Nuovo metodo che verifica i permessi
                $isValidPermissionNew = true;
                if ($checkPermNew) {
                    $isValidPermissionNew = self::checkPermissionByUrl($url, $options);
                }

                if ($isValidPermission) {
                    if (\Yii::$app->getUser()->can($safeUrl)) {

                        $paramsUrl = ArrayHelper::merge([0 => $safeUrl], $safeUrlParams);

                        return parent:: a($text, $paramsUrl, $options);
                    } else {
                        return '';
                    }
                } elseif ($checkPermNew && !$isValidPermissionNew) {
                    return '';
                } else {
                    return parent:: a($text, $url, $options);
                }
            }
        }
        return parent:: a($text, $url, $options);
    }

    /**
     * @param mixed $url
     * @param array $options
     * @return bool
     */
    private static function checkPermissionByUrl($url, $options = []) {        
        if (is_array($url)) {
            $splittedUrl = explode("/", $url[0]);
            $realActionId = trim(strtoupper(end($splittedUrl)));
            $realModelClassName = (isset($options['model']) ? get_class($options['model']) : NULL);           
        } else {
            list($controllerObj, $actionId) = \Yii::$app->createController($url);
            $realModelClassName = (isset($controllerObj->modelClassName) ? $controllerObj->modelClassName : '');
            $splittedModelClassName = explode("\\", $realModelClassName);
            $splittedActionId = explode("?", $actionId);
            $realActionId = trim(strtoupper($splittedActionId[0]));
        }
        if (!$realActionId) {
            $realActionId = 'INDEX';
        }
        $mappedActionId = $realActionId;
        if (array_key_exists($realActionId, self::$actionMap)) {
            $mappedActionId = self::$actionMap[$realActionId];
        }        
        if (!empty($realModelClassName)) {
            $permission1 = $realModelClassName . '_' . strtoupper($mappedActionId);
            $permission2 = strtoupper(StringHelper::basename($realModelClassName)) . '_' . strtoupper($mappedActionId);
            return (\Yii::$app->getUser()->can($permission1, $options) || \Yii::$app->getUser()->can($permission2, $options));
        } else {
            return FALSE;
        }
    }
   
    /**
     * Generates a boolean input.
     * @param string $type the input type. This can be either `radio` or `checkbox`.
     * @param string $name the name attribute.
     * @param boolean $checked whether the checkbox should be checked.
     * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
     *
     * - uncheck: string, the value associated with the uncheck state of the checkbox. When this attribute
     *   is present, a hidden input will be generated so that if the checkbox is not checked and is submitted,
     *   the value of this attribute will still be submitted to the server via the hidden input.
     * - label: string, a label displayed next to the checkbox.  It will NOT be HTML-encoded. Therefore you can pass
     *   in HTML code such as an image tag. If this is is coming from end users, you should [[encode()]] it to prevent XSS attacks.
     *   When this option is specified, the checkbox will be enclosed by a label tag.
     * - labelOptions: array, the HTML attributes for the label tag. Do not set this option unless you set the "label" option.
     *
     * The rest of the options will be rendered as the attributes of the resulting checkbox tag. The values will
     * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
     * See [[renderTagAttributes()]] for details on how attributes are being rendered.
     *
     * @return string the generated checkbox tag
     * @since 2.0.9
     */
    protected static function booleanInput($type, $name, $checked = false, $options = []) {
        $options['checked'] = (bool) $checked;
        $value = array_key_exists('value', $options) ? $options['value'] : '1';

        if (isset($options['uncheck'])) {
            // add a hidden field so that if the checkbox is not selected, it still submits a value
            $hidden = static::hiddenInput($name, $options['uncheck']);
            unset($options['uncheck']);
        } else {
            $hidden = '';
        }

        if (isset($options['label'])) {
            $label = $options['label'];
            $labelOptions = isset($options['labelOptions']) ? $options['labelOptions'] : [];
            unset($options['label'], $options['labelOptions']);

            if (empty($options['labelOptions'])) {
                if (!isset($options['id'])) {
                    $options['id'] = strtolower($name . '-' . strtolower($value));
                }
                $content = '<label for="' . strtolower($name . '-' . strtolower($value)) . '">' . static::input($type, $name, $value, $options) . $label . '</label>';
            } else {
                $content = static::label(static::input($type, $name, $value, $options) . ' ' . $label, null, $labelOptions);
            }
            return $hidden . $content;
        } else {
            return $hidden . static::input($type, $name, $value, $options);
        }
    }
    
    /**
     * Generates a list of radio buttons.
     * A radio button list is like a checkbox list, except that it only allows single selection.
     * @param string $name the name attribute of each radio button.
     * @param string|array $selection the selected value(s).
     * @param array $items the data item used to generate the radio buttons.
     * The array keys are the radio button values, while the array values are the corresponding labels.
     * @param array $options options (name => config) for the radio button list container tag.
     * The following options are specially handled:
     *
     * - tag: string|false, the tag name of the container element. False to render radio buttons without container.
     *   See also [[tag()]].
     * - unselect: string, the value that should be submitted when none of the radio buttons is selected.
     *   By setting this option, a hidden input will be generated.
     * - encode: boolean, whether to HTML-encode the checkbox labels. Defaults to true.
     *   This option is ignored if `item` option is set.
     * - separator: string, the HTML code that separates items.
     * - itemOptions: array, the options for generating the radio button tag using [[radio()]].
     * - item: callable, a callback that can be used to customize the generation of the HTML code
     *   corresponding to a single item in $items. The signature of this callback must be:
     *
     *   ```php
     *   function ($index, $label, $name, $checked, $value)
     *   ```
     *
     *   where $index is the zero-based index of the radio button in the whole list; $label
     *   is the label for the radio button; and $name, $value and $checked represent the name,
     *   value and the checked status of the radio button input, respectively.
     *
     * See [[renderTagAttributes()]] for details on how attributes are being rendered.
     *
     * @return string the generated radio button list
     */
    public static function radioList($name, $selection = null, $items = [], $options = [])
    {
        $formatter = ArrayHelper::remove($options, 'item');
        $itemOptions = ArrayHelper::remove($options, 'itemOptions', []);
        $encode = ArrayHelper::remove($options, 'encode', true);
        $separator = ArrayHelper::remove($options, 'separator', "\n");
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        // add a hidden field so that if the list box has no option being selected, it still submits a value
        $hidden = isset($options['unselect']) ? static::hiddenInput($name, $options['unselect']) : '';
        unset($options['unselect']);

        $lines = [];
        $index = 0;
        foreach ($items as $value => $label) {
            $checked = $selection !== null &&
                (!ArrayHelper::isTraversable($selection) && !strcmp($value, $selection)
                    || ArrayHelper::isTraversable($selection) && ArrayHelper::isIn($value, $selection));
            if ($formatter !== null) {
                $lines[] = call_user_func($formatter, $index, $label, $name, $checked, $value);
            } else {
                $lines[] = static::radio($name, $checked, array_merge($itemOptions, [
                    'value' => $value,
                    'label' => $encode ? static::encode($label) : $label,
                ]));
            }
            $index++;
        }
        $visibleContent = implode($separator, $lines);
        
        if ($tag === false) {
            return '<fieldset><legend class="sr-only">' . \yii\helpers\Inflector::camel2words($name) . '</legend>' . $hidden . $visibleContent . '</fieldset>';
        }

        return '<fieldset><legend class="sr-only">' . \yii\helpers\Inflector::camel2words($name) . '</legend>' . $hidden . static::tag($tag, $visibleContent, $options) . '</fieldset>';
    }
    
    /**
     * Generates a list of checkboxes.
     * A checkbox list allows multiple selection, like [[listBox()]].
     * As a result, the corresponding submitted value is an array.
     * @param string $name the name attribute of each checkbox.
     * @param string|array $selection the selected value(s).
     * @param array $items the data item used to generate the checkboxes.
     * The array keys are the checkbox values, while the array values are the corresponding labels.
     * @param array $options options (name => config) for the checkbox list container tag.
     * The following options are specially handled:
     *
     * - tag: string|false, the tag name of the container element. False to render checkbox without container.
     *   See also [[tag()]].
     * - unselect: string, the value that should be submitted when none of the checkboxes is selected.
     *   By setting this option, a hidden input will be generated.
     * - encode: boolean, whether to HTML-encode the checkbox labels. Defaults to true.
     *   This option is ignored if `item` option is set.
     * - separator: string, the HTML code that separates items.
     * - itemOptions: array, the options for generating the checkbox tag using [[checkbox()]].
     * - item: callable, a callback that can be used to customize the generation of the HTML code
     *   corresponding to a single item in $items. The signature of this callback must be:
     *
     *   ```php
     *   function ($index, $label, $name, $checked, $value)
     *   ```
     *
     *   where $index is the zero-based index of the checkbox in the whole list; $label
     *   is the label for the checkbox; and $name, $value and $checked represent the name,
     *   value and the checked status of the checkbox input, respectively.
     *
     * See [[renderTagAttributes()]] for details on how attributes are being rendered.
     *
     * @return string the generated checkbox list
     */
    public static function checkboxList($name, $selection = null, $items = [], $options = [])
    {
        if (substr($name, -2) !== '[]') {
            $name .= '[]';
        }

        $formatter = ArrayHelper::remove($options, 'item');
        $itemOptions = ArrayHelper::remove($options, 'itemOptions', []);
        $encode = ArrayHelper::remove($options, 'encode', true);
        $separator = ArrayHelper::remove($options, 'separator', "\n");
        $tag = ArrayHelper::remove($options, 'tag', 'div');

        $lines = [];
        $index = 0;
        foreach ($items as $value => $label) {
            $checked = $selection !== null &&
                (!ArrayHelper::isTraversable($selection) && !strcmp($value, $selection)
                    || ArrayHelper::isTraversable($selection) && ArrayHelper::isIn($value, $selection));
            if ($formatter !== null) {
                $lines[] = call_user_func($formatter, $index, $label, $name, $checked, $value);
            } else {
                $lines[] = static::checkbox($name, $checked, array_merge($itemOptions, [
                    'value' => $value,
                    'label' => $encode ? static::encode($label) : $label,
                ]));
            }
            $index++;
        }

        if (isset($options['unselect'])) {
            // add a hidden field so that if the list box has no option being selected, it still submits a value
            $name2 = substr($name, -2) === '[]' ? substr($name, 0, -2) : $name;
            $hidden = static::hiddenInput($name2, $options['unselect']);
            unset($options['unselect']);
        } else {
            $hidden = '';
        }

        $visibleContent = implode($separator, $lines);

        if ($tag === false) {
            return '<fieldset><legend class="sr-only">' . \yii\helpers\Inflector::camel2words($name) . '</legend>' . $hidden . $visibleContent . '</fieldset>';
        }

        return '<fieldset><legend class="sr-only">' . \yii\helpers\Inflector::camel2words($name) . '</legend>' . $hidden . static::tag($tag, $visibleContent, $options) . '</fieldset>';
    }

}
