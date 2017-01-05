<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Utility class for file handling (especially FAL support in TYPO3 6.0)
 *
 * @package ArminVieweg\Dce
 */
class File
{
    /**
     * Converts given file path to absolute or relative file path. If FAL reference is given (eg. "file:123") it will be
     * interpret to real existing file path. It also performs getFileAbsFileName when the $absolute parameter is true,
     * which allows you to use links like: "EXT:extension_key/path/to/file"
     *
     * @param string $file Filename (eg. "fileadmin/file.html") or FAL reference (eg. "file:123")
     * @param bool $absolute If TRUE the given file path will be converted to absolute path.
     * @return string File path (absolute or relative)
     */
    public static function getFilePath($file, $absolute = true)
    {
        $filePath = $file;
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($file, 'file:')) {
            $combinedIdentifier = substr($file, 5);
            $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();

            $fileOrFolder = $resourceFactory->retrieveFileOrFolderObject($combinedIdentifier);
            $filePath = $fileOrFolder->getPublicUrl();
        }

        if ($absolute === true) {
            return \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($filePath);
        }
        return $filePath;
    }

    /**
     * Opens JSON File and returns JSON data as array.
     * File may contain "EXT:extension_key/path/to/file"
     *
     * @param string $file
     * @return array
     */
    public static function openJsonFile($file)
    {
        $filePath = self::getFilePath($file, true);
        $content = file_get_contents($filePath);
        return json_decode($content, true);
    }
}
