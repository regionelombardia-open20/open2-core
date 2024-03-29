<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\utilities
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;

/**
 * Class StringUtils
 * @package open20\amos\core\utilities
 */
final class StringUtils
{
    /**
     * The empty `string` `''`.
     *
     * @var string
     */
    const EMPTY_STR = '';

    /**
     * Represents a failed index search.
     *
     * @var integer
     */
    const INDEX_NOT_FOUND = -1;

    // @codeCoverageIgnoreStart

    /**
     * {@see StringUtils} instances can **NOT** be constructed in standard
     * programming.
     *
     * Instead, the class should be used as:
     *
     *     StringUtils::trim(' foo ');
     */
    protected function __construct()
    {
        // NOOP
    }
    // @codeCoverageIgnoreEnd

    /**
     * Returns the character at the specified index.
     *
     * An index ranges from `0` to `StringUtils::length() - 1`. The first
     * character of the sequence is at index `0`, the next at index `1`, and so
     * on, as for array indexing.
     *
     * @param string $str The `string` to return the character from.
     * @param integer $index The index of the character.
     *
     * @return string The character at the specified index of the `string`. The
     *    first character is at index `0`.
     * @throws OutOfBoundsException If the $index argument is negative or not
     *    less than the length of the `string`.
     */
    public static function charAt($str, $index)
    {
        if (false === substr($str, $index, 1)) {
            throw new \OutOfBoundsException;
        }
        return substr($str, $index, 1);
    }

    /**
     * Removes one newline from end of a string if it's there, otherwise leave
     * it alone.
     *
     * A newline is "`\n`", "`\r`", or "`\r\n`".
     *
     *     StringUtils::chomp(null);          // null
     *     StringUtils::chomp('');            // ''
     *     StringUtils::chomp("abc \r");      // 'abc '
     *     StringUtils::chomp("abc\n");       // 'abc'
     *     StringUtils::chomp("abc\r\n");     // 'abc'
     *     StringUtils::chomp("abc\r\n\r\n"); // "abc\r\n"
     *     StringUtils::chomp("abc\n\r");     // "abc\n"
     *     StringUtils::chomp("abc\n\rabc");  // "abc\n\rabc"
     *     StringUtils::chomp("\r");          // ''
     *     StringUtils::chomp("\n");          // ''
     *     StringUtils::chomp("\r\n");        // ''
     *
     * @param string $str The `string` to chomp a newline from.
     *
     * @return string The `string` $str without newline, `null` if `null`
     *    `string` input.
     */
    public static function chomp($str)
    {
        if (true === self::isEmpty($str)) {
            return $str;
        }
        if (1 === self::length($str)) {
            $firstChar = self::charAt($str, 0);
            if ("\r" === $firstChar || "\n" === $firstChar) {
                return self::EMPTY_STR;
            }
            return $str;
        }
        $lastIndex = self::length($str) - 1;
        $lastChar  = self::charAt($str, $lastIndex);
        if ("\n" === $lastChar) {
            if ("\r" === self::charAt($str, $lastIndex - 1)) {
                --$lastIndex;
            }
        } elseif ("\r" !== $lastChar) {
            ++$lastIndex;
        }
        return self::substring($str, 0, $lastIndex);
    }

    /**
     * Remove the specified last character from a `string`.
     *
     * If the `string` ends in `\r\n`, then remove both of them.
     *
     *     StringUtils::chop(null);       // null
     *     StringUtils::chop('');         // ''
     *     StringUtils::chop("abc \r");   // 'abc '
     *     StringUtils::chop("abc\n");    // 'abc'
     *     StringUtils::chop("abc\r\n");  // 'abc'
     *     StringUtils::chop('abc');      // 'ab'
     *     StringUtils::chop("abc\nabc"); // "abc\nab"
     *     StringUtils::chop('a');        // ''
     *     StringUtils::chop("\r");       // ''
     *     StringUtils::chop("\n");       // ''
     *     StringUtils::chop("\r\n");     // ''
     *
     * @param string $str The `string` to chop the last character from.
     *
     * @return string|null The `string` without the last character; `null` if
     *    `null` `string` input.
     */
    public static function chop($str)
    {
        if (true === self::isEmpty($str)) {
            return $str;
        }
        if ("\r\n" === \substr($str, -2)) {
            return \substr($str, 0, -2);
        }
        return \substr($str, 0, -1);
    }

    /**
     * Checks if a `string` is whitespace, empty (`''`) or `null`.
     *
     *     StringUtils::isBlank(null);      // true
     *     StringUtils::isBlank('');        // true
     *     StringUtils::isBlank(' ');       // true
     *     StringUtils::isBlank('bob');     // false
     *     StringUtils::isBlank('  bob  '); // false
     *
     * @param string $str The `string` to check.
     *
     * @return boolean `true` if the `string` is `null`, empty or whitespace;
     *    `false` otherwise.
     */
    public static function isBlank($str)
    {
        return self::EMPTY_STR === \trim($str);
    }

    /**
     * Checks if a `string` is not empty (`''`), not `null` and not whitespace
     * only.
     *
     *     StringUtils::isNotBlank(null);      // false
     *     StringUtils::isNotBlank('');        // false
     *     StringUtils::isNotBlank(' ');       // false
     *     StringUtils::isNotBlank('bob');     // true
     *     StringUtils::isNotBlank('  bob  '); // true
     *
     * @param string $str The `string` to check.
     *
     * @return boolean `true` if the `string` is not empty and not `null` and
     *    not whitespace; `false` otherwise.
     */
    public static function isNotBlank($str)
    {
        return false === self::isBlank($str);
    }

    /**
     * Checks if a `string` is empty (`''`) or `null`.
     *
     *     StringUtils::isEmpty(null);      // true
     *     StringUtils::isEmpty('');        // true
     *     StringUtils::isEmpty(' ');       // false
     *     StringUtils::isEmpty('bob');     // false
     *     StringUtils::isEmpty('  bob  '); // false
     *
     * @param string $str The `string` to check.
     *
     * @return boolean `true` if the `string` is empty or `null`, `false`
     *    otherwise.
     */
    public static function isEmpty($str)
    {
        return (self::EMPTY_STR === $str) || (null === $str);
    }

