<?php
namespace DceTeam\Dce\ViewHelpers;
	/***************************************************************
*  Copyright notice
*
*  (c) 2012-2014 Armin Rüdiger Vieweg <armin@v.ieweg.de>
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
 * This view helper handles parameter strings using typolink function of TYPO3.
 * It creates the whole <a>-Tag.
 *
 * @copyright  2012-2014 Copyright belongs to the respective authors
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TypolinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * Create a typolink.
	 *
	 * @param string $parameter Parameter string, which can be handled by typolink functionality
	 * @param string $subject Link text
	 * @return string Rendered HTML <a>-tag
	 */
	public function render($parameter, $subject = NULL) {
		if ($subject === NULL) {
			$subject = $this->renderChildren();
		}

		/** @var $cObj \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer */
		$cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
		return $cObj->getTypoLink($subject, $parameter);
	}
}