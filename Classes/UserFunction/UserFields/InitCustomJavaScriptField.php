<?php
namespace ArminVieweg\Dce\UserFunction\UserFields;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InitCustomJavaScriptField
 *
 * @package ArminVieweg\Dce
 */
class InitCustomJavaScriptField
{
    /**
     * @param array $parameter
     * @return string
     */
    public function init(array $parameter)
    {
        /** @var \TYPO3\CMS\Backend\Template\DocumentTemplate $mediumDocumentTemplate */
        $mediumDocumentTemplate = GeneralUtility::makeInstance('TYPO3\CMS\Backend\Template\DocumentTemplate');

        $extPath = ExtensionManagementUtility::extRelPath('dce');
        $mediumDocumentTemplate->getPageRenderer()->addJsFile(
            $extPath . 'Resources/Public/JavaScript/InitializeCodemirror.js'
        );
        if (GeneralUtility::compat_version('7.6')) {
            $mediumDocumentTemplate->getPageRenderer()->addJsFile(
                $extPath . 'Resources/Public/JavaScript/EnhanceIrre.js'
            );
        }
        return '';
    }
}
