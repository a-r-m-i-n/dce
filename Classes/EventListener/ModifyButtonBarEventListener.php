<?php

declare(strict_types = 1);

namespace T3\Dce\EventListener;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Domain\Repository\DceRepository;
use T3\Dce\Utility\BackendModuleLinkUtility;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ComponentFactory;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ModifyButtonBarEventListener
{
    public function __construct(private readonly ComponentFactory $componentFactory)
    {
    }

    public function __invoke(ModifyButtonBarEvent $event): void
    {
        $contentUid = $this->getContentUid($event->getRequest()->getQueryParams());
        if ($contentUid && $this->userIsAdmin() && $this->getDceUid($contentUid)) {
            /** @var IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

            $button = $this->componentFactory->createLinkButton();
            $button->setIcon($iconFactory->getIcon('dce-ext', IconSize::SMALL));
            $button->setTitle(LocalizationUtility::translate('editDceOfThisContentElement', 'dce'));
            $button->setShowLabelText(false);
            $button->setHref('#');
            $button->setDataAttributes(['dce-edit-url' => $this->getDceEditLink($contentUid)]);

            $buttons = $event->getButtons();
            $buttons[ButtonBar::BUTTON_POSITION_LEFT][] = [$button];
            $event->setButtons($buttons);

            /** @var PageRenderer $pageRenderer */
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->loadJavaScriptModule('@t3/dce/dce-edit-button');
            $pageRenderer->loadJavaScriptModule('@t3/dce/flexform-section-collapse');
        }
    }

    private function getDceEditLink(int $contentItemUid): string
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
            GeneralUtility::explodeUrl2Array('edit[tx_dce_domain_model_dce][' . $dceIdent . ']=edit&returnUrl=' . $returnUrl)
        );
    }

    /**
     * Returns the uid of the currently edited content element in backend.
     */
    private function getContentUid(array $queryParameters): ?int
    {
        $editGetParameters = $queryParameters['edit']['tt_content'] ?? null;
        if (!is_array($editGetParameters) || empty($editGetParameters)) {
            return null;
        }

        $contentUid = current(array_keys($editGetParameters));
        if ('edit' !== $editGetParameters[$contentUid]) {
            return null;
        }

        if (is_string($contentUid)) {
            return (int)trim($contentUid, ',');

        }

        return $contentUid;
    }

    /**
     * Returns the uid of DCE of given content element.
     */
    private function getDceUid(int $contentUid): ?int
    {
        $contentRecord = BackendUtility::getRecord('tt_content', $contentUid, 'CType');
        if (!is_array($contentRecord) || !isset($contentRecord['CType'])) {
            return null;
        }

        return DceRepository::extractUidFromCTypeOrIdentifier((string)$contentRecord['CType']);
    }

    /**
     * Checks if the current logged-in user is admin.
     */
    private function userIsAdmin(): bool
    {
        /** @var BackendUserAuthentication $backendUser */
        $backendUser = $GLOBALS['BE_USER'];

        return $backendUser->isAdmin();
    }
}
