<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class for file handling (especially FAL support in TYPO3 6.0)
 *
 * @package ArminVieweg\Dce
 */
class File
{
    /**
     * Resolves path to file.
     *
     * @param string $file Supports relative paths, EXT: paths, file: paths and t3:// paths.
     * @return string Resolved path to file
     */
    public static function get($file)
    {
        $filePath = $file;
        if (GeneralUtility::isFirstPartOfStr($filePath, 'file:')) {
            $combinedIdentifier = substr($file, 5);
            $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();

            $fileOrFolder = $resourceFactory->retrieveFileOrFolderObject($combinedIdentifier);
            $filePath = $fileOrFolder->getPublicUrl();
        } elseif (GeneralUtility::isFirstPartOfStr($filePath, 't3://')) {
            /** @var \TYPO3\CMS\Core\LinkHandling\LinkService $linkService */
            $linkService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\LinkHandling\LinkService::class);

            /** @var \TYPO3\CMS\Core\Resource\File $file */
            $resolvedFile = $linkService->resolveByStringRepresentation($filePath)['file'];
            $filePath = $resolvedFile->getPublicUrl();
        }
        return GeneralUtility::getFileAbsFileName($filePath);
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
        $content = file_get_contents(self::get($file));
        return json_decode($content, true);
    }
}