    /**
     * Checks if a `string` is not empty (`''`) and not `null`.
     *
     *     StringUtils::isNotEmpty(null);      // false
     *     StringUtils::isNotEmpty('');        // false
     *     StringUtils::isNotEmpty(' ');       // true
     *     StringUtils::isNotEmpty('bob');     // true
     *     StringUtils::isNotEmpty('  bob  '); // true
     *
     * @param string $str The `string` to check.
     *
     * @return boolean `true` if the `string` is not empty or `null`, `false`
     *    otherwise.
     */
    public static function isNotEmpty($str)
    {
        return false === self::isEmpty($str);
    }

    /**
     * Returns the length of a `string` or `0` if the `string` is `null`.
     *
     * @param string $str The `string` to check.
     *
     * @return integer The length of the `string` or `0` if the `string` is
     *    `null`.
     */
    public static function length($str)
    {
        return \strlen($str);
    }

    /**
     * Converts a `string` to lower case.
     *
     * A `null` input `string` returns `null`.
     *
     *     StringUtils::lowerCase(null)  = null
     *     StringUtils::lowerCase('')    = ''
     *     StringUtils::lowerCase('aBc') = 'abc'
     *
     * @param string $str The `string` to lower case.
     *
     * @return string The lower cased `string` or `null` if `null` `string`
     *    input.
     */
    public static function lowerCase($str)
    {
        return (true === self::isEmpty($str)) ? $str : strtolower($str);
    }

    /**
     * Converts a `string` to upper case.
     *
     * A `null` input `string` returns `null`.
     *
     *     StringUtils::upperCase(null)  = null
     *     StringUtils::upperCase('')    = ''
     *     StringUtils::upperCase('aBc') = 'ABC'
     *
     * @param string $str The `string` to upper case.
     *
     * @return string The upper cased `string` or `null` if `null` `string`
     *    input.
     */
    public static function upperCase($str)
    {
        return (true === self::isEmpty($str)) ? $str : strtoupper($str);
    }

    /**
     * Replaces a `string` with another `string` inside a larger `string`, for
     * the first maximum number of values to replace of the search `string`.
     *
     *     StringUtils::replace(null, *, *, *)         // null
     *     StringUtils::replace('', *, *, *)           // ''
     *     StringUtils::replace('any', null, *, *)     // 'any'
     *     StringUtils::replace('any', *, null, *)     // 'any'
     *     StringUtils::replace('any', '', *, *)       // 'any'
     *     StringUtils::replace('any', *, *, 0)        // 'any'
     *     StringUtils::replace('abaa', 'a', null, -1) // 'abaa'
     *     StringUtils::replace('abaa', 'a', '', -1)   // 'b'
     *     StringUtils::replace('abaa', 'a', 'z', 0)   // 'abaa'
     *     StringUtils::replace('abaa', 'a', 'z', 1)   // 'zbaa'
     *     StringUtils::replace('abaa', 'a', 'z', 2)   // 'zbza'
     *     StringUtils::replace('abaa', 'a', 'z', -1)  // 'zbzz'
     *
     * @param string $text The `string` to search and replace in.
     * @param string $search The `string` to search for.
     * @param string $replace The `string` to replace $search with.
     * @param integer $max The maximum number of values to replace, or `-1`
     *                         if no maximum.
     *
     * @return string The text with any replacements processed or `null` if
     *    `null` `string` input.
     */
    public static function replaceReg($text, $search, $replace, $max = -1)
    {
        if ((true === self::isEmpty($text)) || (true === self::isEmpty($search)) || (null === $replace) || (0 === $max)
        ) {
            return $text;
        }
        return \preg_replace(
            '/'.\preg_quote($search).'/', $replace, $text, $max
        );
    }

    /**
     * Replaces a `string` with another `string` inside a larger `string`, for
     * the first maximum number of values to replace of the search `string`.
     *
     *     StringUtils::replace(null, *, *, *)         // null
     *     StringUtils::replace('', *, *, *)           // ''
     *     StringUtils::replace('any', null, *, *)     // 'any'
     *     StringUtils::replace('any', *, null, *)     // 'any'
     *     StringUtils::replace('any', '', *, *)       // 'any'
     *     StringUtils::replace('any', *, *, 0)        // 'any'
     *     StringUtils::replace('abaa', 'a', null, -1) // 'abaa'
     *     StringUtils::replace('abaa', 'a', '', -1)   // 'b'
     *     StringUtils::replace('abaa', 'a', 'z', 0)   // 'abaa'
     *     StringUtils::replace('abaa', 'a', 'z', 1)   // 'zbaa'
     *     StringUtils::replace('abaa', 'a', 'z', 2)   // 'zbza'
     *     StringUtils::replace('abaa', 'a', 'z', -1)  // 'zbzz'
     *
     * @param string $text The `string` to search and replace in.
     * @param string $search The `string` to search for.
     * @param string $replace The `string` to replace $search with.
     * @param integer $max The maximum number of values to replace, or `-1`
     *                         if no maximum.
     *
     * @return string The text with any replacements processed or `null` if
     *    `null` `string` input.
     */
    public static function replace($text, $search, $replace, $max = null)
    {
        if ((true === self::isEmpty($text)) || (true === self::isEmpty($search)) || (null === $replace) || (0 === $max)
        ) {
            return $text;
        }
        return str_replace(
            $search, $replace, $text, $max
        );
    }
    /* -------------------------------------------------------------------------
     * Trim
     * ---------------------------------------------------------------------- */

    /**
     * Removes control characters (char &lt;= 32) from both ends of a `string`,
     * handling `null` by returning `null`.
     *
     * This method removes start and end characters &lt;= 32. To strip
     * whitespace use {@see strip}.
     *
     * To trim your choice of characters, use the {@see strip} method.
     *
     *     StringUtils::trim(null);          // null
     *     StringUtils::trim('');            // ''
     *     StringUtils::trim('     ');       // ''
     *     StringUtils::trim('abc');         // 'abc'
     *     StringUtils::trim('    abc    '); // 'abc'
     *
     * @param string $str The `string` to be trimmed.
     *
     * @return string The trimmed `string` or `null` if `null` `string` input.
     */
    public static function trim($str)
    {
        return (true === self::isEmpty($str)) ? $str : self::trimToEmpty($str);
    }

