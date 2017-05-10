<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Hook for DocHeaderButtons
 *
 * @package ArminVieweg\Dce
 */
class DocHeaderButtonsHook
{
    /**
     * @param array $params
     * @param \TYPO3\CMS\Backend\Template\Components\ButtonBar $buttonBar
     * @return array Buttons
     */
    public function addDcePopupButton(array $params, \TYPO3\CMS\Backend\Template\Components\ButtonBar $buttonBar)
    {
        $buttons = $params['buttons'];
        if (!$this->isButtonVisible()) {
            return $buttons;
        }

        $contentUid = $this->getContentUid();
        if ($contentUid !== null) {
            /** @var \TYPO3\CMS\Core\Imaging\IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance('TYPO3\CMS\Core\Imaging\IconFactory');
            $button = $buttonBar->makeLinkButton();
            $button->setIcon($iconFactory->getIcon('ext-dce-dce', Icon::SIZE_SMALL));
            $button->setTitle(LocalizationUtility::translate('editDceOfThisContentElement', 'dce'));
            $button->setOnClick(
                'window.open(\'' . $this->getDceEditLink($contentUid) . '\', \'editDcePopup\', ' .
                '\'height=768,width=1024,status=0,menubar=0,scrollbars=1\')'
            );
            $buttons[\TYPO3\CMS\Backend\Template\Components\ButtonBar::BUTTON_POSITION_LEFT][][] = $button;
        }
        return $buttons;
    }

    /**
     * Generates link to edit DCE of given content element uid
     *
     * @param int $contentItemUid
     * @return string
     */
    protected function getDceEditLink($contentItemUid)
    {
        $dceIdent = $this->getDceUid($contentItemUid);
        if (!is_numeric($dceIdent)) {
            $dceIdent = 'dce_' . $dceIdent;
        }

        $returnUrl = 'sysext/backend/Resources/Private/Templates/Close.html';
        return \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl(
            'record_edit',
            GeneralUtility::explodeUrl2Array('edit[tx_dce_domain_model_dce][' . $dceIdent . ']=edit' .
                '&returnUrl=' . $returnUrl)
        );
    }

    /**
     * Checks if the popup button should be displayed. Returns false if not.
     * Otherwise returns true.
     *
     * @return bool
     */
    protected function isButtonVisible()
    {
        $contentUid = $this->getContentUid();
        if ($contentUid !== null && $GLOBALS['BE_USER']->isAdmin()) {
            /** @var $tceMain \TYPO3\CMS\Core\DataHandling\DataHandler */
            $tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
            $contentRecord = $tceMain->recordInfo('tt_content', $contentUid, 'CType');
            $cType = current($contentRecord);
            $dceUid = \ArminVieweg\Dce\Domain\Repository\DceRepository::extractUidFromCtype($cType);
            return $dceUid !== false;
        }
        return false;
    }

    /**
     * Returns the get parameters, related to currently edited tt_content element
     *
     * @return null|array
     */
    protected function getEditGetParameters()
    {
        $editGetParam = GeneralUtility::_GP('edit');
        return isset($editGetParam['tt_content']) ? $editGetParam['tt_content'] : null;
    }

    /**
     * Returns the uid of the currently edited content element in backend
     *
     * @return int|null content element uid
     */
    protected function getContentUid()
    {
        $editGetParameters = $this->getEditGetParameters();
        if (!is_array($editGetParameters) || empty($editGetParameters)) {
            return null;
        }

        $contentUid = current(array_keys($editGetParameters));
        if ($editGetParameters[$contentUid] !== 'edit') {
            return null;
        }
        return (int) trim($contentUid, ',');
    }

    /**
     * Returns the uid of DCE of given content element
     *
     * @param int $contentUid uid of content element
     * @return bool|int|string
     */
    protected function getDceUid($contentUid)
    {
        /** @var $tceMain \TYPO3\CMS\Core\DataHandling\DataHandler */
        $tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
        $contentRecord = $tceMain->recordInfo('tt_content', $contentUid, 'CType');
        $cType = current($contentRecord);
        return \ArminVieweg\Dce\Domain\Repository\DceRepository::extractUidFromCtype($cType);
    }
}
