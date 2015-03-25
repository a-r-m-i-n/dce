<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is free software and is                          *
 *  | licensed under GNU General Public License.                                                                ♥php  *
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>                                                          */

/**
 * Receives FAL FileReference objects
 *
 * @package ArminVieweg\Dce
 */
class FalViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

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

		$pageSelect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_pageSelect');
		$tableName = 'tt_content';
		$rows = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
			'uid',
			'sys_file_reference',
			'tablenames=' . \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->fullQuoteStr($tableName, 'sys_file_reference') .
				' AND uid_foreign=' . $contentObjectUid .
				' AND fieldname=' . \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->fullQuoteStr($field, 'sys_file_reference') .
				$pageSelect->enableFields('sys_file_reference', $pageSelect->showHiddenRecords),
			'',
			'sorting_foreign',
			'',
			'uid'
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