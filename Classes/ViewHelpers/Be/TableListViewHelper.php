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
 *  the Free Software Foundation; either version 2 of the License, or
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

if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 6002000) {
	require_once (PATH_typo3 . 'class.db_list.inc');
	require_once (PATH_typo3 . 'class.db_list_extra.inc');
}

/**
 * Class Tx_Dce_ViewHelpers_Be_TableListViewHelper
 */
class Tx_Dce_ViewHelpers_Be_TableListViewHelper extends Tx_Fluid_ViewHelpers_Be_TableListViewHelper {

	/**
	 * Renders a record list as known from the TYPO3 list module
	 * Note: This feature is experimental!
	 *
	 * @param string $tableName name of the database table
	 * @param array $fieldList list of fields to be displayed. If empty, only the title column (configured in $TCA[$tableName]['ctrl']['title']) is shown
	 * @param integer $storagePid by default, records are fetched from the storage PID configured in persistence.storagePid. With this argument, the storage PID can be overwritten
	 * @param integer $levels corresponds to the level selector of the TYPO3 list module. By default only records from the current storagePid are fetched
	 * @param string $filter corresponds to the "Search String" textbox of the TYPO3 list module. If not empty, only records matching the string will be fetched
	 * @param integer $recordsPerPage amount of records to be displayed at once. Defaults to $TCA[$tableName]['interface']['maxSingleDBListItems'] or (if that's not set) to 100
	 * @param string $sortField table field to sort the results by
	 * @param boolean $sortDescending if TRUE records will be sorted in descending order
	 * @param boolean $readOnly if TRUE, the edit icons won't be shown. Otherwise edit icons will be shown, if the current BE user has edit rights for the specified table!
	 * @param boolean $enableClickMenu enables context menu
	 * @param string $clickTitleMode one of "edit", "show" (only pages, tt_content), "info"
	 * @param boolean $alternateBackgroundColors if set, rows will have alternate background colors
	 * @return string the rendered record list
	 * @see localRecordList
	 */
	public function render($tableName, array $fieldList = array(), $storagePid = NULL, $levels = 0, $filter = '', $recordsPerPage = 0, $sortField = '', $sortDescending = FALSE, $readOnly = FALSE, $enableClickMenu = TRUE, $clickTitleMode = NULL, $alternateBackgroundColors = FALSE) {
		if (!is_object($GLOBALS['SOBE'])) {
			$GLOBALS['SOBE'] = new stdClass();
		}
		$this->getDocInstance();

		return parent::render($tableName, $fieldList, $storagePid, $levels, $filter, $recordsPerPage, $sortField, $sortDescending, $readOnly, $enableClickMenu, $clickTitleMode, $alternateBackgroundColors);
	}
}