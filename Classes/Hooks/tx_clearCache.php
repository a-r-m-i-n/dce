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
 * Clear Cache Hook
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class tx_clearCache {

	/**
	 * Clears the dce cache files
	 * @param $params
	 * @return void
	 */
	public function clearDceCache($params) {
		if ($params['cacheCmd'] === 'all' || $params['cacheCmd'] === 'temp_cached') {
			if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 6000000) {
				if (file_exists($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceLocalconfPath'])) {
					unlink($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceLocalconfPath']);
				}
				if (file_exists($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath'])) {
					unlink($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath']);
				}
			} else {
				t3lib_extMgm::removeCacheFiles('temp_CACHED_dce');
			}
		}
	}
}
?>