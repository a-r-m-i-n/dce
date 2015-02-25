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
 * Gets the current language key as string
 *
 * @copyright  2012-2015 Copyright belongs to the respective authors
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @see \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger
 */
class CurrentLanguageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper  {

	/**
	 * Returns the current language key
	 *
	 * @return string Current language key
	 */
	public function render() {
		if (TYPO3_MODE === 'FE') {
			if (isset($GLOBALS['TSFE']->config['config']['language'])) {
				return $GLOBALS['TSFE']->config['config']['language'];
			}
		} elseif (strlen($GLOBALS['BE_USER']->uc['lang']) > 0) {
			return $GLOBALS['BE_USER']->uc['lang'];
		}
		return 'default';
	}
}