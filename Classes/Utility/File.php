<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Utility class for file handling (especially FAL support in TYPO3 6.0)
 *
 * @package ArminVieweg\Dce
 */
class File {
	/**
	 * Converts given file path to absolute or relative file path.
	 * If FAL reference is given (eg. "file:123") it will be interpret to
	 * real existing file path.
	 *
	 * @param string $file Filename (eg. "fileadmin/file.html") or FAL reference
	 *                     (eg. "file:123")
	 * @param bool $absolute If TRUE the given file path will be converted to
	 *                       absolute path.
	 * @return string File path (absolute or relative)
	 */
	static public function getFilePath($file, $absolute = TRUE) {
		$filePath = $file;
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($file, 'file:')) {
			$combinedIdentifier = substr($file, 5);
			$resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();

			$fileOrFolder = $resourceFactory->retrieveFileOrFolderObject($combinedIdentifier);
			$filePath = $fileOrFolder->getPublicUrl();
		}

		if ($absolute === TRUE) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($filePath);
		}
		return $filePath;
	}
}