<?php
namespace ArminVieweg\Dce\ViewHelpers\Be;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Gets the current version of DCE as integer
 *
 * @package ArminVieweg\Dce
 * @deprecated Will be removed in 1.2. Use \ArminVieweg\Dce\ViewHelpers\Be\Version\DceViewHelper instead.
 */
class CurrentDceVersionViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper
{

    /**
     * Returns the current version of DCE as int
     *
     * @return int Current DCE version
     */
    public function render()
    {
        return VersionNumberUtility::convertVersionNumberToInteger(
            ExtensionManagementUtility::getExtensionVersion('dce')
        );
    }
}
