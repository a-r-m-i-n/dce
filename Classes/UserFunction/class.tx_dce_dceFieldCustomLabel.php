<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2014 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
	 * User function to get custom labels for DCE fields
	 * to show available variable name after title.
	 *
	 * It also respects section fields and child fields inside of sections
	 * and marks them with a blue "n", which indicates that the section
	 * variable contains an array with n records.
	 *
	 * @param array $parameter
	 * @return void
	 */
	public function getLabel(&$parameter) {
		if (!$this->isSectionChildField($parameter)) {
			if (!$this->isSectionField($parameter)) {
					// Standard field
				$parameter['title'] = $parameter['row']['title'] . ' <i style="font-weight: normal">{field.' . $parameter['row']['variable'] . '}</i>';
			} else {
				$parameter['title'] = $parameter['row']['title'] . ' <i style="font-weight: normal">{field.' . $parameter['row']['variable'] . '.<span style="color: blue;">n</span>}</i>';
			}
		} else {
			// Section child field
			$parentFieldRow = $this->getDceFieldRecordByUid($parameter['parent']['uid']);
			$parameter['title'] = $parameter['row']['title'] . ' <i style="font-weight: normal">{field.' . $parentFieldRow['variable'] . '.<span style="color: blue;">n.</span>' . $parameter['row']['variable'] . '}</i>';
		}
	}

	/**
	 * Checks if given parameters, belonging to a DCE field, is a
	 * child field of section
	 *
	 * @param array $parameter
	 * @return boolean TRUE if given field parameters are child field of section
	 */
	protected function isSectionChildField($parameter) {
		return $parameter['parent']['config']['MM'] === 'tx_dce_dcefield_sectionfields_mm';
	}

	/**
	 * Checks if given parameters, belonging to a DCE field, is a
	 * section field.
	 *
	 * @param array $parameter
	 * @return boolean
	 */
	protected function isSectionField($parameter) {
		return intval($parameter['row']['type']) === 2;
	}

	/**
	 * Get row of dce field of given uid
	 *
	 * @param integer $uid
	 * @return array dce field row
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