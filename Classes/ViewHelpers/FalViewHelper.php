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
 * Receives FAL FileReference objects
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Dce_ViewHelpers_FalViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Gets FileReference objects (FAL)
	 * Requires TYPO3 6.0 or greater.
	 *
	 * @param string $field Name of field in DCE
	 * @param array $contentObject Content object data array, which is stored in {contentObject} in dce template.
	 *
	 * @return array|string String or array with found media
	 */
	public function render($field, array $contentObject) {
		$contentObjectUid = intval($contentObject['uid']);

		/** @var t3lib_DB $typo3Database */
		$typo3Database = Tx_Dce_Utility_DatabaseUtility::getDatabaseConnection();
		$rows = $typo3Database->exec_SELECTgetRows(
			'uid',
			'sys_file_reference',
			'tablenames = "tt_content" AND uid_foreign = ' . $contentObjectUid . ' AND fieldname = "' . $field . '" AND deleted = 0 AND hidden = 0'
		);

		/** @var \TYPO3\CMS\Core\Resource\FileRepository $fileRepository */
		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		$result = array();
		foreach ($rows as $referenceUid) {
			$result[] = $fileRepository->findFileReferenceByUid(intval($referenceUid['uid']));
		}
		return $result;
	}
}