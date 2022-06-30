<?php

namespace open20\amos\core\helpers;

use yii\helpers\ArrayHelper;
use yii\helpers\BaseStringHelper;

class StringHelper extends BaseStringHelper
{

    /**
     * TypeCast a string to its specific types.
     *
     * Arrays will passed to to the {{luya\helpers\ArrayHelper::typeCast()}} class.
     *
     * @param mixed $string The input string to type cast. Arrays will be passted to {{luya\helpers\ArrayHelper::typeCast()}}.
     * @return mixed The new type casted value, if the input is an array the output is the typecasted array.
     */
    public static function typeCast($string)
    {
        if (is_numeric($string)) {
            return static::typeCastNumeric($string);
        } elseif (is_array($string)) {
            return ArrayHelper::typeCast($string);
        }

        return $string;
    }

    /**
     * Checke whether a strings starts with the wildcard symbole and compares the string before the wild card symbol *
     * with the string provided, if there is NO wildcard symbold it always return false.
     *
     *
     * @param string $string The string which should be checked with $with comperator
     * @param string $with The with string which must end with the wildcard symbol * e.g. `foo*` would match string `foobar`.
     * @param boolean $caseSensitive Whether to compare the starts with string as case sensitive or not, defaults to true.
     * @return boolean Whether the string starts with the wildcard marked string or not, if no wildcard symbol is contained.
     * in the $with it always returns false.
     */
    public static function startsWithWildcard($string, $with, $caseSensitive = true)
    {
        if (substr($with, -1) != "*") {
            return false;
        }

        return self::startsWith($string, rtrim($with, '*'), $caseSensitive);
    }

    /**
     * TypeCast a numeric value to float or integer.
     *
     * If the given value is not a numeric or float value it will be returned as it is. In order to find out whether its float
     * or not use {{luya\helpers\StringHelper::isFloat()}}.
     *
     * @param mixed $value The given value to parse.
     * @return mixed Returns the original value if not numeric or integer, float casted value.
     */
    public static function typeCastNumeric($value)
    {
        if (!self::isFloat($value)) {
            return $value;
        }

        if (intval($value) == $value) {
            return (int) $value;
        }

        return (float) $value;
    }

    /**
     * Checks whether a string is a float value.
     *
     * Compared to `is_float` function of php, it only ensures whether the input variable is type float.
     *
     * @param mixed $value The value to check whether its float or not.
     * @return boolean Whether its a float value or not.
     */
    public static function isFloat($value)
    {
        if (is_float($value)) {
            return true;
        }

        return ($value == (string) (float) $value);
    }

    /**
     * Replace only the first occurance found inside the string.
     *
     * The replace first method is *case sensitive*.
     *
     * ```php
     * StringHelper::replaceFirst('abc', '123', 'abc abc abc'); // returns "123 abc abc"
     * ```
     *
     * @param string $search Search string to look for.
     * @param string $replace Replacement value for the first found occurrence.
     * @param string $subject The string you want to look up to replace the first element.
     * @return mixed Replaced string
     */
    public static function replaceFirst($search, $replace, $subject)
    {
        return preg_replace('/' . preg_quote($search, '/') . '/', $replace, $subject, 1);
    }

    /**
     * Check whether a char or word exists in a string or not.
     *
     * This method is case sensitive. The need can be an array with multiple chars or words who
     * are going to look up in the haystack string.
     *
     * If an array of needle words is provided the $strict parameter defines whether all need keys must be found
     * in the string to get the `true` response or if just one of the keys are found the response is already `true`.
     *
     * @param string|array $needle The char or word to find in the $haystack. Can be an array to multi find words or char in the string.
     * @param string $haystack The haystack where the $needle string should be looked  up.
     * @param boolean $strict If an array of needles is provided the $strict parameter defines whether all keys must be found ($strict = true) or just one result must be found ($strict = false).
     * @return boolean If an array of values is provided the response may change depending on $findAll.
     */
    public static function contains($needle, $haystack, $strict = false)
    {
        $needles = (array) $needle;

        $state = false;

        foreach ($needles as $item) {
            $state = (strpos($haystack, $item) !== false);

            if ($strict && !$state) {
                return false;
            }

            if (!$strict && $state) {
                return true;
            }
        }

        return $state;
    }

