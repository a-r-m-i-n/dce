<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
class tx_dce_identifierField {

	/**
	 * @param $parameter
	 * @return string
	 */
	function getIdentifierField($parameter) {
		if (empty($parameter['row'])) {
			return '<input type="text">';
		}





		\TYPO3\CMS\Core\Utility\DebugUtility::debug($parameter, 'Field');


		return '<input type="text" value="..." disabled="disabled">';
	}

}