<?php

namespace T3\Dce\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * FileInfo viewhelper.
 *
 * Usage example for sections:
 *
 * <f:for each="{field.section}" as="entry">
 *     <f:for each="{entry.images -> dce:explode()}" as="imageUid">
 *         <f:image src="file:{imageUid}" width="350" /><br />
 *         Width: <dce:fileInfo fileUid="{imageUid}" attribute="width" />px
 *     </f:for>
 * </f:for>
 */
class FileInfoViewHelper extends AbstractViewHelper
{
    protected static ?FileRepository $fileRepository = null;

    /**
     * @var array
     */
    protected static $files = [];

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('fileUid', 'integer', 'Uid of file to get attributes of', true);
        $this->registerArgument('attribute', 'string', 'Name of attribute to return', true);
    }

    /**
     * Returns file info
     * Merges metadata of with properties of file. Properties have got higher
     * priority.
     *
     * @return string
     */
    public function render()
    {
        $file = $this->getFile($this->arguments['fileUid']);
        $properties = array_merge($file->getMetaData()->get(), $file->getProperties());

        if (!array_key_exists($this->arguments['attribute'], $properties)) {
            throw new \Exception('Given file in DCE\'s fileInfo view helper has no attribute named "' . $this->arguments['attribute'] . '". Most common, available attributes are: title, description, alternative, width, height, name, extension, size and uid', 1429046106);
        }

        return $properties[$this->arguments['attribute']];
    }

    protected function getFile(int $fileUid): File
    {
        if (array_key_exists($fileUid, self::$files)) {
            return self::$files[$fileUid];
        }
        $file = $this->getFileRepository()->findByUid($fileUid);
        self::$files[$fileUid] = $file;

        return $file;
    }

    /**
     * Get file repository and stores it in static property.
     */
    protected function getFileRepository(): FileRepository
    {
        if (null !== self::$fileRepository) {
            return self::$fileRepository;
        }

        self::$fileRepository = GeneralUtility::makeInstance(FileRepository::class);

        return self::$fileRepository;
    }
}
