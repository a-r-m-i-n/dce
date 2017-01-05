<?php
namespace ArminVieweg\Dce\UserConditions;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Checks if the current page contains a DCE (instance).
 * Usage in typoscript:
 * [userFunc = \ArminVieweg\Dce\UserConditions\user_dceOnCurrentPage(42)]
 *
 * 42 is a sample for the UID of DCE type.
 *
 * @param int $dceUid Uid of DCE type to check for
 * @return bool Returns true if the current page contains a DCE (instance)
 */
function user_dceOnCurrentPage($dceUid)
{
    if (TYPO3_MODE !== 'FE') {
        return false;
    }

    $currentPageUid = $GLOBALS['TSFE']->id;
    if (isset($GLOBALS['TSFE']->page['content_from_pid']) && $GLOBALS['TSFE']->page['content_from_pid'] > 0) {
        $currentPageUid = $GLOBALS['TSFE']->page['content_from_pid'];
    }
    return (bool) \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_SELECTcountRows(
        'uid',
        'tt_content',
        'pid=' . $currentPageUid . ' AND CType="dce_dceuid' . (int) $dceUid . '"'
    ) > 0;
}
