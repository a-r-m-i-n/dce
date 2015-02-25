<?php
namespace DceTeam\Dce\ViewHelpers;
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
 * Returns the given index of an array.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ArrayGetIndexViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Returns the value of the given index in the given array. To make sure the indexes are numeric the
	 * array will be converted. Named array keys will be overwritten by ascending index numbers (starting with 0).
	 *
	 * @param array $subject The array to get the value of
	 * @param int|string $index Index of array. May be int or string. Default is  zero (0).
	 *
	 * @return mixed The value of the given array index
	 */
	public function render(array $subject = NULL, $index = 0) {
		if ($subject === NULL) {
			$subject = $this->renderChildren();
		}
		$subject = array_values($subject);
		return $subject[$index];
	}
}