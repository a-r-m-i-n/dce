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
 * xClass for clearing the configuration cache. Adds clear of dce cache files
 */
class ux_t3lib_TCEmain extends t3lib_TCEmain {

	/**
	 * Adds clear of dce cache files
	 *
	 * @return integer The number of files deleted
	 */
	public function removeCacheFiles() {
		$fileCount = parent::removeCacheFiles();
		$fileCount += t3lib_extMgm::removeCacheFiles('temp_CACHED_dce');
		return $fileCount;
	}
}
?>