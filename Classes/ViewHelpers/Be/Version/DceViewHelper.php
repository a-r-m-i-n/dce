<?php

namespace T3\Dce\ViewHelpers\Be\Version;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Gets the current version of DCE.
 */
class DceViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('returnInt', 'boolean', 'Returns the version number as integer if true', false, false);
    }

    /**
     * @return string
     *
     * @throws \TYPO3\CMS\Core\Package\Exception
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        if ($arguments['returnInt']) {
            return (string)VersionNumberUtility::convertVersionNumberToInteger(
                ExtensionManagementUtility::getExtensionVersion('dce')
            );
        }

        return ExtensionManagementUtility::getExtensionVersion('dce');
    }
}
