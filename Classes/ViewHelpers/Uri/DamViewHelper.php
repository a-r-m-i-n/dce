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
 * Converts a content object and given dce field to media path and filename.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Dce_ViewHelpers_Uri_DamViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * ...
	 *
	 * @param string $field Name of field in DCE
	 * @param array $contentObject Content object data array, which is stored in {contentObject} in dce template.
	 * @param boolean $returnArray If TRUE it returns an array with found media. If FALSE, returns them comma separated.
	 *
	 * @return string|array String or array with found media
	 */
	public function render($field, array $contentObject, $returnArray = FALSE) {
		if (!t3lib_extMgm::isLoaded('dam')) {
			throw new Exception(
				'The dam extension is not installed. No need to use the dce:uri.dam() viewhelper! Use f:uri.image() viewhelper instead.',
				1335781788);
		}

		/** @var $dam tx_dam_db */
		$dam = t3lib_div::makeInstance('tx_dam_db');

		$media = $dam->getReferencedFiles(
			'tt_content',
			intval($contentObject['uid']),
			$field,
			'tx_dam_mm_ref'
		);
		$media = $media['files'];

		if ($returnArray != FALSE) {
				// returns string
			return implode(',', $media);
		} else {
				// returns array
			return $media;
		}
	}
}
?>