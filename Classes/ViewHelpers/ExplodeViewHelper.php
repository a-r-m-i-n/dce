<?php
namespace DceTeam\Dce\ViewHelpers;
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
 * Explode viewhelper which uses the trimExplode method of \TYPO3\CMS\Core\Utility\GeneralUtility
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ExplodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Splits a string to an array.
	 *
	 * @param string $subject String to explode.
	 * @param string $delimiter Char or string to split the string into pieces. Default is a comma sign(,).
	 * @param bool $removeEmpty If TRUE empty items will be removed.
	 *
	 * @return array Exploded parts
	 */
	public function render($subject = NULL, $delimiter = ',', $removeEmpty = TRUE) {
		if ($subject === NULL) {
			$subject = $this->renderChildren();
		}

		if ($delimiter == '\n') { $delimiter = "\n"; }
		if ($delimiter == '\r') { $delimiter = "\r"; }
		if ($delimiter == '\r\n') { $delimiter = "\r\n"; }
		if ($delimiter == '\t') { $delimiter = "\t"; }

		return \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode($delimiter, $subject, $removeEmpty);
	}
}