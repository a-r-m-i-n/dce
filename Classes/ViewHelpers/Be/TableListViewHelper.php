<?php
namespace DceTeam\Dce\ViewHelpers\Be;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is free software and is                          *
 *  | licensed under GNU General Public License.                                                                ♥php  *
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>                                                          */

/**
 * Class \TYPO3\CMS\Fluid\ViewHelpers\Be\TableListViewHelper
 *
 * @package DceTeam\Dce
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