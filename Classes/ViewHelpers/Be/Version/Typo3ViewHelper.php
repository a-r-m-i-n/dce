<?php
namespace ArminVieweg\Dce\ViewHelpers\Be\Version;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Gets the current TYPO3 version
 *
 * @package ArminVieweg\Dce
 */
class Typo3ViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper
{

    /**
     * Returns the current TYPO3 version
     *
     * @param bool $returnInt Returns the version number as integer if true
     * @return string Current TYPO3 version
     */
    public function render($returnInt = false)
    {
        if ($returnInt) {
            return \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
        }
        return TYPO3_version;
    }
}
