<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\DatabaseUtility;

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
        $dceUid = \ArminVieweg\Dce\Domain\Repository\DceRepository::extractUidFromCtype($row['CType']);
        if (!$dceUid) {
            return;
        }

        $dceFieldsWithMappingsAmount = DatabaseUtility::getDatabaseConnection()->exec_SELECTcountRows(
            'uid',
            'tx_dce_domain_model_dcefield',
            'parent_dce=' . $dceUid . ' AND map_to="tx_dce_index" AND deleted=0 AND hidden=0'
        );
        if (!$dceFieldsWithMappingsAmount) {
            return;
        }

        $fullRow = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
            '*',
            'tt_content',
            'uid=' . $row['uid']
        );
        if ($fullRow['tx_dce_index']) {
            $bodytext = $fullRow['tx_dce_index'];
        }
    }
}
