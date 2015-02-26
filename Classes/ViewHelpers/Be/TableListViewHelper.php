<?php
namespace DceTeam\Dce\ViewHelpers\Be;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2015 Armin Rüdiger Vieweg <armin@v.ieweg.de>
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

/**
 * Class \TYPO3\CMS\Fluid\ViewHelpers\Be\TableListViewHelper
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class TableListViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\TableListViewHelper {

	/**
	 * Renders a record list as known from the TYPO3 list module
	 * Note: This feature is experimental!
	 *
	 * @param string $tableName name of the database table
	 * @param array $fieldList list of fields to be displayed. If empty, only the title column (configured in $TCA[$tableName]['ctrl']['title']) is shown
	 * @param int $storagePid by default, records are fetched from the storage PID configured in persistence.storagePid. With this argument, the storage PID can be overwritten
	 * @param int $levels corresponds to the level selector of the TYPO3 list module. By default only records from the current storagePid are fetched
	 * @param string $filter corresponds to the "Search String" textbox of the TYPO3 list module. If not empty, only records matching the string will be fetched
	 * @param int $recordsPerPage amount of records to be displayed at once. Defaults to $TCA[$tableName]['interface']['maxSingleDBListItems'] or (if that's not set) to 100
	 * @param string $sortField table field to sort the results by
	 * @param bool $sortDescending if TRUE records will be sorted in descending order
	 * @param bool $readOnly if TRUE, the edit icons won't be shown. Otherwise edit icons will be shown, if the current BE user has edit rights for the specified table!
	 * @param bool $enableClickMenu enables context menu
	 * @param string $clickTitleMode one of "edit", "show" (only pages, tt_content), "info"
	 * @param bool $alternateBackgroundColors if set, rows will have alternate background colors
	 * @return string the rendered record list
	 * @see localRecordList
	 */
	public function render($tableName, array $fieldList = array(), $storagePid = NULL, $levels = 0, $filter = '', $recordsPerPage = 0, $sortField = '', $sortDescending = FALSE,
							$readOnly = FALSE, $enableClickMenu = TRUE, $clickTitleMode = NULL, $alternateBackgroundColors = FALSE) {

		if (!is_object($GLOBALS['SOBE'])) {
			$GLOBALS['SOBE'] = new \stdClass();
		}
		$this->getDocInstance();

		return parent::render($tableName, $fieldList, $storagePid, $levels, $filter, $recordsPerPage, $sortField, $sortDescending,
			$readOnly, $enableClickMenu, $clickTitleMode, $alternateBackgroundColors);
	}
}