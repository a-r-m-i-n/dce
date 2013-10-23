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
 * Image viewhelper which works for preview texts as well
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Dce_ViewHelpers_ImageViewHelper extends Tx_Fluid_ViewHelpers_ImageViewHelper {

	/**
	 * Resizes a given image (if required) and renders the respective img tag
	 *
	 * @param string $src
	 * @param string $width width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param string $height height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param integer $minWidth minimum width of the image
	 * @param integer $minHeight minimum height of the image
	 * @param integer $maxWidth maximum width of the image
	 * @param integer $maxHeight maximum height of the image
	 *
	 * @return string rendered tag.
	 */
	public function render($src, $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL) {
		$imageTag = parent::render($src, $width, $height, $minWidth, $minHeight, $maxWidth, $maxHeight);
		if (TYPO3_MODE === 'BE') {
				// Make image src absolute (and respect sub folder, if existing)
			$subPart = '/';
			if (isset($_SERVER['DOCUMENT_ROOT'])) {
				$subPart = substr(PATH_site, strlen($_SERVER['DOCUMENT_ROOT']));
			}
			$imageTag = preg_replace('/(.*?src=")..\/(.*?)/i', '$1' . $subPart . '$2', $imageTag);
		}
		return $imageTag;
	}
}
?>