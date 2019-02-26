<?php
namespace T3\Dce\Hooks;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2016-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Utility\DatabaseUtility;

/**
 * ke_search Hook
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
        $dceUid = \T3\Dce\Domain\Repository\DceRepository::extractUidFromCTypeOrIdentifier($row['CType']);
        if (!$dceUid) {
            return;
        }

        $dceFieldsWithMappingsAmount = \count(DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
            'uid',
            'tx_dce_domain_model_dcefield',
            'parent_dce=' . $dceUid . ' AND map_to="tx_dce_index" AND deleted=0 AND hidden=0'
        ));
        if (!$dceFieldsWithMappingsAmount) {
            return;
        }

        $fullRow = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
            '*',
            'tt_content',
            'uid=' . $row['uid']
        );
        if ($fullRow['tx_dce_index']) {
            $bodytext = $this->sanitizeBodytext($fullRow['tx_dce_index']);
        }
    }

    /**
     * Performing the same bodytext replacements like ke_search itself
     *
     * @param string $bodytext
     * @return string
     * @see \TeaminmediasPluswerk\KeSearch\Indexer\Types\Page::getContentFromContentElement()
     */
    protected function sanitizeBodytext(string $bodytext) : string
    {
        // following lines prevents having words one after the other like: HelloAllTogether
        $bodytext = str_replace('<td', ' <td', $bodytext);
        $bodytext = str_replace('<br', ' <br', $bodytext);
        $bodytext = str_replace('<p', ' <p', $bodytext);
        $bodytext = str_replace('<li', ' <li', $bodytext);
        return strip_tags($bodytext);
    }
}