    /**
     * 
     * @param type $input
     * @param array $options not used here
     * @return type
     */
    public static function minify($input, array $options = [])
    {
        if (trim($input) === '') {
            return $input;
        }
        
        // Remove extra white-space(s) between HTML attribute(s)
        $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
            return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
        }, str_replace("\r", "", $input));
        
        // Minify inline CSS declaration(s)
        if (strpos($input, ' style=') !== false) {
            $input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
                return '<' . $matches[1] . ' style=' . $matches[2] . self::minify_css($matches[3]) . $matches[2];
            }, $input);
        }
        
        if (strpos($input, '</style>') !== false) {
            $input = preg_replace_callback('#<style(.*?)>(.*?)</style>#is', function($matches) {
                return '<style' . $matches[1] . '>' . self::minify_css($matches[2]) . '</style>';
            }, $input);
        }
        
        if (strpos($input, '</script>') !== false) {
            $input = preg_replace_callback('#<script(.*?)>(.*?)</script>#is', function($matches) {
                return '<script' . $matches[1] . '>' . self::minify_js($matches[2]) . '</script>';
            }, $input);
        }

        if (ArrayHelper::getValue($options, 'comments', true)) {
            $input = preg_replace('/<!--(.*)-->/Uis', '', $input);
        }
        
        return preg_replace(
            array(
                // t = text
                // o = tag open
                // c = tag close
                // Keep important white-space(s) after self-closing HTML tag(s)
                '#<(img|input)(>| .*?>)#s',
                // Remove a line break and two or more white-space(s) between tag(s)
                '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
                '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
                '#<(img|input)(>| .*?>)<\/\1>#s', // reset previous fix
                '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
                '#(?<=\>)(&nbsp;)(?=\<)#', // --ibid
                // Remove HTML comment(s) except IE comment(s)
                '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
            ),
            array(
                '<$1$2</$1>',
                '$1$2$3',
                '$1$2$3',
                '$1$2$3$4$5',
                '$1$2$3$4$5$6$7',
                '$1$2$3',
                '<$1$2',
                '$1 ',
                '$1',
                ""
            ),
            $input);
    }

    /**
     * CSS Minifier => http://ideone.com/Q5USEF + improvement(s)
     * 
     * @param type $input
     * @return type
     */
    public static function minify_css($input)
    {
        if (trim($input) === '') {
            return $input;
        }
        
        return preg_replace(
            array(
                // Remove comment(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                // Remove unused white-space(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                // Replace `:0 0 0 0` with `:0`
                '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                // Replace `background-position:0` with `background-position:0 0`
                '#(background-position):0(?=[;\}])#si',
                // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                '#(?<=[\s:,\-])0+\.(\d+)#s',
                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                // Minify HEX color code
                '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                // Replace `(border|outline):none` with `(border|outline):0`
                '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                // Remove empty selector(s)
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
            ),
            array(
                '$1',
                '$1$2$3$4$5$6$7',
                '$1',
                ':0',
                '$1:0 0',
                '.$1',
                '$1$3',
                '$1$2$4$5',
                '$1$2$3',
                '$1:0',
                '$1$2'
            ),
            $input);
    }

    /**
     * JavaScript Minifier
     * 
     * @param type $input
     * @return type
     */
    public static function minify_js($input)
    {
        if (trim($input) === '') {
            return $input;
        }
        
        return preg_replace(
            array(
                // Remove comment(s)
                '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
                // Remove white-space(s) outside the string and regex
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
                // Remove the last semicolon
                '#;+\}#',
                // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
                '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
                // --ibid. From `foo['bar']` to `foo.bar`
                '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
            ),
            array(
                '$1',
                '$1$2',
                '}',
                '$1$3',
                '$1.$3'
            ),
            $input);
    }

