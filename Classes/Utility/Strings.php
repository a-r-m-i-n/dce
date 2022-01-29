<?php

declare(strict_types = 1);

namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

/**
 * Helper class for strings.
 */
class Strings
{
    /**
     * Truncates a string and pre-/appends a string.
     * Unit tested by Kasper.
     *
     * @param string $charset The character set
     * @param string $string  Character string
     * @param int    $len     Length (in characters)
     * @param string $crop    Crop signifier
     *
     * @return string The shortened string
     *
     * @see substr(), mb_strimwidth()
     */
    public static function crop(string $charset, string $string, int $len, string $crop = ''): string
    {
        if (0 === $len || mb_strlen($string, $charset) <= abs($len)) {
            return $string;
        }
        if ($len > 0) {
            $string = mb_substr($string, 0, $len, $charset) . $crop;
        } else {
            $string = $crop . mb_substr($string, $len, mb_strlen($string, $charset), $charset);
        }

        return $string;
    }
}
