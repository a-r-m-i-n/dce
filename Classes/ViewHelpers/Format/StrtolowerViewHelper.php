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
 * Convert a string to lower chars
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Dce_ViewHelpers_Format_StrtolowerViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Performs strtolower on given subject
	 *
	 * @param string $subject to perform strtolower
	 * @return string lowered string
	 */
	public function render($subject = NULL) {
		if ($subject === NULL) {
			$subject = $this->renderChildren();
		}
		return strtolower($subject);
	}
}
?>