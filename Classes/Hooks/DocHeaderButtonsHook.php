<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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

        $uidWithComma = current(array_keys($this->getEditGetParameters()));
        $editGetParameters = $this->getEditGetParameters();
        if (is_array($editGetParameters) && $editGetParameters[$uidWithComma] === 'edit') {
            $contentItemUid = intval($uidWithComma);

            /** @var \TYPO3\CMS\Core\Imaging\IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance('TYPO3\CMS\Core\Imaging\IconFactory');
            $button = $buttonBar->makeLinkButton();
            $button->setIcon($iconFactory->getIcon('ext-dce-dce-type-databased', Icon::SIZE_SMALL));
            $button->setTitle(LocalizationUtility::translate('editDceOfThisContentElement', 'dce'));
            $button->setOnClick(
                'window.open(\'' . $this->getDceEditLink($contentItemUid) . '\', \'editDcePopup\', ' .
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
        /** @var $tceMain \TYPO3\CMS\Core\DataHandling\DataHandler */
        $tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
        $contentRecord = $tceMain->recordInfo('tt_content', $contentItemUid, 'CType');
        $cType = current($contentRecord);
        $dceIdent = \ArminVieweg\Dce\Domain\Repository\DceRepository::extractUidFromCtype($cType);
        if (!is_numeric($dceIdent)) {
            $dceIdent = 'dce_' . $dceIdent;
        }

        $returnUrl = 'sysext/backend/Resources/Private/Templates/Close.html';
        if (!GeneralUtility::compat_version('7.4')) {
            $returnUrl = 'close.html';
        }
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
        $editGetParam = GeneralUtility::_GP('edit');
        $editGetParam = isset($editGetParam['tt_content']) ? $editGetParam['tt_content'] : null;
        return !($editGetParam === null || !is_array($editGetParam) || !$GLOBALS['BE_USER']->isAdmin());
    }

    /**
     * Returns the get parameters related to edit of tt_content item.
     *
     * @return array|null
     */
    protected function getEditGetParameters()
    {
        $editGetParam = GeneralUtility::_GP('edit');
        return isset($editGetParam['tt_content']) ? $editGetParam['tt_content'] : null;
    }

    /**
     * Adds a new button to docheader. This affects just DCE instances.
     * The button will be not visible if the current backend user is
     * no administrator.
     *
     * @param array $params
     * @return void
     * @deprecated Will get removed then 6.2 support is running out
     */
    public function addDcePopupButton62(array &$params)
    {
        if (!$this->isButtonVisible()) {
            return;
        }

        $uidWithComma = current(array_keys($this->getEditGetParameters()));
        if ($this->getEditGetParameters()[$uidWithComma] === 'edit') {
            $uid = intval($uidWithComma);

            /** @var $tceMain \TYPO3\CMS\Core\DataHandling\DataHandler */
            $tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
            $contentRecord = $tceMain->recordInfo('tt_content', $uid, 'CType');
            $cType = current($contentRecord);
            $dceUid = \ArminVieweg\Dce\Domain\Repository\DceRepository::extractUidFromCtype($cType);

            if ($dceUid !== false) {
                $buttonCode = $this->generateButtonHtmlCode($dceUid);
                $params['markers']['BUTTONLIST_LEFT'] .= $buttonCode . $this->getCustomStylesheet();
            }
        }
    }

    /**
     * Adds stylesheet when editing dce instance. Not nice solved, but it works.
     *
     * @return string
     * @deprecated Will get removed then 6.2 support is running out
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
     * @deprecated Will get removed then 6.2 support is running out
     */
    protected function generateButtonHtmlCode($dceUid)
    {
        if (!$GLOBALS['BE_USER']->isAdmin()) {
            return '';
        }
        $returnUrl = 'sysext/backend/Resources/Private/Templates/Close.html';
        if (!GeneralUtility::compat_version('7.4')) {
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
