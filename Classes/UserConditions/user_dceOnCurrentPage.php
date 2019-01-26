<?php
namespace T3\Dce\UserConditions;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */

/**
 * Checks if the current page contains a DCE (instance).
 * Usage in typoscript:
 * [userFunc = \T3\Dce\UserConditions\user_dceOnCurrentPage(42)]
 *
 * 42 is a sample for the UID of DCE type.
 *
 * @param int $dceUid Uid of DCE type to check for
 * @return bool Returns true if the current page contains a DCE (instance)
 */
function user_dceOnCurrentPage(int $dceUid)
{
    if (TYPO3_MODE !== 'FE') {
        return false;
    }

    $currentPageUid = $GLOBALS['TSFE']->id;
    if (isset($GLOBALS['TSFE']->page['content_from_pid']) && $GLOBALS['TSFE']->page['content_from_pid'] > 0) {
        $currentPageUid = $GLOBALS['TSFE']->page['content_from_pid'];
    }

    $dce = \T3\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
        '*',
        'tx_dce_domain_model_dce',
        'uid=' . $dceUid
    );
    $dceIdentifier = !empty($dce['identifier']) ? 'dce_' . $dce['identifier'] : 'dce_dceuid' . $dceUid;

    return \count(\T3\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
        'uid',
        'tt_content',
        'pid=' . $currentPageUid . ' AND CType="' . $dceIdentifier . '"'
    )) > 0;
}
