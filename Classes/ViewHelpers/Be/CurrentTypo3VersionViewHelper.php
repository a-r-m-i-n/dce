<?php
namespace ArminVieweg\Dce\ViewHelpers\Be;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Get the current version of TYPO3 as integer
 *
 * @package ArminVieweg\Dce
 * @deprecated Will be removed in 1.2. Use \ArminVieweg\Dce\ViewHelpers\Be\Version\Typo3ViewHelper instead.
 */
class CurrentTypo3VersionViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper
{

    /**
     * Returns the current version of TYPO3 as integer
     *
     * @return int Current TYPO3 version
     */
    public function render()
    {
        return \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
    }
}