    /**
     * Removes control characters (char &lt;= 32) from both ends of a `string`
     * returning an empty `string` (`''`) if the `string` is empty (`''`) after
     * the trim or if it is `null`.
     *
     * This method removes start and end characters &lt;= 32. To strip
     * whitespace use {@see stripToEmpty}.
     *
     *     StringUtils::trimToEmpty(null);          // ''
     *     StringUtils::trimToEmpty('');            // ''
     *     StringUtils::trimToEmpty('     ');       // ''
     *     StringUtils::trimToEmpty('abc');         // 'abc'
     *     StringUtils::trimToEmpty('    abc    '); // 'abc'
     *
     * @param string $str The `string` to be trimmed.
     *
     * @return string The trimmed `string` or an empty `string` if `null` input.
     */
    public static function trimToEmpty($str)
    {
        return \trim($str);
    }

    /**
     * Removes control characters (char &lt;= 32) from both ends of a `string`
     * returning `null` if the `string` is empty (`''`) after the trim or if it
     * is `null`.
     *
     * This method removes start and end characters &lt;= 32. To strip
     * whitespace use {@see stripToNull}.
     *
     *     StringUtils::trimToNull(null);          // null
     *     StringUtils::trimToNull('');            // null
     *     StringUtils::trimToNull('     ');       // null
     *     StringUtils::trimToNull('abc');         // 'abc'
     *     StringUtils::trimToNull('    abc    '); // 'abc'
     *
     * @param string $str The `string` to be trimmed.
     *
     * @return string|null The trimmed `string' or `null` if only chars
     *    &lt;= 32, empty or `null` `string` input.
     */
    public static function trimToNull($str)
    {
        $str = self::trimToEmpty($str);
        return (true === self::isEmpty($str)) ? null : $str;
    }
    /* -------------------------------------------------------------------------
     * Strip
     * ---------------------------------------------------------------------- */

    /**
     * Strips any of a set of characters from the start and end of a `string`.
     *
     * This is similar to {@see trim} but allows the characters to be stripped
     * to be controlled.
     *
     * A `null` input `string` returns `null`.
     * An empty string (`''`) input returns the empty `string`.
     *
     * If the `string` for the characters to remove is `null`, whitespace is
     * stripped.
     *
     *     StringUtils::strip(null, *);          // null
     *     StringUtils::strip('', *);            // ''
     *     StringUtils::strip('abc', null);      // 'abc'
     *     StringUtils::strip('  abc', null);    // 'abc'
     *     StringUtils::strip('abc  ', null);    // 'abc'
     *     StringUtils::strip(' abc ', null);    // 'abc'
     *     StringUtils::strip('  abcyx', 'xyz'); // '  abc'
     *
     * @param string $str The `string` to remove characters from.
     * @param string $chars The characters to remove. `null` is treated as
     *    whitespace.
     *
     * @return string|null The stripped `string` or `null` if `null` `string`
     *    input.
     */
    public static function strip($str, $chars)
    {
        return (true === self::isEmpty($str)) ? $str : self::stripEnd(self::stripStart($str, $chars), $chars);
    }

    /**
     * Strips whitespace from the start and end of a `string` returning an empty
     * `string` if `null` input.
     *
     * This is similar to {@see trimToEmpty} but removes whitespace.
     *
     *     StringUtils::stripToEmpty(null);     // ''
     *     StringUtils::stripToEmpty('');       // ''
     *     StringUtils::stripToEmpty('   ');    // ''
     *     StringUtils::stripToEmpty('abc');    // 'abc'
     *     StringUtils::stripToEmpty('  abc');  // 'abc'
     *     StringUtils::stripToEmpty('abc  ');  // 'abc'
     *     StringUtils::stripToEmpty(' abc ');  // 'abc'
     *     StringUtils::stripToEmpty(' ab c '); // 'ab c'
     *
     * @param string $str The `string` to be stripped.
     *
     * @return string The stripped `string` or an empty `string` if `null`
     *    input.
     */
    public static function stripToEmpty($str)
    {
        return (null === $str) ? self::EMPTY_STR : self::strip($str, null);
    }

    /**
     * Strips whitespace from the start and end of a `string` returning `null`
     * if the `string` is empty (`''`) after the strip.
     *
     * This is similar to {@see trimToNull} but removes whitespace.
     *
     *     StringUtils::stripToNull(null);     // null
     *     StringUtils::stripToNull('');       // null
     *     StringUtils::stripToNull('   ');    // null
     *     StringUtils::stripToNull('abc');    // 'abc'
     *     StringUtils::stripToNull('  abc');  // 'abc'
     *     StringUtils::stripToNull('abc  ');  // 'abc'
     *     StringUtils::stripToNull(' abc ');  // 'abc'
     *     StringUtils::stripToNull(' ab c '); // 'ab c'
     *
     * @param string $str The `string` to be stripped.
     *
     * @return string|null The stripped `string` or `null` if whitespace, empty
     *    or `null` `string` input.
     */
    public static function stripToNull($str)
    {
        $str = self::strip($str, null);
        return (0 === self::length($str)) ? null : $str;
    }

    /**
     * Strips any of a set of characters from the start of a `string`.
     *
     * A `null` input `string` returns `null`.
     * An empty string (`''`) input returns the empty `string`.
     *
     * If the `string` for the characters to remove is `null`, whitespace is
     * stripped.
     *
     *     StringUtils::stripStart(null, *);          // null
     *     StringUtils::stripStart('', *);            // ''
     *     StringUtils::stripStart('abc', '');        // 'abc'
     *     StringUtils::stripStart('abc', null);      // 'abc'
     *     StringUtils::stripStart('  abc', null);    // 'abc'
     *     StringUtils::stripStart('abc  ', null);    // 'abc  '
     *     StringUtils::stripStart(' abc ', null);    // 'abc '
     *     StringUtils::stripStart('yxabc  ', 'xyz'); // 'abc  '
     *
     * @param string $str The `string` to remove characters from.
     * @param string $chars The characters to remove. `null` is treated as
     *    whitespace.
     *
     * @return string|null The stripped `string` or `null` if `null` `string`
     *    input.
     */
    public static function stripStart($str, $chars)
    {
        if (true === self::isEmpty($str)) {
            return $str;
        }
        return (null === $chars) ? \ltrim($str) : \ltrim($str, $chars);
    }