//    /**
//     * "Minify" html content.
//     *
//     * + remove space
//     * + remove tabs
//     * + remove newlines
//     * + remove html comments
//     *
//     * @param string $content The content to minify.
//     * @param array $options Optional arguments to provide for minification:
//     * - comments: boolean, where html comments should be removed or not. defaults to true
//     * @return mixed Returns the minified content.
//     * @since 1.0.7
//     */
//    public static function minify($content, array $options = [])
//    {
//
//        //Remove JS comments
//        $min = preg_replace('~//<!\[CDATA\[\s*|\s*//\]\]>~', '', trim($content));
//        $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/';
//        $min = preg_replace($pattern, '', $min);
//
//        $min = preg_replace(['/[\n\r]/', '/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s',], ['', '>', '<', '\\1'], $min);
//        $min = str_replace(['> <'], ['><'], $min);
//
//        if (ArrayHelper::getValue($options, 'comments', true)) {
//            $min = preg_replace('/<!--(.*)-->/Uis', '', $min);
//        }
//
//        return $min;
//    }

    /**
     * Cut the given word/string from the content. Its truncates to the left side and to the right side of the word.
     *
     * An example of how a sentenced is cut:
     *
     * ```php
     * $cut = StringHelper::truncateMiddle('the quick fox jumped over the lazy dog', 'jumped', 12);
     * echo $cut; // ..e quick fox jumped over the la..
     * ```
     *
     * @param string $content The content to cut the words from.
     * @param string $word The word which should be in the middle of the string
     * @param integer $length The amount of the chars to cut on the left and right side from the word.
     * @param string $affix The chars which should be used for prefix and suffix when string is cuted.
     * @param boolean $caseSensitive Whether the search word in the string even when lower/upper case is not correct.
     * @since 1.0.12
     */
    public static function truncateMiddle($content, $word, $length, $affix = '..', $caseSensitive = false)
    {
        $content = strip_tags($content);
        $array = self::mb_str_split($content);
        $first = mb_strpos($caseSensitive ? $content : mb_strtolower($content), $caseSensitive ? $word : mb_strtolower($word));

        // we could not find any match, therefore use casual truncate method.
        if ($first === false) {
            // as the length value in truncate middle stands for to the left and to the right, we multiple this value with 2
            return self::truncate($content, ($length * 2), $affix);
        }

        $last = $first + mb_strlen($word);

        // left and right array chars from word
        $left = array_slice($array, 0, $first, true);
        $right = array_slice($array, $last, null, true);
        $middle = array_splice($array, $first, mb_strlen($word));

        // string before
        $before = (count($left) > $length) ? $affix . implode("", array_slice($left, -$length)) : implode("", $left);
        $after = (count($right) > $length) ? implode("", array_slice($right, 0, $length)) . $affix : implode("", $right);

        return $before . implode("", $middle) . $after;
    }

    /**
     * Highlight a word within a content.
     *
     * Since version 1.0.14 an array of words to highlight is possible.
     *
     * > This function IS NOT case sensitive!
     *
     *
     *
     * @param string $content The content to find the word.
     * @param string $word The word to find within the content.
     * @param string $markup The markup used wrap the word to highlight.
     * @since 1.0.12
     */
    public static function highlightWord($content, $word, $markup = '<b>%s</b>')
    {
        $word = (array) $word;
        $content = strip_tags($content);
        $latest = null;
        foreach ($word as $needle) {
            preg_match_all("/" . preg_quote($needle, '/') . "+/i", $content, $matches);
            if (is_array($matches[0]) && count($matches[0]) >= 1) {
                foreach ($matches[0] as $match) {
                    // ensure if a word is found twice we don't replace again.
                    if ($latest === $match) {
                        continue;
                    }
                    $content = str_replace($match, sprintf($markup, $match), $content);
                    $latest = $match;
                }
            }
        }

        return $content;
    }

    /**
     * Multibyte-safe str_split funciton.
     *
     * @param string $string The string to split into an array
     * @param integer $length The length of the chars to cut.
     * @since 1.0.12
     */
    public static function mb_str_split($string, $length = 1)
    {
        $array = [];
        $stringLength = mb_strlen($string, 'UTF-8');

        for ($i = 0; $i < $stringLength; $i += $length) {
            $array[] = mb_substr($string, $i, $length, 'UTF-8');
        }

        return $array;
    }

}
