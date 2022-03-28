<?php

namespace T3\Dce\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Compatibility;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
    /**
     * @var FileRepository
     */
    protected static $fileRepository;

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
     * Merges meta data of with properties of file. Properties have got higher
     * priority.
     *
     * @return string
     *
     * @throws \Exception
     */
    public function render()
    {
        $file = $this->getFile($this->arguments['fileUid']);

        if (Compatibility::isTypo3Version('10.0.0')) {
            $properties = array_merge($file->getMetaData()->get(), $file->getProperties());
        } else {
            $properties = array_merge($file->_getMetaData(), $file->getProperties());
        }

        if (!array_key_exists($this->arguments['attribute'], $properties)) {
            throw new \Exception('Given file in DCE\'s fileInfo view helper has no attribute named "' . $this->arguments['attribute'] . '". Most common, available attributes are: ' . 'title, description, alternative, width, height, name, extension, size and uid', 1429046106);
        }

        return $properties[$this->arguments['attribute']];
    }

    /**
     * Get file.
     *
     * @param int $fileUid
     *
     * @return File
     *
     * @throws \Exception
     */
    protected function getFile($fileUid)
    {
        if (array_key_exists($fileUid, self::$files)) {
            return self::$files[$fileUid];
        }
        $file = $this->getFileRepository()->findByUid((int)$fileUid);
        if (!$file instanceof File) {
            throw new \Exception('No file found with uid "' . (int)$fileUid . '"!', 1429046285);
        }
        self::$files[$fileUid] = $file;

        return $file;
    }

    /**
     * Get file repository and stores it in static property.
     *
     * @return FileRepository
     */
    protected function getFileRepository()
    {
        if (null !== self::$fileRepository) {
            return self::$fileRepository;
        }
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        // @var FileRepository $fileRepository
        self::$fileRepository = $objectManager->get(FileRepository::class);

        return self::$fileRepository;
    }
}
