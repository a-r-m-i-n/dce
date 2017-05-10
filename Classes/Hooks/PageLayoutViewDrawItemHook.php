<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PageLayoutView DrawItem Hook for DCE content elements
 *
 * @package ArminVieweg\Dce
 */
class PageLayoutViewDrawItemHook implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface
{
    /**
     * @var bool
     */
    protected $stylesAdded = false;

    /**
     * Disable rendering restrictions for dce content elements
     *
     * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject
     * @param $drawItem
     * @param $headerContent
     * @param $itemContent
     * @param array $row #
     * @return void
     */
    public function preProcess(
        \TYPO3\CMS\Backend\View\PageLayoutView &$parentObject,
        &$drawItem,
        &$headerContent,
        &$itemContent,
        array &$row
    ) {
        $dceUid = DatabaseUtility::getDceUidByContentElementUid($row['uid']);
        if ($dceUid === 0) {
            return;
        }

        try {
            /** @var \ArminVieweg\Dce\Domain\Model\Dce $dce */
            $dce = \ArminVieweg\Dce\Utility\Extbase::bootstrapControllerAction(
                'ArminVieweg',
                'Dce',
                'Dce',
                'renderDce',
                'Dce',
                [
                    'contentElementUid' => $row['uid'],
                    'dceUid' => $dceUid
                ],
                true
            );
        } catch (\Exception $exception) {
            $headerContent = '<strong class="text-danger">' . $exception->getMessage() .'</strong>';
            return;
        }

        $drawItem = false;
        if ($dce->isUseSimpleBackendView()) {
            $this->addPageViewStylesheets();

            /** @var \ArminVieweg\Dce\Components\BackendView\SimpleBackendView $simpleBackendView */
            $simpleBackendView = GeneralUtility::makeInstance(
                'ArminVieweg\Dce\Components\BackendView\SimpleBackendView'
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
     * Add custom dce styles for Simple Backend View to page module
     *
     * @return void
     */
    protected function addPageViewStylesheets()
    {
        if ($this->stylesAdded) {
            return;
        }
        /** @var \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance('TYPO3\CMS\Core\Page\PageRenderer');
        $pageRenderer->addCssInlineBlock(
            'DcePageLayoutStyles',
            file_get_contents(ExtensionManagementUtility::extPath('dce') . 'Resources/Public/Css/dceInstance.css')
        );
        $this->stylesAdded = true;
    }
}
