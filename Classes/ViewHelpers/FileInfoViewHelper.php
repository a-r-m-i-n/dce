<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2018 Armin Vieweg <armin@v.ieweg.de>
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
 *         Width: <dce:fileInfo fileUid="{imageUid}" attribute="width" />px
 *     </f:for>
 * </f:for>
 */
class FileInfoViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @var \TYPO3\CMS\Core\Resource\FileRepository
     */
    protected static $fileRepository;

    /**
     * @var array
     */
    protected static $files = [];

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('$fileUid', 'integer', 'Uid of file to get attributes of', true);
        $this->registerArgument('attribute', 'string', 'Name of attribute to return', true);
    }

    /**
     * Returns file info
     * Merges meta data of with properties of file. Properties have got higher
     * priority.
     *
     * @return string
     * @throws \Exception
     */
    public function render()
    {
        $file = $this->getFile($this->arguments['fileUid']);
        $properties = array_merge($file->_getMetaData(), $file->getProperties());
        if (!array_key_exists($this->arguments['attribute'], $properties)) {
            throw new \Exception(
                'Given file in DCE\'s fileInfo view helper has no attribute named "' . $this->arguments['attribute'] .
                '". Most common, available attributes are: title,description,alternative,width,height,name,' .
                'extension,size,uid',
                1429046106
            );
        }
        return $properties[$this->arguments['attribute']];
    }

    /**
     * Get file
     *
     * @param int $fileUid
     * @return \TYPO3\CMS\Core\Resource\File
     * @throws \Exception
     */
    protected function getFile($fileUid)
    {
        if (array_key_exists($fileUid, self::$files)) {
            return self::$files[$fileUid];
        }
        $file = $this->getFileRepository()->findByUid((int)$fileUid);
        if (!$file instanceof \TYPO3\CMS\Core\Resource\File) {
            throw new \Exception('No file found with uid "' . (int)$fileUid . '"!', 1429046285);
        }
        self::$files[$fileUid] = $file;
        return $file;
    }

    /**
     * Get file repository and stores it in static property
     *
     * @return \TYPO3\CMS\Core\Resource\FileRepository
     */
    protected function getFileRepository()
    {
        if (self::$fileRepository !== null) {
            return self::$fileRepository;
        }
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        /** @var \TYPO3\CMS\Core\Resource\FileRepository $fileRepository */
        self::$fileRepository = $objectManager->get(\TYPO3\CMS\Core\Resource\FileRepository::class);
        return self::$fileRepository;
    }
}
