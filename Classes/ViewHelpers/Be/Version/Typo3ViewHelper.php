<?php

namespace T3\Dce\ViewHelpers\Be\Version;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Gets the current TYPO3 version.
 */
class Typo3ViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('returnInt', 'boolean', 'Returns the version number as integer if true', false, false);
    }

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        /** @var Typo3Version $typo3Version */
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($arguments['returnInt']) {
            return (string)VersionNumberUtility::convertVersionNumberToInteger($typo3Version->getVersion());
        }

        return $typo3Version->getVersion();
    }
}
