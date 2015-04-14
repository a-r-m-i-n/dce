<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FileInfo viewhelper
 *
 * Usage example in for sections:
 *
 * <f:for each="{field.section}" as="entry">
 *     <f:for each="{entry.images -> dce:explode()}" as="imageUid">
 *         <f:image src="file:{imageUid}" width="350" /><br />
 *         Original width: <dce:fileInfo fileUid="{imageUid}" attribute="width" />px
 *     </f:for>
 * </f:for>
 *
 * @package ArminVieweg\Dce
 */
class FileInfoViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Core\Resource\FileRepository
	 */
	protected static $fileRepository;

	/**
	 * Returns file info
	 *
	 * @param int $fileUid Uid of file to get attributes of
	 * @param string $attribute Name of attribute to return
	 * @return string
	 * @throws \Exception
	 */
	public function render($fileUid, $attribute) {
		$file = $this->getFileRepository()->findByUid((int) $fileUid);
		if (!$file instanceof \TYPO3\CMS\Core\Resource\File) {
			throw new \Exception('No file found with uid "' . (int) $fileUid . '"!', 1429046285);
		}

		$metaData = $file->_getMetaData();
		if (!array_key_exists($attribute, $metaData)) {
			throw new \Exception(
				'Given file in DCE\'s fileInfo view helper has no attribute named "' . $attribute . '". ' .
				'Most common, available attributes are: title, description, alternative, width, height',
				1429046106
			);
		}
		return $metaData[$attribute];
	}

	/**
	 * Get file repository and stores it in static property
	 *
	 * @return \TYPO3\CMS\Core\Resource\FileRepository
	 */
	protected function getFileRepository() {
		if (self::$fileRepository !== NULL) {
			return self::$fileRepository;
		}
		/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		/** @var \TYPO3\CMS\Core\Resource\FileRepository $fileRepository */
		self::$fileRepository = $objectManager->get('TYPO3\CMS\Core\Resource\FileRepository');
		return self::$fileRepository;
	}
}