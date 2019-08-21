<?php

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
namespace T3\Dce {

    use TYPO3\CMS\Backend\Routing\UriBuilder;
    use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
    use TYPO3\CMS\Core\Context\Context;
    use TYPO3\CMS\Core\Core\Environment;
    use TYPO3\CMS\Core\Utility\GeneralUtility;
    use TYPO3\CMS\Core\Utility\VersionNumberUtility;

    /**
     * Contains static methods, to tackle deprecation warnings in 9.5, but keep compatibility with 8.7
     */
    class Compatibility
    {
        /**
         * Checks if current TYPO3 version is 9.0.0 or greater (by default)
         *
         * @param string $version e.g. 9.0.0
         * @return bool
         */
        public static function isTypo3Version($version = '9.0.0') : bool
        {
            return VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) >=
                VersionNumberUtility::convertVersionNumberToInteger($version);
        }

        /**
         * Returns the current sys_language_uid (in frontend)
         *
         * @return int|null
         */
        public static function getSysLanguageUid() : ?int
        {
            if (!static::isTypo3Version()) {
                if (!isset($GLOBALS['TSFE'])) {
                    return null;
                }
                return (int) $GLOBALS['TSFE']->sys_language_uid;
            }
            // TYPO3 9 way to fetch sys_language_uid
            $context = GeneralUtility::makeInstance(Context::class);
            try {
                return (int) $context->getPropertyFromAspect('language', 'id', 0);
            } catch (AspectNotFoundException $e) {
            }
            return null;
        }

        /**
         * Returns the URL to a given module
         *
         * @param string $moduleName Name of the module
         * @param array $urlParameters URL parameters that should be added as key value pairs
         * @return string Calculated URL
         * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
         */
        public static function getModuleUrl($moduleName, $urlParameters = []) : string
        {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            try {
                $uri = $uriBuilder->buildUriFromRoute($moduleName, $urlParameters);
            } catch (\TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException $e) {
                $uri = static::isTypo3Version()
                    ? $uriBuilder->buildUriFromRoutePath($moduleName, $urlParameters)
                    : $uriBuilder->buildUriFromModule($moduleName, $urlParameters);
            }
            return (string) $uri;
        }

        /**
         * Returns the path to /var directory. Before TYPO3 9 it uses a hardcoded string.
         * This method becomes unnecessary, when 8.7 support is dropped.
         *
         * @return string
         */
        public static function getVarPath(): string
        {
            if (!self::isTypo3Version()) {
                return PATH_site . 'typo3temp/var';
            }
            return Environment::getVarPath();
        }
    }
}

// phpcs:disable

/**
 * Compatibility Layer for old user condition
 */
namespace ArminVieweg\Dce\UserConditions
{
    use T3\Dce\Components\UserConditions\DceOnCurrentPage;
    use TYPO3\CMS\Core\Utility\GeneralUtility;

    /**
     * @param string|int $dceUidOrIdentifier
     * @return bool
     */
    function user_dceOnCurrentPage($dceUidOrIdentifier) : bool
    {
        $condition = GeneralUtility::makeInstance(DceOnCurrentPage::class);
        return $condition->matchCondition([$dceUidOrIdentifier]);
    }
}

/**
 * Compatibility layer for view helpers
 */
namespace ArminVieweg\Dce\ViewHelpers
{
    class ArrayGetIndexViewHelper extends \T3\Dce\ViewHelpers\ArrayGetIndexViewHelper {}
    class ExplodeViewHelper extends \T3\Dce\ViewHelpers\ExplodeViewHelper {}
    class FalViewHelper extends \T3\Dce\ViewHelpers\FalViewHelper {}
    class FileInfoViewHelper extends \T3\Dce\ViewHelpers\FileInfoViewHelper {}
    class GPViewHelper extends \T3\Dce\ViewHelpers\GPViewHelper {}
    class IsArrayViewHelper extends \T3\Dce\ViewHelpers\IsArrayViewHelper {}
    class ThisUrlViewHelper extends \T3\Dce\ViewHelpers\ThisUrlViewHelper {}
}
namespace ArminVieweg\Dce\ViewHelpers\Be
{
    class CurrentLanguageViewHelper extends \T3\Dce\ViewHelpers\Be\CurrentLanguageViewHelper {}
    class IncludeCssFileViewHelper extends \T3\Dce\ViewHelpers\Be\IncludeCssFileViewHelper {}
    class IncludeJsFileViewHelper extends \T3\Dce\ViewHelpers\Be\IncludeJsFileViewHelper {}
    class ModuleLinkViewHelper extends \T3\Dce\ViewHelpers\Be\ModuleLinkViewHelper {}
    class TableListViewHelper extends \T3\Dce\ViewHelpers\Be\TableListViewHelper {}
}
namespace ArminVieweg\Dce\ViewHelpers\Be\Version
{
    class DceViewHelper extends \T3\Dce\ViewHelpers\Be\Version\DceViewHelper {}
    class Typo3ViewHelper extends \T3\Dce\ViewHelpers\Be\Version\Typo3ViewHelper {}
}
namespace ArminVieweg\Dce\ViewHelpers\Format
{
    class AddcslashesViewHelper extends \T3\Dce\ViewHelpers\Format\AddcslashesViewHelper {}
    class CdataViewHelper extends \T3\Dce\ViewHelpers\Format\CdataViewHelper {}
    class ReplaceViewHelper extends \T3\Dce\ViewHelpers\Format\ReplaceViewHelper {}
    class StripslashesViewHelper extends \T3\Dce\ViewHelpers\Format\StripslashesViewHelper {}
    class StrtolowerViewHelper extends \T3\Dce\ViewHelpers\Format\StrtolowerViewHelper {}
    class TinyViewHelper extends \T3\Dce\ViewHelpers\Format\TinyViewHelper {}
    class UcfirstViewHelper extends \T3\Dce\ViewHelpers\Format\UcfirstViewHelper {}
    class WrapWithCurlyBracesViewHelper extends \T3\Dce\ViewHelpers\Format\WrapWithCurlyBracesViewHelper {}
}