    /**
     * Strips any of a set of characters from the end of a `string`.
     *
     * A `null` input `string` returns `null`.
     * An empty string (`''`) input returns the empty `string`.
     *
     * If the `string` for the characters to remove is `null`, whitespace is
     * stripped.
     *
     *     StringUtils::stripEnd(null, *)          = null
     *     StringUtils::stripEnd('', *)            = ''
     *     StringUtils::stripEnd('abc', '')        = 'abc'
     *     StringUtils::stripEnd('abc', null)      = 'abc'
     *     StringUtils::stripEnd('  abc', null)    = '  abc'
     *     StringUtils::stripEnd('abc  ', null)    = 'abc'
     *     StringUtils::stripEnd(' abc ', null)    = ' abc'
     *     StringUtils::stripEnd('  abcyx', 'xyz') = '  abc'
     *
     * @param string $str The `string` to remove characters from.
     * @param string $chars The characters to remove. `null` is treated as
     *    whitespace.
     *
     * @return string|null The stripped `string` or `null` if `null` `string`
     *    input.
     */
    public static function stripEnd($str, $chars)
    {
        if (true === self::isEmpty($str)) {
            return $str;
        }
        return (null === $chars) ? \rtrim($str) : \rtrim($str, $chars);
    }
    /* -------------------------------------------------------------------------
     * Compare
     * ---------------------------------------------------------------------- */

    /**
     * Compares two `string`s lexicographically.
     *
     * The comparison is based on the Unicode value of each character in the
     * `string`s. The character sequence represented by the first `string` is
     * compared lexicographically to the character sequence represented by the
     * second `string`. The result is a negative integer if the first `string`
     * lexicographically precedes the second `string`. The result is a positive
     * integer if the first `string` lexicographically follows the second
     * `string`.
     *
     * This method returns an integer whose sign is that of calling {@see
     * compare} with normalized versions of the `string`s.
     *
     * @param string $str1 The first `string` to be compared.
     * @param string $str2 The second `string` to be compared.
     *
     * @return integer A negative integer, zero, or a positive integer as the
     *    first `string` is less than, equal to, or greater than the second
     *    `string`.
     */
    public static function compare($str1, $str2)
    {
        return \strcmp($str1, $str2);
    }

    /**
     * Compares two `string`s lexicographically, ignoring case differences.
     *
     * This method returns an integer whose sign is that of calling {@see
     * compare} with normalized versions of the `string`s.
     *
     * @param string $str1 The first `string` to be compared.
     * @param string $str2 The second `string` to be compared.
     *
     * @return integer A negative integer, zero, or a positive integer as the
     *    first `string` is greater than, equal to, or less than the second
     *    `string`, ignoring case considerations.
     */
    public static function compareIgnoreCase($str1, $str2)
    {
        return \strcasecmp($str2, $str1);
    }
    /* -------------------------------------------------------------------------
     * Equals
     * ---------------------------------------------------------------------- */

    /**
     * Compares two `string`s, returning `true` if they are equal.
     *
     * `null`s are handled without exceptions. Two `null` references are
     * considered to be equal. The comparison is case sensitive.
     *
     *     StringUtils::equals(null, null);   // true
     *     StringUtils::equals(null, 'abc');  // false
     *     StringUtils::equals('abc', null);  // false
     *     StringUtils::equals('abc', 'abc'); // true
     *     StringUtils::equals('abc', 'ABC'); // false
     *
     * @param string $str1 The first `string`.
     * @param string $str2 The second `string`.
     *
     * @return boolean `true` if the `string`s are equal, case sensitive, or
     *    both `null`.
     */
    public static function equal($str1, $str2)
    {
        return (null === $str1) ? (null === $str2) : ($str1 === $str2);
    }

    /**
     * Compares two `string`s, returning `true` if they are equal ignoring the
     * case.
     *
     * `null`s are handled without exceptions. Two `null` references are
     * considered to be equal. The comparison is case insensitive.
     *
     *     StringUtils::equalsIgnoreCase(null, null);   // true
     *     StringUtils::equalsIgnoreCase(null, 'abc');  // false
     *     StringUtils::equalsIgnoreCase('abc', null);  // false
     *     StringUtils::equalsIgnoreCase('abc', 'abc'); // true
     *     StringUtils::equalsIgnoreCase('abc', 'ABC'); // true
     *
     * @param string $str1 The first `string`.
     * @param string $str2 The second `string`.
     *
     * @return boolean `true` if the `string`s are equal, case insensitive, or
     *    both `null`.
     */
    public static function equalsIgnoreCase($str1, $str2)
    {
        return (null === $str1) ? (null === $str2) : (self::lowercase($str1) === self::lowercase($str2));
    }

    /**
     * Finds the first index within a `string` from a start position, handling
     * `null`.
     *
     * A `null` or empty (`''`) `string` will return `-1`.
     * A negative start position is treated as zero.
     * A start position greater than the string length returns `-1`.
     *
     *     StringUtils::indexOf(null, *, *);          // -1
     *     StringUtils::indexOf(*, null, *);          // -1
     *     StringUtils::indexOf('', '', 0);           // 0
     *     StringUtils::indexOf('aabaabaa', 'a', 0);  // 0
     *     StringUtils::indexOf('aabaabaa', 'b', 0);  // 2
     *     StringUtils::indexOf('aabaabaa', 'ab', 0); // 1
     *     StringUtils::indexOf('aabaabaa', 'b', 3);  // 5
     *     StringUtils::indexOf('aabaabaa', 'b', 9);  // -1
     *     StringUtils::indexOf('aabaabaa', 'b', -1); // 2
     *     StringUtils::indexOf('aabaabaa', '', 2);   // 2
     *     StringUtils::indexOf('abc', '', 9);        // 3
     *
     * @param string $str The `string` to check.
     * @param string $search The `string` to find.
     * @param integer $startPos The start position, negative treated as zero.
     *
     * @return integer The first index of the search character, `-1` if no match
     *    or `null` `string` input.
     */
    public static function indexOf($str, $search, $startPos = 0)
    {
        $result = self::validateIndexOf($str, $search, $startPos);
        if (true !== $result) {
            return $result;
        }
        if (true === self::isEmpty($search)) {
            return $startPos;
        }
        $pos = \strpos($str, $search, $startPos);
        return (false === $pos) ? -1 : $pos;
    }

