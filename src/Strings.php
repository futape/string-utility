<?php


namespace Futape\Utility\String;


use Futape\Utility\ArrayUtility\Arrays;

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
     * @param string|string[] $start
     * @param bool $ignoreCase
     * @return bool
     */
    public static function startsWith(string $value, $start, bool $ignoreCase = false): bool
    {
        if ($ignoreCase) {
            $value = mb_strtolower($value);
        }

        $start = is_array($start) ? $start : [$start];

        foreach ($start as $val) {
            if ($ignoreCase) {
                $val = mb_strtolower($val);
            }

            if (mb_substr($value, 0, mb_strlen($val)) == $val) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a string ends with a specific string
     *
     * @param string $value
     * @param string|string[] $end
     * @param bool $ignoreCase
     * @return bool
     */
    public static function endsWith(string $value, $end, bool $ignoreCase = false): bool
    {
        if ($ignoreCase) {
            $value = mb_strtolower($value);
        }

        $end = is_array($end) ? $end : [$end];

        foreach ($end as $val) {
            if ($ignoreCase) {
                $val = mb_strtolower($val);
            }

            if (mb_substr($value, -mb_strlen($val)) == $val) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $value
     * @return string
     */
    public static function inline(string $value): string
    {
        return preg_replace('/\s/', ' ', $value);
    }

    /**
     * Does an inverse substr()
     *
     * Instead of returning a substring defined by a start position and a length,
     * this function returns the inverse part of the string, with the substring removed.
     *
     * @param string $value
     * @param int $start
     * @param int|null $length
     * @param bool $multibyte
     * @return string
     */
    public static function supstr(string $value, int $start, ?int $length = null, bool $multibyte = true): string
    {
        if ($multibyte) {
            $substr = mb_substr($value, $start, $length);
            $beginning = mb_substr($value, 0, $start);
            $end = mb_substr($value, mb_strlen($beginning) + mb_strlen($substr));
        } else {
            if ($length !== null) {
                $substr = substr($value, $start, $length);
            } else {
                $substr = substr($value, $start);
            }
            $substr = $substr === false ? '' : $substr;

            $beginning = substr($value, 0, $start);
            $beginning = $beginning === false ? '' : $beginning;

            $end = substr($value, strlen($beginning) + strlen($substr));
            $end = $end === false ? '' : $end;
        }

        return $beginning . $end;
    }

    /**
     * @param array $values
     * @param int $threshold
     * @return string
     */
    public static function createSeries(array $values, int $threshold = 3): string
    {
        array_walk(
            $values,
            function (&$val) {
                if (!is_int($val)) {
                    $val = mb_substr((string)$val, 0, 1);
                }
            }
        );
        $values = array_filter(
            Arrays::unique($values, Arrays::UNIQUE_STRICT),
            function ($val) {
                return $val !== '';
            }
        );
        usort(
            $values,
            function ($a, $b) {
                if (is_int($a) && is_int($b)) {
                    return $a - $b;
                }
                if (is_int($a)) {
                    return -1;
                }
                if (is_int($b)) {
                    return 1;
                }

                return mb_ord($a) - mb_ord($b);
            }
        );

        $threshold = max($threshold, 2);
        $series = [];

        for ($i = 0; $i < count($values); $i++) {
            $originalValue = $values[$i];
            $value = is_int($originalValue) ? $originalValue : mb_ord($originalValue);
            $seriesEnd = $value;

            for ($y = $i + 1; $y < count($values); $y++) {
                if (is_int($originalValue) != is_int($values[$y])) {
                    break;
                }

                $consecutiveValue = is_int($values[$y]) ? $values[$y] : mb_ord($values[$y]);

                if ($consecutiveValue == $seriesEnd + 1) {
                    $seriesEnd = $consecutiveValue;
                } else {
                    break;
                }
            }

            if ($seriesEnd >= $value + $threshold - 1) {
                if (is_int($originalValue)) {
                    $series[] = $originalValue . ' - ' . $seriesEnd;
                } else {
                    $series[] = $originalValue . ' - ' . mb_chr($seriesEnd);
                }
                $i += $seriesEnd - $value;
            } else {
                $series[] = $originalValue;
            }
        }

        var_dump(implode(', ', $series));

        return implode(', ', $series);
    }
}
