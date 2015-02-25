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
 * Access check hook
 * To allow edit not existing DCE entries (static DCEs)
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class tx_accessCheck {

	/**
	 * Returns TRUE if requested record is a static DCE
	 *
	 * @param array $params
	 * @return bool
	 */
	public function checkAccess(array $params) {
		if (is_numeric($params['uid'])) {
			return $params['hasAccess'];
		}
		return $params['table'] === 'tx_dce_domain_model_dce' && strpos($params['uid'], 'dce_') === 0;
	}

}