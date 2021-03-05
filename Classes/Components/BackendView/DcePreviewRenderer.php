<?php

declare(strict_types = 1);

namespace T3\Dce\Components\BackendView;

use T3\Dce\Domain\Model\Dce;
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DcePreviewRenderer extends StandardContentPreviewRenderer
{
    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        $row = $item->getRecord();

        $dceUid = DatabaseUtility::getDceUidByContentElementRow($row);
        if (0 === $dceUid) {
            return parent::renderPageModulePreviewHeader($item);
        }

        try {
            /** @var Dce $dce */
            $dce = DatabaseUtility::getDceObjectForContentElement($row['uid']);
        } catch (\Exception $exception) {
            $headerContent = '<strong class="text-danger">' . $exception->getMessage() . '</strong>';

            return $headerContent;
        }

        if ($dce->isUseSimpleBackendView()) {
            /** @var SimpleBackendView $simpleBackendView */
            $simpleBackendView = GeneralUtility::makeInstance(
                SimpleBackendView::class
            );

            $headerContent = $this->linkEditContent(
                $simpleBackendView->getHeaderContent($dce),
                $row
            );
        } else {
            $headerContent = $this->linkEditContent($dce->renderBackendTemplate('header'), $row);
        }

        return $headerContent;
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $row = $item->getRecord();

        $dceUid = DatabaseUtility::getDceUidByContentElementRow($row);
        if (0 === $dceUid) {
            return '';
        }

        try {
            /** @var Dce $dce */
            $dce = DatabaseUtility::getDceObjectForContentElement($row['uid']);
        } catch (\Exception $exception) {
            return '';
        }

        if ($dce->isUseSimpleBackendView()) {
            $this->addPageViewStylesheets();

            /** @var SimpleBackendView $simpleBackendView */
            $simpleBackendView = GeneralUtility::makeInstance(
                SimpleBackendView::class
            );

            $headerContent = $this->linkEditContent(
                $simpleBackendView->getBodytextContent($dce, $row),
                $row
            );
        } else {
            $headerContent = $this->linkEditContent($dce->renderBackendTemplate('bodytext'), $row);
        }

        return $headerContent;
    }

    /**
     * Add custom dce styles for Simple Backend View to page module.
     */
    protected function addPageViewStylesheets(): void
    {
        /** @var AssetCollector $assetCollector */
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        $assetCollector->addStyleSheet(
            'dce_content_preview_renderer',
            'EXT:dce/Resources/Public/Css/dceInstance.css'
        );
    }
}