    /**
     * Helper method for {@see indexOf} and {@see lastIndexOf}.
     *
     * @param string $str The `string` to check.
     * @param string $search The `string` to find.
     * @param integer $startPos The start position, negative treated as zero.
     *
     * @return integer|boolean `-1` if no match or `null` `string` input; `true`
     *    otherwise.
     */
    private static function validateIndexOf($str, $search, &$startPos)
    {
        if ((null === $str) || (null === $search)) {
            return -1;
        }
        $lengthSearch = self::length($search);
        $lengthStr    = self::length($str);
        if ((0 === $lengthSearch) && ($startPos >= $lengthStr)) {
            return $lengthStr;
        }
        if ($startPos >= $lengthStr) {
            return -1;
        }
        if (0 > $startPos) {
            $startPos = 0;
        }
        return true;
    }

    /**
     * Finds the first index within a `string`, handling `null`.
     *
     * A `null` `string` will return `-1`.
     * A negative start position returns `-1`. An empty (`''`) search `string`
     * always matches unless the start position is negative.
     * A start position greater than the `string` length searches the whole
     * `string`.
     *
     *     StringUtils::lastIndexOf(null, *, *);          // -1
     *     StringUtils::lastIndexOf(*, null, *);          // -1
     *     StringUtils::lastIndexOf('aabaabaa', 'a', 8);  // 7
     *     StringUtils::lastIndexOf('aabaabaa', 'b', 8);  // 5
     *     StringUtils::lastIndexOf('aabaabaa', 'ab', 8); // 4
     *     StringUtils::lastIndexOf('aabaabaa', 'b', 9);  // 5
     *     StringUtils::lastIndexOf('aabaabaa', 'b', -1); // -1
     *     StringUtils::lastIndexOf('aabaabaa', 'a', 0);  // 0
     *     StringUtils::lastIndexOf('aabaabaa', 'b', 0);  // -1
     *
     * @param string $str The `string` to check.
     * @param string $search The `string` to find.
     * @param integer $startPos The start position, negative treated as zero.
     *
     * @return integer The first index of the search `string`, `-1` if no match
     *    or null `string` input.
     */
    public static function lastIndexOf($str, $search, $startPos = 0)
    {
        $result = self::validateIndexOf($str, $search, $startPos);
        if (true !== $result) {
            return $result;
        }
        if (true === self::isEmpty($search)) {
            return $startPos;
        }
        $pos = \strrpos($str, $search, $startPos);
        return (false === $pos) ? -1 : $pos;
    }
    /* -------------------------------------------------------------------------
     * Split
     * ---------------------------------------------------------------------- */

    /**
     * Splits the provided text into an `array` with a maximum length,
     * separators specified.
     *
     * The separator is not included in the returned `string` `array`. Adjacent
     * separators are treated as one separator. A `null` input `string` returns
     * `null`. A `null` $chars splits on whitespace. If more than $max
     * delimited substrings are found, the returned `string` includes all
     * characters after the first `$max - 1` returned `string`s (including
     * separator characters).
     *
     *     StringUtils::split(null, null, null);      // null
     *     StringUtils::split('', null, null);        // []
     *     StringUtils::split('ab cd ef', null, 0);   // ['ab', 'cd', 'ef']
     *     StringUtils::split('ab   cd ef', null, 0); // ['ab', 'cd', 'ef']
     *     StringUtils::split('ab:cd:ef', ':', 0);    // ['ab', 'cd', 'ef']
     *     StringUtils::split('ab:cd:ef', ':', 2);    // ['ab', 'cd:ef']
     *
     * @param string $str The `string` to parse.
     * @param string $chars The characters used as the delimiters, `null`
     *    splits on whitespace.
     * @param integer $max The maximum number of elements to include in the
     *    `array.` A zero or negative value implies no limit.
     *
     * @return array|null An `array` of parsed `string`s, `null` if `null`
     *    `string` input.
     */
    public static function split($str, $chars = null, $max = 0)
    {
        $result = self::EMPTY_STR;
        if (null === $str) {
            return null;
        }
        if (self::EMPTY_STR === $str) {
            return array();
        }
        if (null === $chars) {
            $result = \preg_split('/\s+/', $str, $max);
        } elseif ($max > 0) {
            $result = \explode($chars, $str, $max);
        } else {
            $result = \explode($chars, $str);
        }
        return $result;
    }

    /**
     * Gets a substring from the specified `string` avoiding exceptions.
     *
     * A negative start position can be used to start/end *n* characters from
     * the end of the `string`.
     *
     * The returned substring starts with the character in the `$start` position
     * and ends before the `$end` position. All position counting is zero-based
     * -- i.e., to start at the beginning of the `string` use `$start = 0`.
     *
     * Negative start and end positions can be used to specify offsets relative
     * to the end of the `string`.
     *
     * If `$start` is not strictly to the left of `$end`, the empty string is
     * returned.
     *
     *     StringUtils::substring(null, *);       // null
     *     StringUtils::substring('', *);         // ''
     *     StringUtils::substring('abc', 0);      // 'abc'
     *     StringUtils::substring('abc', 2);      // 'c'
     *     StringUtils::substring('abc', 4);      // ''
     *     StringUtils::substring('abc', -2);     // 'bc'
     *     StringUtils::substring('abc', -4);     // 'abc'
     *     StringUtils::substring(null, *, *);    // null
     *     StringUtils::substring('', * ,  *);    // '';
     *     StringUtils::substring('abc', 0, 2);   // 'ab'
     *     StringUtils::substring('abc', 2, 0);   // ''
     *     StringUtils::substring('abc', 2, 4);   // 'c'
     *     StringUtils::substring('abc', 4, 6);   // ''
     *     StringUtils::substring('abc', 2, 2);   // ''
     *     StringUtils::substring('abc', -2, -1); // 'b'
     *     StringUtils::substring('abc', -4, 2);  // 'ab'
     *
     * @param string $str The `string` to get the substring from.
     * @param integer $start The position to start from, negative means count
     *    back from the end of the `string` by this many characters.
     * @param integer $end The position to end at (exclusive), negative means
     *    count back from the end of the `string` by this many characters.
     *
     * @return string|null The substring from start position to end position,
     *    `null` if `null` `string` input.
     */
    public static function substring($str, $start, $end = null)
    {
        if ((0 > $start) && (0 < $end)) {
            $start = 0;
        }
        if (null === $end) {
            $end = self::length($str);
        }
        return \substr($str, $start, $end - $start);
    }

