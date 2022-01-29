<?php

namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Core\Bootstrap;

/**
 * LanguageService utility.
 */
class LanguageService
{
    /**
     * Initializes LanguageObject if necessary.
     */
    protected static function initialize(): void
    {
        Bootstrap::initializeBackendUser();
        Bootstrap::initializeLanguageObject();
    }

    /**
     * splitLabel function.
     *
     * All translations are based on $LOCAL_LANG variables.
     * 'language-splitted' labels can therefore refer to a local-lang file + index.
     * Refer to 'Inside TYPO3' for more details
     *
     * @param string|null $input Label key/reference
     * @param bool        $hsc   If set, the return value is htmlspecialchar'ed
     */
    public static function sL(?string $input, bool $hsc = false): string
    {
        if (!$input) {
            return '';
        }
        if (!$GLOBALS['LANG']) {
            static::initialize();
        }

        return $GLOBALS['LANG']->sL($input, $hsc);
    }
}
