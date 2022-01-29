<?php

namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Components\BackendView\SimpleBackendView;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PageLayoutView DrawItem Hook for DCE content elements.
 *
 * @deprecated Only used before TYPO3 v10
 */
class PageLayoutViewDrawItemHook implements PageLayoutViewDrawItemHookInterface
{
    /**
     * @var bool
     */
    protected $stylesAdded = false;

    /**
     * Disable rendering restrictions for dce content elements.
     *
     * @param bool   $drawItem
     * @param string $headerContent
     * @param string $itemContent
     * @param array  $row           #
     *
     * @throws ResourceDoesNotExistException
     */
    public function preProcess(
        PageLayoutView &$parentObject,
        &$drawItem,
        &$headerContent,
        &$itemContent,
        array &$row
    ): void {
        $dceUid = DatabaseUtility::getDceUidByContentElementRow($row);
        if (0 === $dceUid) {
            return;
        }

        try {
            /** @var Dce $dce */
            $dce = DatabaseUtility::getDceObjectForContentElement($row['uid']);
        } catch (\Exception $exception) {
            $headerContent = '<strong class="text-danger">' . $exception->getMessage() . '</strong>';

            return;
        }

        $drawItem = false;
        if ($dce->isUseSimpleBackendView()) {
            $this->addPageViewStylesheets();

            /** @var SimpleBackendView $simpleBackendView */
            $simpleBackendView = GeneralUtility::makeInstance(
                SimpleBackendView::class
            );

            $headerContent = $parentObject->linkEditContent(
                $simpleBackendView->getHeaderContent($dce),
                $row
            );
            $itemContent .= $parentObject->linkEditContent(
                $simpleBackendView->getBodytextContent($dce, $row),
                $row
            );
        } else {
            $headerContent = $parentObject->linkEditContent($dce->renderBackendTemplate('header'), $row);
            $itemContent .= $parentObject->linkEditContent($dce->renderBackendTemplate('bodytext'), $row);
        }
    }

    /**
     * Add custom dce styles for Simple Backend View to page module.
     */
    protected function addPageViewStylesheets(): void
    {
        if ($this->stylesAdded) {
            return;
        }
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addCssInlineBlock(
            'DcePageLayoutStyles',
            file_get_contents(ExtensionManagementUtility::extPath('dce') . 'Resources/Public/Css/dceInstance.css')
        );
        $this->stylesAdded = true;
    }
}
