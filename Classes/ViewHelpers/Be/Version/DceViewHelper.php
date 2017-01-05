<?php
namespace ArminVieweg\Dce\ViewHelpers\Be\Version;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Gets the current version of DCE
 *
 * @package ArminVieweg\Dce
 */
class DceViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper
{
    /**
     * Returns the current version of DCE
     *
     * @param bool $returnInt Returns the version number as integer if true
     * @return string Current DCE version
     */
    public function render($returnInt = false)
    {
        if ($returnInt) {
            return \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(
                ExtensionManagementUtility::getExtensionVersion('dce')
            );
        }
        return ExtensionManagementUtility::getExtensionVersion('dce');
    }
}
