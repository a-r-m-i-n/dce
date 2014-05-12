<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2014 Armin Rüdiger Vieweg <armin@v.ieweg.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Checks if the current page contains a DCE (instance).
 * Usage in typoscript: [userFunc = user_dceOnCurrentPage(42)]
 * 42 is a sample for the UID of DCE type.
 *
 * @param integer Uid of DCE type to check for
 * @return boolean Returns TRUE if the current page contains a DCE (instance). Otherwise returns FALSE.
 */
function user_dceOnCurrentPage($dceUid) {
	if (TYPO3_MODE !== 'FE') {
		return FALSE;
	}

	$dceUid = intval($dceUid);
	$currentPageUid = $GLOBALS['TSFE']->id;

	return ($GLOBALS['TYPO3_DB']->exec_SELECTcountRows('uid', 'tt_content', 'pid=' . $currentPageUid . ' AND CType="dce_dceuid' . $dceUid . '"') > 0);
}
?>