<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
 * Save DCE Hook
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class tx_saveDce {
	/**
	 * @var array with table names, which should be used for this hook
	 */
	protected  $useThisTables = array('tx_dce_domain_model_dce', 'tx_dce_domain_model_dcefield');

	/**
	 * @var array with status names, which should be used for this hook
	 */
	protected $useThisStatus = array('update', 'new');

	/**
	 * Hook action
	 *
	 * @param $status
	 * @param $table
	 * @param $id
	 * @param $fieldArray
	 * @param $pObj
	 *
	 * @return void
	 */
    public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $pObj) {
		if (in_array($table, $this->useThisTables) && in_array($status, $this->useThisStatus)) {
			t3lib_extMgm::removeCacheFiles('temp_CACHED_dce');
		}
    }
}
?>