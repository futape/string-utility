<?php


namespace Futape\Utility\String;


abstract class Strings
{
    /**
     * Escapes specific characters in a string
     *
     * This function acts like addslashes() but provides the ability to pass the characters to escape.
     * NULL bytes are always escaped.
     *
     * If the backslash itself shouldn't be escaped, this function ensures that by prepending a backslash to one
     * of the specified characters, that backslash itself is not being escaped by a preceding blackslash by
     * removing that preceding backslash in that case.
     *
     * @see https://www.php.net/manual/en/function.addslashes.php
     *
     * @param string $value
     * @param string $chars
     * @return string
     */
    public static function escape(string $value, string $chars = '\\"$\''): string
    {
        $charsPattern = '[' . preg_quote($chars, '/') . '\0]';

        if(mb_strpos($chars, '\\') === false) {
            $value = preg_replace('/((?:^|[^\\\\])(?:\\\\{2})*)\\\\($|'. $charsPattern .')/', '$1$2', $value);
        }

        return preg_replace_callback('/' . $charsPattern . '/', function ($matches) {
            return '\\' . $matches[0];
        }, $value);
    }

    /**
     * Strips off a string from the beginning of another string
     *
     * @param string $value
     * @param string $strip
     * @param bool $ignoreCase
     * @return string
     */
    public static function stripLeft(string $value, string $strip, bool $ignoreCase = false): string
    {
        if (self::startsWith($value, $strip, $ignoreCase)) {
            return mb_substr($value, mb_strlen($strip));
        }

        return $value;
    }

    /**
     * Strips off a string from the end of another string
     *
     * @param string $value
     * @param string $strip
     * @param bool $ignoreCase
     * @return string
     */
    public static function stripRight(string $value, string $strip, bool $ignoreCase = false): string
    {
        if (self::endsWith($value, $strip, $ignoreCase)) {
            return mb_substr($value, 0, -mb_strlen($strip));
        }

        return $value;
    }

    /**
     * Resolves meta characters in a string
     *
     * Treats a literal string as if it would be a double-quoted one, except for resolving variables.
     *
     * @param string $value
     * @return string
     */
    public static function resolve(string $value): string
    {
        return eval('return "' . self::escape($value, '$"') . '";');
    }

    /**
     * Normalizes a string's linebreaks
     *
     * Changes CR and CRLF to Unix-style LF linebreaks.
     *
     * @param string $value
     * @return string
     */
    public static function normalizeNewlines(string $value): string
    {
        return preg_replace('/\r\n?/', "\n", $value);
    }

    /**
     * Checks if a string starts with a specific string
     *
     * @param string $value
     * @param string $start
     * @param bool $ignoreCase
     * @return bool
     */
    public static function startsWith(string $value, string $start, bool $ignoreCase = false): bool
    {
        if ($ignoreCase) {
            $value = mb_strtolower($value);
            $start = mb_strtolower($start);
        }

        return mb_substr($value, 0, mb_strlen($start)) == $start;
    }

    /**
     * Checks if a string ends with a specific string
     *
     * @param string $value
     * @param string $end
     * @param bool $ignoreCase
     * @return bool
     */
    public static function endsWith(string $value, string $end, bool $ignoreCase = false): bool
    {
        if ($ignoreCase) {
            $value = mb_strtolower($value);
            $end = mb_strtolower($end);
        }

        return mb_substr($value, -mb_strlen($end)) == $end;
    }

    /**
     * @param string $value
     * @return string
     */
    public static function inline(string $value): string
    {
        return preg_replace('/\s/', ' ', $value);
    }
}
