<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Hook for docHeaderButtons
 *
 * @package ArminVieweg\Dce
 */
class DocHeaderButtonsHook
{
    /**
     * Adds a new button to docheader. This affects just DCE instances.
     * The button will be not visible if the current backend user is
     * no administrator.
     *
     * @param array $params
     * @return void
     */
    public function addDcePopupButton(array &$params)
    {
        $editGetParam = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('edit');
        $editGetParam = isset($editGetParam['tt_content']) ? $editGetParam['tt_content'] : null;
        if ($editGetParam === null || !is_array($editGetParam)) {
            return;
        }
        $uidWithComma = current(array_keys($editGetParam));

        if ($editGetParam[$uidWithComma] === 'edit') {
            $uid = intval($uidWithComma);

            /** @var $tceMain \TYPO3\CMS\Core\DataHandling\DataHandler */
            $tceMain = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
            $contentRecord = $tceMain->recordInfo('tt_content', $uid, 'CType');
            $cType = current($contentRecord);
            $this->requireDceRepository();
            $dceUid = \ArminVieweg\Dce\Domain\Repository\DceRepository::extractUidFromCtype($cType);

            if ($dceUid !== false) {
                $buttonCode = $this->generateButtonHtmlCode($dceUid);
                $params['markers']['BUTTONLIST_LEFT'] .= $buttonCode . $this->getCustomStylesheet();
            }
        }
    }

    /**
     * Includes the dce repository for TYPO3 4.5
     *
     * @return void
     */
    protected function requireDceRepository()
    {
        require_once(ExtensionManagementUtility::extPath('dce') . 'Classes/Domain/Repository/DceRepository.php');
    }

    /**
     * Adds stylesheet when editing dce instance. Not nice solved, but it works.
     *
     * @return string
     */
    protected function getCustomStylesheet()
    {
        $file = ExtensionManagementUtility::extPath('dce') . 'Resources/Public/Css/dceInstance.css';
        $content = file_get_contents($file);
        return '<style type="text/css">' . $content . '</style>';
    }

    /**
     * Generate button html code
     *
     * @param int $dceUid
     * @return string
     */
    protected function generateButtonHtmlCode($dceUid)
    {
        if (!$GLOBALS['BE_USER']->isAdmin()) {
            return '';
        }

        $returnUrl = 'sysext/backend/Resources/Private/Templates/Close.html';
        if (!\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.4')) {
            $returnUrl = 'close.html';
        }
        $linkToDce = 'alt_doc.php?&returnUrl=' . $returnUrl . '&edit[tx_dce_domain_model_dce][' . $dceUid . ']=edit';

        $pathToImage = ExtensionManagementUtility::extRelPath('dce') . 'Resources/Public/Icons/docheader_icon.png';
        $titleTag = LocalizationUtility::translate('dcePopupButtonTitle', 'Dce');

        return '<div class="buttongroup"><a href="#" class="dcePopupButton" onclick="window.open(\'' . $linkToDce .
            '\', \'editDcePopup\', \'height=600,width=820,status=0,menubar=0,scrollbars=1\')"><img src="' .
            $pathToImage . '" alt="" title="' . $titleTag . '" /></a></div>';
    }
}
