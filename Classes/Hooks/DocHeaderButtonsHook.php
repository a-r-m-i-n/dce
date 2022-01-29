<?php

namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Domain\Repository\DceRepository;
use T3\Dce\Utility\BackendModuleLinkUtility;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Hook for DocHeaderButtons.
 */
class DocHeaderButtonsHook
{
    /**
     * @return array Buttons
     *
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function addDcePopupButton(
        array $params,
        ButtonBar $buttonBar
    ): array {
        $buttons = $params['buttons'];
        if (!$this->isButtonVisible()) {
            return $buttons;
        }

        $contentUid = $this->getContentUid();
        if (null !== $contentUid) {
            /** @var IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            $button = $buttonBar->makeLinkButton();
            $button->setIcon($iconFactory->getIcon('ext-dce-dce', Icon::SIZE_SMALL));
            $button->setTitle(LocalizationUtility::translate('editDceOfThisContentElement', 'dce'));
            $button->setOnClick(
                'window.open(\'' . $this->getDceEditLink($contentUid) . '\', \'editDcePopup\', ' .
                '\'height=768,width=1024,status=0,menubar=0,scrollbars=1\')'
            );
            $buttons[ButtonBar::BUTTON_POSITION_LEFT][][] = $button;
        }

        return $buttons;
    }

    /**
     * Generates link to edit DCE of given content element uid.
     *
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    protected function getDceEditLink(int $contentItemUid): string
    {
        $dceIdent = $this->getDceUid($contentItemUid);
        if (!is_numeric($dceIdent)) {
            $dceIdent = 'dce_' . $dceIdent;
        }
        $returnUrl = PathUtility::getAbsoluteWebPath(
            GeneralUtility::getFileAbsFileName('EXT:dce/Resources/Public/Html/Close.html')
        );

        return BackendModuleLinkUtility::getModuleUrl(
            'record_edit',
            GeneralUtility::explodeUrl2Array('edit[tx_dce_domain_model_dce][' . $dceIdent . ']=edit' .
                '&returnUrl=' . $returnUrl)
        );
    }

    /**
     * Checks if the popup button should be displayed. Returns false if not.
     * Otherwise returns true.
     */
    protected function isButtonVisible(): bool
    {
        $contentUid = $this->getContentUid();
        if (null !== $contentUid && $GLOBALS['BE_USER']->isAdmin()) {
            /** @var DataHandler $tceMain */
            $tceMain = GeneralUtility::makeInstance(DataHandler::class);
            $contentRecord = $tceMain->recordInfo('tt_content', $contentUid, 'CType');
            $cType = current($contentRecord);
            $dceUid = DceRepository::extractUidFromCTypeOrIdentifier($cType);

            return (bool)$dceUid;
        }

        return false;
    }

    /**
     * Returns the get parameters, related to currently edited tt_content element.
     */
    protected function getEditGetParameters(): ?array
    {
        $editGetParam = GeneralUtility::_GP('edit');

        return $editGetParam['tt_content'] ?? null;
    }

    /**
     * Returns the uid of the currently edited content element in backend.
     *
     * @return int|null content element uid
     */
    protected function getContentUid(): ?int
    {
        $editGetParameters = $this->getEditGetParameters();
        if (!\is_array($editGetParameters) || empty($editGetParameters)) {
            return null;
        }

        $contentUid = current(array_keys($editGetParameters));
        if ('edit' !== $editGetParameters[$contentUid]) {
            return null;
        }

        return (int)trim($contentUid, ',');
    }

    /**
     * Returns the uid of DCE of given content element.
     *
     * @param int $contentUid uid of content element
     */
    protected function getDceUid($contentUid): ?int
    {
        /** @var DataHandler $tceMain */
        $tceMain = GeneralUtility::makeInstance(DataHandler::class);
        $contentRecord = $tceMain->recordInfo('tt_content', $contentUid, 'CType');
        $cType = current($contentRecord);

        return DceRepository::extractUidFromCTypeOrIdentifier($cType);
    }
}
