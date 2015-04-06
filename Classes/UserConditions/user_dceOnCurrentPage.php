<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Checks if the current page contains a DCE (instance).
 * Usage in typoscript: [userFunc = user_dceOnCurrentPage(42)]
 * 42 is a sample for the UID of DCE type.
 *
 * @param int Uid of DCE type to check for
 * @return bool Returns TRUE if the current page contains a DCE (instance). Otherwise returns FALSE.
 *
 * @package ArminVieweg\Dce
 */
function user_dceOnCurrentPage($dceUid) {
	if (TYPO3_MODE !== 'FE') {
		return FALSE;
	}

	$dceUid = intval($dceUid);
	$currentPageUid = $GLOBALS['TSFE']->id;
	return ($GLOBALS['TYPO3_DB']->exec_SELECTcountRows('uid', 'tt_content', 'pid=' . $currentPageUid . ' AND CType="dce_dceuid' . $dceUid . '"') > 0);
}