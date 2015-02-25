<?php
namespace DceTeam\Dce\Utility;
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
 * Utility class for file handling (especially FAL support in TYPO3 6.0)
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class File {
	/**
	 * Converts given file path to absolute or relative file path.
	 * If FAL reference is given (eg. "file:123") it will be interpret to real existing file path.
	 *
	 * @param string $file Filename (eg. "fileadmin/file.html") or FAL reference (eg. "file:123")
	 * @param bool $absolute If TRUE the given file path will be converted to absolute path.
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