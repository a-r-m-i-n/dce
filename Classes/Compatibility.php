<?php

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2021 Armin Vieweg <armin@v.ieweg.de>
 */
namespace T3\Dce {

    use TYPO3\CMS\Core\Utility\VersionNumberUtility;

    /**
     * Contains static methods, to tackle deprecation issues
     */
    class Compatibility
    {
        /**
         * Checks if current TYPO3 version is 11.0.0 or greater (by default)
         *
         * @param string $version e.g. 11.0.0
         * @return bool
         */
        public static function isTypo3Version($version = '11.0.0') : bool
        {
            return VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) >=
                VersionNumberUtility::convertVersionNumberToInteger($version);
        }
    }
}

// phpcs:disable

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
