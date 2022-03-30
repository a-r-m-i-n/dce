<?php

namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class for file handling (especially FAL support in TYPO3 6.0).
 */
class File
{
    /**
     * Resolves path to file.
     *
     * @param string $file supports relative paths, EXT: paths, file: paths and t3:// paths
     *
     * @return string Resolved path to file
     */
    public static function get(string $file): string
    {
        try {
            $filePath = $file;
            /**
             * @deprecated  The file: and the t3:// link formats are deprecated. Please use EXT: or absolute paths, instead.
             */
            if (GeneralUtility::isFirstPartOfStr($filePath, 'file:')) {
                trigger_error(
                    'Do not use "file:" for DCE template files! ("' . $filePath . '" given)',
                    E_USER_DEPRECATED
                );
                $combinedIdentifier = substr($file, 5);
                /** @var ResourceFactory $resourceFactory */
                $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);

                $fileOrFolder = $resourceFactory->retrieveFileOrFolderObject($combinedIdentifier);
                $filePath = $fileOrFolder->getPublicUrl();
            } elseif (GeneralUtility::isFirstPartOfStr($filePath, 't3://')) {
                trigger_error(
                    'Do not use "t3://" for DCE template files! ("' . $filePath . '" given)',
                    E_USER_DEPRECATED
                );
                /** @var LinkService $linkService */
                $linkService = GeneralUtility::makeInstance(LinkService::class);

                /** @var \TYPO3\CMS\Core\Resource\File $resolvedFile */
                $resolvedFile = $linkService->resolveByStringRepresentation($filePath)['file'];
                $filePath = Environment::getPublicPath() . DIRECTORY_SEPARATOR . $resolvedFile->getPublicUrl();
            }

            return GeneralUtility::getFileAbsFileName($filePath);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Opens JSON File and returns JSON data as array.
     * File may contain "EXT:extension_key/path/to/file".
     */
    public static function openJsonFile(string $file): ?array
    {
        $content = file_get_contents(self::get($file));

        return json_decode($content, true) ?? null;
    }
}
