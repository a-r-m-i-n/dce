<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * ke_search Hook
 *
 * @package ArminVieweg\Dce
 */
class KeSearchHook
{

    /**
     * Renders DCE frontend output and returns it as bodytext value
     *
     * @param string $bodytext Referenced content, which may get modified by this hook
     * @param array $row tt_content row
     * @param \tx_kesearch_indexer_types $indexerTypes
     * @return void
     */
    public function modifyContentFromContentElement(&$bodytext, array $row, $indexerTypes)
    {
        if (!isset($GLOBALS['TSFE'])) {
            //$GLOBALS['TSFE'] = new \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController(array(), 0, 0);
        }


        $dceUid = \ArminVieweg\Dce\Domain\Repository\DceRepository::extractUidFromCtype($row['CType']);
        if (!$dceUid) {
            return;
        }

        $bodytext = 'testav';
        return;

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
            \ArminVieweg\Dce\Utility\FlashMessage::add(
                $exception->getMessage(),
                'Error',
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            return;
        }
        $bodytext = $dce->render();
    }
}
