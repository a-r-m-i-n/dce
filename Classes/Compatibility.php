<?php

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

namespace T3\Dce {
    use TYPO3\CMS\Core\Information\Typo3Version;
    use TYPO3\CMS\Core\Utility\GeneralUtility;
    use TYPO3\CMS\Core\Utility\VersionNumberUtility;

    /**
     * Contains static methods, to tackle deprecation issues.
     */
    class Compatibility
    {
        /**
         * Checks if current TYPO3 version is 11.0.0 or greater (by default).
         *
         * @param string $version
         */
        public static function isTypo3Version($version = '11.0.0'): bool
        {
            /** @var Typo3Version $typo3Version */
            $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);

            return VersionNumberUtility::convertVersionNumberToInteger($typo3Version->getBranch()) >=
                VersionNumberUtility::convertVersionNumberToInteger($version);
        }

        public static function getPageRepositoryClassName(): string
        {
            if (!self::isTypo3Version('10.0.0')) {
                return \TYPO3\CMS\Frontend\Page\PageRepository::class;
            }

            return \TYPO3\CMS\Core\Domain\Repository\PageRepository::class;
        }

        public static function isFrontendMode(): bool
        {
            if (self::isTypo3Version('10.0.0')) {
                return ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof \Psr\Http\Message\ServerRequestInterface &&
                    \TYPO3\CMS\Core\Http\ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();
            }

            return defined('TYPO3_MODE') && TYPO3_MODE === 'FE';
        }
    }
}

// phpcs:disable

/**
 * Compatibility layer for view helpers.
 */

namespace ArminVieweg\Dce\ViewHelpers
{
    class ArrayGetIndexViewHelper extends \T3\Dce\ViewHelpers\ArrayGetIndexViewHelper
    {
    }
    class ExplodeViewHelper extends \T3\Dce\ViewHelpers\ExplodeViewHelper
    {
    }
    class FalViewHelper extends \T3\Dce\ViewHelpers\FalViewHelper
    {
    }
    class FileInfoViewHelper extends \T3\Dce\ViewHelpers\FileInfoViewHelper
    {
    }
    class GPViewHelper extends \T3\Dce\ViewHelpers\GPViewHelper
    {
    }
    class IsArrayViewHelper extends \T3\Dce\ViewHelpers\IsArrayViewHelper
    {
    }
    class ThisUrlViewHelper extends \T3\Dce\ViewHelpers\ThisUrlViewHelper
    {
    }
}

namespace ArminVieweg\Dce\ViewHelpers\Be
{
    class CurrentLanguageViewHelper extends \T3\Dce\ViewHelpers\Be\CurrentLanguageViewHelper
    {
    }
    class IncludeCssFileViewHelper extends \T3\Dce\ViewHelpers\Be\IncludeCssFileViewHelper
    {
    }
    class IncludeJsFileViewHelper extends \T3\Dce\ViewHelpers\Be\IncludeJsFileViewHelper
    {
    }
    class ModuleLinkViewHelper extends \T3\Dce\ViewHelpers\Be\ModuleLinkViewHelper
    {
    }
}

namespace ArminVieweg\Dce\ViewHelpers\Be\Version
{
    class DceViewHelper extends \T3\Dce\ViewHelpers\Be\Version\DceViewHelper
    {
    }
    class Typo3ViewHelper extends \T3\Dce\ViewHelpers\Be\Version\Typo3ViewHelper
    {
    }
}

namespace ArminVieweg\Dce\ViewHelpers\Format
{
    class AddcslashesViewHelper extends \T3\Dce\ViewHelpers\Format\AddcslashesViewHelper
    {
    }
    class CdataViewHelper extends \T3\Dce\ViewHelpers\Format\CdataViewHelper
    {
    }
    class ReplaceViewHelper extends \T3\Dce\ViewHelpers\Format\ReplaceViewHelper
    {
    }
    class StripslashesViewHelper extends \T3\Dce\ViewHelpers\Format\StripslashesViewHelper
    {
    }
    class StrtolowerViewHelper extends \T3\Dce\ViewHelpers\Format\StrtolowerViewHelper
    {
    }
    class TinyViewHelper extends \T3\Dce\ViewHelpers\Format\TinyViewHelper
    {
    }
    class UcfirstViewHelper extends \T3\Dce\ViewHelpers\Format\UcfirstViewHelper
    {
    }
    class WrapWithCurlyBracesViewHelper extends \T3\Dce\ViewHelpers\Format\WrapWithCurlyBracesViewHelper
    {
    }
}
