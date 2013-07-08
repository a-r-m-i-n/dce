<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class tx_dce_dceFieldCustomLabel {

	/**
	 * User function to get custom label
	 *
	 * @param $parameter
	 *
	 * @return void
	 */
	public function getLabel(&$parameter) {
		$prepend = '{field.';
		if(isset($parameter['parent'])) {
			$parentRow = $this->getDceFieldRecordByUid($parameter['parent']['uid']);
			if ($parentRow['type'] == '2') {
				$prepend = '{field.' . $parentRow['variable'] . '.<span style="color: blue;">n.</span>';
			}
		}

		$row = $parameter['row'];
		if ($row['type'] == '2' || $row['type'] == '0' && is_array($parameter['parent'])) {
			// Element
			$parameter['title'] = $row['title'] . ' <i style="font-weight: normal;">' . $prepend . $row['variable'] . '}</i>';
		} else {
			// Tab
			$parameter['title'] = $row['title'];
		}
	}


	/**
	 * @param integer $uid
	 * @return array
	 */
	protected function getDceFieldRecordByUid($uid) {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'*',
			'tx_dce_domain_model_dcefield',
			'uid=' . intval($uid)
		);
	}
}
?>