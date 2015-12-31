<?php
namespace ArminVieweg\Dce\ViewHelpers\Be;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Returns the path of content element wizard icons
 *
 * @package ArminVieweg\Dce
 * @deprecated Will be removed with 6.2 support
 */
class ContentElementWizardIconPathViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper
{
    /**
     * Returns path of ce wizard icon. If name is given it appends the filename.
     *
     * @param string $name Optional, If set it appends the filename to path
     * @return string ce wizard icon path
     */
    public function render($name = '')
    {
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.6')) {
            return $name;
        }
        if (!empty($name)) {
            $name = $name . '.gif';
        }
        if (!\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.4')) {
            return 'gfx/c_wiz/' . $name;
        }
        return 'EXT:frontend/Resources/Public/Icons/ContentElementWizard/' . $name;
    }
}
