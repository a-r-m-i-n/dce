<?php
namespace ArminVieweg\Dce\ViewHelpers\Be;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Class \TYPO3\CMS\Fluid\ViewHelpers\Be\TableListViewHelper
 *
 * @package ArminVieweg\Dce
 */
class TableListViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\TableListViewHelper {

	/**
	 * @param string $tableName
	 * @param array $fieldList
	 * @param null $storagePid
	 * @param int $levels
	 * @param string $filter
	 * @param int $recordsPerPage
	 * @param string $sortField
	 * @param bool $sortDescending
	 * @param bool $readOnly
	 * @param bool $enableClickMenu
	 * @param null $clickTitleMode
	 * @return string the rendered record list
	 * @see localRecordList
	 */
	public function render($tableName, array $fieldList = array(), $storagePid = NULL, $levels = 0, $filter = '',
							$recordsPerPage = 0, $sortField = '', $sortDescending = FALSE,
							$readOnly = FALSE, $enableClickMenu = TRUE, $clickTitleMode = NULL,
							$alternateBackgroundColors = FALSE) {

		if (!is_object($GLOBALS['SOBE'])) {
			$GLOBALS['SOBE'] = new \stdClass();
		}
		$this->getDocInstance();

		return parent::render($tableName, $fieldList, $storagePid, $levels, $filter, $recordsPerPage, $sortField, $sortDescending,
			$readOnly, $enableClickMenu, $clickTitleMode, $alternateBackgroundColors);
	}
}