<?php

namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * LanguageService utility.
 */
class LanguageService
{
    /**
     * splitLabel function.
     *
     * All translations are based on $LOCAL_LANG variables.
     * 'language-splitted' labels can therefore refer to a local-lang file + index.
     * Refer to 'Inside TYPO3' for more details
     *
     * @param string|null $input Label key/reference
     */
    public static function sL(?string $input): string
    {
        if (!$input) {
            return '';
        }
        if (!$GLOBALS['LANG']) {
            $languageServiceFactory = GeneralUtility::makeInstance(LanguageServiceFactory::class);
            $GLOBALS['LANG'] = $languageServiceFactory->createFromUserPreferences($GLOBALS['BE_USER'] ?? null);
        }

        return $GLOBALS['LANG']->sL($input);
    }
}
