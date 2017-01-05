<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * LanguageService utility
 *
 * @package ArminVieweg\Dce
 */
class LanguageService
{
    /**
     * Initializes LanguageObject if necessary
     *
     * @return void
     */
    protected static function initialize()
    {
        \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->initializeBackendUser();
        \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->initializeLanguageObject();
    }
    /**
     * Returns a valid LanguageService object that is connected and ready
     * to be used static
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    public static function get()
    {
        if (!$GLOBALS['LANG']) {
            static::initialize();
        }
        return $GLOBALS['LANG'];
    }

    /**
     * splitLabel function
     *
     * All translations are based on $LOCAL_LANG variables.
     * 'language-splitted' labels can therefore refer to a local-lang file + index.
     * Refer to 'Inside TYPO3' for more details
     *
     * @param string $input Label key/reference
     * @param bool $hsc If set, the return value is htmlspecialchar'ed
     * @return string
     */
    public static function sL($input, $hsc = false)
    {
        if (!$GLOBALS['LANG']) {
            static::initialize();
        }
        return $GLOBALS['LANG']->sL($input, $hsc);
    }
}
