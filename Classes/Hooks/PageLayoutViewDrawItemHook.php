<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PageLayoutView DrawItem Hook for DCE content elements
 *
 * @package ArminVieweg\Dce
 */
class PageLayoutViewDrawItemHook implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface
{
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
        $dceUid = $this->getDceUidByContentElementUid($row['uid']);
        if ($dceUid === 0) {
            return;
        }

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

        if ($dce->isUseSimpleBackendView()) {
            /** @var \ArminVieweg\Dce\Utility\SimpleBackendView $simpleBackendViewUtility */
            $simpleBackendViewUtility = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\SimpleBackendView');
            $drawItem = false;
            $headerContent = $parentObject->linkEditContent(
                '<strong>' . $simpleBackendViewUtility->getSimpleBackendViewHeaderContent($dce) . '</strong>',
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
     * Gets dce uid by content element uid
     *
     * @param int $uid content element uid (tt_content)
     * @return int Returns zero if it is no dce. Otherwise uid of dce
     */
    protected function getDceUidByContentElementUid($uid)
    {
        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
        $cType = current($dataHandler->recordInfo('tt_content', $uid, 'CType'));
        return intval(substr($cType, strlen('dce_dceuid')));
    }
}