    /**
     * Gets the substring after the first occurrence of a separator.
     *
     * The separator is not returned.
     *
     * A `null` `string` input will return `null`.
     * An empty (`''`) `string` input will return the empty `string`.
     * A `null` separator will return the empty `string` if the input `string`
     * is not `null`.
     *
     * If nothing is found, the empty `string` is returned.
     *
     *     StringUtils::substringAfter(null, *);      // null
     *     StringUtils::substringAfter('', *);        // ''
     *     StringUtils::substringAfter(*, null);      // ''
     *     StringUtils::substringAfter('abc', 'a');   // 'bc'
     *     StringUtils::substringAfter('abcba', 'b'); // 'cba'
     *     StringUtils::substringAfter('abc', 'c');   // ''
     *     StringUtils::substringAfter('abc', 'd');   // ''
     *     StringUtils::substringAfter('abc', '');    // 'abc'
     *
     * @param string $str The `string` to get a substring from.
     * @param string $separator The `string` to search for.
     *
     * @return string|null The substring after the first occurrence of the
     *    separator, `null` if `null` `string` input.
     */
    public static function substringAfter($str, $separator)
    {
        if (true === self::isEmpty($str)) {
            return $str;
        }
        if (null === $separator) {
            return self::EMPTY_STR;
        }
        $pos = self::indexOf($str, $separator);
        if (self::INDEX_NOT_FOUND === $pos) {
            return self::EMPTY_STR;
        }
        return self::substring($str, $pos + self::length($separator));
    }

    /**
     * Gets the substring after the last occurrence of a separator.
     *
     * The separator is not returned.
     *
     * A `null` `string` input will return `null`.
     * An empty (`''`) `string` input will return the empty `string`.
     * An empty or `null` separator will return the empty `string` if the input
     * `string` is not `null`.
     *
     * If nothing is found, the empty `string` is returned.
     *
     *     StringUtils::substringAfterLast(null, *);      // null
     *     StringUtils::substringAfterLast('', *);        // ''
     *     StringUtils::substringAfterLast(*, '');        // ''
     *     StringUtils::substringAfterLast(*, null);      // ''
     *     StringUtils::substringAfterLast('abc', 'a');   // 'bc'
     *     StringUtils::substringAfterLast('abcba', 'b'); // 'a'
     *     StringUtils::substringAfterLast('abc', 'c');   // ''
     *     StringUtils::substringAfterLast('a', 'a');     // ''
     *     StringUtils::substringAfterLast('a', 'z');     // ''
     *
     * @param string $str The `string` to get a substring from.
     * @param string $separator The `string` to search for.
     *
     * @return string|null The substring after the last occurrence of the
     *    separator, `null` if `null` `string` input.
     */
    public static function substringAfterLast($str, $separator)
    {
        if (true === self::isEmpty($str)) {
            return $str;
        }
        if (true === self::isEmpty($separator)) {
            return self::EMPTY_STR;
        }
        $pos = self::lastIndexOf($str, $separator);
        if (self::INDEX_NOT_FOUND === $pos || (self::length($str) - self::length($separator)) === $pos
        ) {
            return self::EMPTY_STR;
        }
        return self::substring($str, $pos + self::length($separator));
    }

    /**
     * Gets the substring before the first occurrence of a separator.
     *
     * The separator is not returned.
     *
     * A `null` `string` input will return `null`.
     * An empty (`''`) `string` input will return the empty `string`.
     * A `null` separator will return the input string.
     *
     * If nothing is found, the `string` input is returned.
     *
     *     StringUtils::substringBefore(null, *);      // null
     *     StringUtils::substringBefore('', *);        // ''
     *     StringUtils::substringBefore('abc', 'a');   // ''
     *     StringUtils::substringBefore('abcba', 'b'); // 'a'
     *     StringUtils::substringBefore('abc', 'c');   // 'ab'
     *     StringUtils::substringBefore('abc', 'd');   // 'abc'
     *     StringUtils::substringBefore('abc', '');    // ''
     *     StringUtils::substringBefore('abc', null);  // 'abc'
     *
     * @param string $str The `string` to get a substring from.
     * @param string $separator The `string` to search for.
     *
     * @return string|null The substring before the first occurrence of the
     *    separator, `null` if `null` `string` input.
     */
    public static function substringBefore($str, $separator)
    {
        if ((true === self::isEmpty($str)) || (null === $separator)) {
            return $str;
        }
        if (0 === self::length($separator)) {
            return self::EMPTY_STR;
        }
        $pos = self::indexOf($str, $separator);
        if (self::INDEX_NOT_FOUND === $pos) {
            return $str;
        }
        return self::substring($str, 0, $pos);
    }

    /**
     * Gets the substring before the last occurrence of a separator.
     *
     * The separator is not returned.
     *
     * A `null` `string` input will return `null`.
     * An empty (`''`) `string` input will return the empty `string`.
     * An empty or `null` separator will return the input `string`.
     *
     * If nothing is found, the `string` input is returned.
     *
     *     StringUtils::substringBeforeLast(null, *);      // null
     *     StringUtils::substringBeforeLast('', *);        // ''
     *     StringUtils::substringBeforeLast('abcba', 'b'); // 'abc'
     *     StringUtils::substringBeforeLast('abc', 'c');   // 'ab'
     *     StringUtils::substringBeforeLast('a', 'a');     // ''
     *     StringUtils::substringBeforeLast('a', 'z');     // 'a'
     *     StringUtils::substringBeforeLast('a', null);    // 'a'
     *     StringUtils::substringBeforeLast('a', '');      // 'a'
     *
     * @param string $str The `string` to get a substring from.
     * @param string $separator The `string` to search for.
     *
     * @return string|null The substring before the last occurrence of the
     *    seperator, `null` if `null` `string` input.
     */
    public static function substringBeforeLast($str, $separator)
    {
        if ((true === self::isEmpty($str)) || (true === self::isEmpty($separator))
        ) {
            return $str;
        }
        $pos = self::lastIndexOf($str, $separator);
        if (self::INDEX_NOT_FOUND === $pos) {
            return $str;
        }
        return self::substring($str, 0, $pos);
    }

