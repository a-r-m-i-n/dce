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
 * Import Hook
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class tx_dce_impexp {
	/**
	 * Update tt_content dce record on import. Also sets global for import in progress indicator used in tx_saveDce.
	 *
	 * @param array $params
	 * @return void
	 */
	public function before_setRelation(array $params) {
		/** @var $data array */
		$data = $params['data'];

		/** @var $TCEmain t3lib_TCEmain */
		$TCEmain = $params['tce'];
		$TCEmain->start(array(), array());

		if (array_key_exists('tt_content', $data)) {
			foreach($data['tt_content'] as $ttContentUid => $ttContentUpdatedFields) {
				if (array_key_exists('tx_dce_dce', $ttContentUpdatedFields)) {
					$dceUid = intval(substr($ttContentUpdatedFields['tx_dce_dce'], strlen('tx_dce_domain_model_dce_')));
					$TCEmain->updateDB('tt_content', $ttContentUid, array(
						'CType' => Tx_Dce_Domain_Repository_DceRepository::convertUidToCType($dceUid),
						'tx_dce_dce' => $dceUid
					));
				}
			}
		}
	}

	/**
	 * Sets a global before import of dce starts
	 * @param array $params
	 */
	public function before_writeRecordsRecords(array $params) {
		if (array_key_exists('tx_dce_domain_model_dce', $params['data'])) {
			$GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress'] = TRUE;
		}
	}
}