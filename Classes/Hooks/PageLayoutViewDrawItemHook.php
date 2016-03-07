<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
                array(
                    'contentElementUid' => $row['uid'],
                    'dceUid' => $dceUid
                ),
                true
            );
        } catch (\Exception $exception) {
            $headerContent = '<strong class="text-danger">' . $exception->getMessage() .'</strong>';
            return;
        }

        if ($dce->isUseSimpleBackendView()) {
            $this->addPageViewStylesheets();

            /** @var \ArminVieweg\Dce\Components\SimpleBackendView\SimpleBackendView $simpleBackendViewUtility */
            $simpleBackendViewUtility = GeneralUtility::makeInstance(
                'ArminVieweg\Dce\Components\SimpleBackendView\SimpleBackendView'
            );
            $drawItem = false;
            $headerContent = $parentObject->linkEditContent(
                $simpleBackendViewUtility->getSimpleBackendViewHeaderContent($dce),
                $row
            );
            $itemContent .= $parentObject->linkEditContent(
                $simpleBackendViewUtility->getSimpleBackendViewBodytextContent($dce, $row),
                $row
            );
            return;
        }

        if (strpos($row['CType'], 'dce_dceuid') !== false) {
            $drawItem = false;
            $itemContent .= $parentObject->linkEditContent($row['bodytext'], $row) . '<br />';
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