    /**
     * Gets the `string` that is nested in between two `string`s.
     *
     * Only the first match is returned.
     *
     * A `null` input `string` returns `null`. A `null` `$open`/`$close` returns
     * `null` (no match). An empty (`''`) `$open` and `$close` returns an empty
     * `string`.
     *
     *     StringUtils::substringBetween('wx[b]yz', '[', ']');    // 'b'
     *     StringUtils::substringBetween(null, *, *);             // null
     *     StringUtils::substringBetween(*, null, *);             // null
     *     StringUtils::substringBetween(*, *, null);             // null
     *     StringUtils::substringBetween('', '', '');             // ''
     *     StringUtils::substringBetween('', '', ']');            // null
     *     StringUtils::substringBetween('', '[', ']');           // null
     *     StringUtils::substringBetween('yabcz', '', '');        // ''
     *     StringUtils::substringBetween('yabcz', 'y', 'z');      // 'abc'
     *     StringUtils::substringBetween('yabczyabcz', 'y', 'z'); // 'abc'
     *
     * @param string $str The `string` containing the substrings, `null`
     *    returns `null`, empty returns empty.
     * @param string $open The `string` identifying the start of the substring,
     *    empty returns `null`.
     * @param string $close The `string` identifying the end of the substring,
     *    empty returns `null`.
     *
     * @return string|null The `string` after the substring, `null` if no match.
     */
    public static function substringBetween($str, $open, $close = null)
    {
        $result = null;
        if (null === $close) {
            $close = $open;
        }
        $startPos = self::indexOf($str, $open);
        if (self::INDEX_NOT_FOUND !== $startPos) {
            $startPos += self::length($open);
            $endPos   = self::indexOf($str, $close, $startPos);
            if (self::INDEX_NOT_FOUND !== $endPos) {
                $result = self::substring($str, $startPos, $endPos);
            }
        }
        return $result;
    }
    /* -------------------------------------------------------------------------
     * Capitalizing
     * ---------------------------------------------------------------------- */

    /**
     * Capitalizes a `string` changing the first letter to upper case.
     *
     * No other letters are changed. For a word based algorithm, see {@see
     * capitalize}. A `null` input `string` returns `null`.
     *
     *     StringUtils::capitalize(null);  // null
     *     StringUtils::capitalize('');    // ''
     *     StringUtils::capitalize('cat'); // 'Cat'
     *     StringUtils::capitalize('cAt'); // 'CAt'
     *
     * @param string $str The `string` to capitalize.
     *
     * @return string|null The capitalized `string` or `null` if `null` `string`
     *    input.
     */
    public static function capitalize($str)
    {
        return \ucfirst($str);
    }

    /**
     * Uncapitalizes a `string` changing the first letter to lower case.
     *
     * No other letters are changed. For a word based algorithm, see {@see
     * uncapitalize}. A `null` input `string` returns `null`.
     *
     *     StringUtils::uncapitalize(null);  // null
     *     StringUtils::uncapitalize('');    // ''
     *     StringUtils::uncapitalize('Cat'); // 'cat'
     *     StringUtils::uncapitalize('CAT'); // 'cAT'
     *
     * @param string $str The `string` to uncapitalize.
     *
     * @return string|null The uncapitalized `string` or `null` if `null`
     *    `string` input.
     */
    public static function uncapitalize($str)
    {
        return \lcfirst($str);
    }
    /* -------------------------------------------------------------------------
     * Repeating
     * ---------------------------------------------------------------------- */

    /**
     * Repeats a `string` the specified number of times to form a new `string`,
     * with a specified `string` injected each time.
     *
     *     StringUtils::repeat(null, 2, null); // null
     *     StringUtils::repeat(null, 2, 'x');  // null
     *     StringUtils::repeat('', 0, null);   // ''
     *     StringUtils::repeat('', 2, '');     // ''
     *     StringUtils::repeat('', 3, 'x');    // 'xxx'
     *     StringUtils::repeat('?', 3, ', ');  // '?, ?, ?'
     *
     * @param string $str The `string` to repeat.
     * @param integer $repeat The number of times to repeat $str, negative
     *    treated as zero.
     * @param string $separator The `string` to inject.
     *
     * @return string|null The capitalized `string` or `null` if `null` `string`
     *    input.
     */
    public static function repeat($str, $repeat, $separator = null)
    {
        $result = self::EMPTY_STR;
        if ((null === $str) || (null === $separator)) {
            $result = \str_repeat($str, $repeat);
        } else {
            $result = \str_repeat($str.$separator, $repeat);
            if (true === self::isNotEmpty($str)) {
                $result = self::removeEnd($result, $separator);
            }
        }
        return $result;
    }
    /* -------------------------------------------------------------------------
     * Remove
     * ---------------------------------------------------------------------- */

    /**
     * Checks if a `string` ends with a specified suffix.
     *
     * `null`s are handled without exceptions. Two `null` references are
     * considered to be equal. The comparison is case sensitive.
     *
     *     StringUtils::endsWith(null, null);      // true
     *     StringUtils::endsWith(null, 'def');     // false
     *     StringUtils::endsWith('abcdef', null);  // false
     *     StringUtils::endsWith('abcdef', 'def'); // true
     *     StringUtils::endsWith('ABCDEF', 'def'); // false
     *     StringUtils::endsWith('ABCDEF', 'cde'); // false
     *
     * @param string $str The `string` to check.
     * @param string $suffix The suffix to find.
     *
     * @return boolean `true` if the `string` $str ends with the suffix $suffix,
     *    case sensitive, or both `null`.
     */
    public static function endsWith($str, $suffix)
    {
        return ((null === $str) && (null === $suffix)) ? true : self::substring(
                $str, self::length($str) - self::length($suffix)
            ) === $suffix;
    }

    /**
     * Checks if a `string` starts with a specified prefix.
     *
     * `null`s are handled without exceptions. Two `null` references are
     * considered to be equal. The comparison is case sensitive.
     *
     *     StringUtils::startsWith(null, null);      // true
     *     StringUtils::startsWith(null, 'abc');     // false
     *     StringUtils::startsWith('abcdef', null);  // false
     *     StringUtils::startsWith('abcdef', 'abc'); // true
     *     StringUtils::startsWith('ABCDEF', 'abc'); // false
     *
     * @param string $str The `string` to check.
     * @param string $prefix The prefix to find.
     *
     * @return boolean `true` if the `string` `$str` starts with the prefix
     *    `$prefix`, case sensitive, or both `null`.
     */
    public static function startsWith($str, $prefix)
    {
        return ((null === $str) && (null === $prefix)) ? true : self::substring($str, 0, self::length($prefix)) === $prefix;
    }
    /* -------------------------------------------------------------------------
     * Remove
     * ---------------------------------------------------------------------- */

    /**
     * Removes a substring only if it is at the end of a source `string`,
     * otherwise returns the source `string`.
     *
     * A `null` source `string` will return `null`.
     * An empty (`''`) source `string` will return the empty `string`.
     * A `null` search `string` will return the source `string`.
     *
     *     StringUtils::removeEnd(null, *);                    // null
     *     StringUtils::removeEnd('', *);                      // ''
     *     StringUtils::removeEnd(*, null);                    // *
     *     StringUtils::removeEnd('www.domain.com', '.com.');  // 'www.domain.com'
     *     StringUtils::removeEnd('www.domain.com', '.com');   // 'www.domain'
     *     StringUtils::removeEnd('www.domain.com', 'domain'); // 'www.domain.com'
     *     StringUtils::removeEnd('abc', '');                  // 'abc'
     *
     * @param string $str The source `string` to search.
     * @param string $remove The `string` to search for and remove.
     *
     * @return string|null The substring with the `string` removed if found,
     *    `null` if `null` `string` input.
     */
    public static function removeEnd($str, $remove)
    {
        if ((true === self::isEmpty($str)) || (true === self::isEmpty($remove))
        ) {
            return $str;
        }
        if (true === self::endsWith($str, $remove)) {
            return self::substring(
                    $str, 0, self::length($str) - self::length($remove)
            );
        }
        return $str;
    }

    /**
     * Removes a substring only if it is at the beginning of a source `string`,
     * otherwise returns the source `string`.
     *
     * A `null` source string will return `null`.
     * An empty (`''`) source `string` will return the empty `string`.
     * A `null` search `string` will return the source `string`.
     *
     *     StringUtils::removeStart(null, *);                    // null
     *     StringUtils::removeStart('', *);                      // ''
     *     StringUtils::removeStart(*, null);                    // *
     *     StringUtils::removeStart('www.domain.com', 'www.');   // 'domain.com'
     *     StringUtils::removeStart('domain.com', 'www.');       // 'domain.com'
     *     StringUtils::removeStart('www.domain.com', 'domain'); // 'www.domain.com'
     *     StringUtils::removeStart('abc', '');                  // 'abc'
     *
     * @param string $str The source `string` to search.
     * @param string $remove The `string` to search for and remove.
     *
     * @return string|null The substring with the `string` removed if found,
     *    `null` if `null` `string` input.
     */
    public static function removeStart($str, $remove)
    {
        if ((true === self::isEmpty($str)) || (true === self::isEmpty($remove))
        ) {
            return $str;
        }
        if (true === self::startsWith($str, $remove)) {
            return self::substring($str, self::length($remove));
        }
        return $str;
    }

    /**
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public static function contains($needle, $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }

    /**
     * @param $str
     * @param $word
     * @return bool
     */
    public static function containsWord($str, $word)
    {
        return !!preg_match('#\\b'.preg_quote($word, '#').'\\b#i', $str);
    }

    /**
     * Parse string and return limited one
     * @param $text
     * @param $char_limit
     * @return string
     */
    public static function shortText($text, $char_limit)
    {
        //Remove html tags
        $asString = strip_tags($text);

        //If already good string
        if (strlen($asString) < $char_limit) {
            return $asString;
        }

        if ($char_limit != -1) {

            //Limit string
            $asString = substr($asString, 0, $char_limit + 1);

            //Explode to array
            $arrayString = explode(' ', $asString);

            if (count($arrayString) > 1) {
                //Remove last word
                array_pop($arrayString);

                //Merge string
                $asString = implode(' ', $arrayString);
            }

            //Return it
            return $asString."...";
        } else {
            return $asString;
        }
    }

    /**
     * @param string $str
     * @return array[]|false|string[]
     */
    public static function splitAtUpperCase($str)
    {
        return preg_split('/(?=[A-Z])/', $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     *
     * @param string $str
     * @param integer $len
     * @param string $end
     * @return string
     */
    public static function truncateHTML($str, $len, $end = '&hellip;')
    {
        //find all tags
        $tagPattern = '/(<\/?)([\w]*)(\s*[^>]*)>?|&[\w#]+;/i';  //match html tags and entities
        preg_match_all($tagPattern, $str, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        //WSDDebug::dump($matches); exit;
        $i          = 0;
        //loop through each found tag that is within the $len, add those characters to the len,
        //also track open and closed tags
        // $matches[$i][0] = the whole tag string  --the only applicable field for html enitities
        // IF its not matching an &htmlentity; the following apply
        // $matches[$i][1] = the start of the tag either '<' or '</'
        // $matches[$i][2] = the tag name
        // $matches[$i][3] = the end of the tag
        //$matces[$i][$j][0] = the string
        //$matces[$i][$j][1] = the str offest

        while ($matches[$i][0][1] < $len && !empty($matches[$i])) {

            $len = $len + strlen($matches[$i][0][0]);
            if (substr($matches[$i][0][0], 0, 1) == '&') $len = $len - 1;


            //if $matches[$i][2] is undefined then its an html entity, want to ignore those for tag counting
            //ignore empty/singleton tags for tag counting
            if (!empty($matches[$i][2][0]) && !in_array($matches[$i][2][0],
                    array('br', 'img', 'hr', 'input', 'param', 'link'))) {
                //double check
                if (substr($matches[$i][3][0], -1) != '/' && substr($matches[$i][1][0], -1) != '/')
                        $openTags[] = $matches[$i][2][0];
                elseif (end($openTags) == $matches[$i][2][0]) {
                    array_pop($openTags);
                } else {
                    $warnings[] = "html has some tags mismatched in it:  $str";
                }
            }


            $i++;
        }

        $closeTags = '';

        if (!empty($openTags)) {
            $openTags = array_reverse($openTags);
            foreach ($openTags as $t) {
                $closeTagString .= "</".$t.">";
            }
        }

        if (strlen($str) > $len) {
            // Finds the last space from the string new length
            $lastWord = strpos($str, ' ', $len);
            if ($lastWord) {
                //truncate with new len last word
                $str            = substr($str, 0, $lastWord);
                //finds last character
                $last_character = (substr($str, -1, 1));
                //add the end text
                $truncated_html = ($last_character == '.' ? $str : ($last_character == ',' ? substr($str, 0, -1) : $str).$end);
            }
            //restore any open tags
            $truncated_html .= $closeTagString;
        } else $truncated_html = $str;


        return $truncated_html;
    }
}