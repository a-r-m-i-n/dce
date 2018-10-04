<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2018 Armin Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Receives FAL FileReference objects
 */
class FalViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('field', 'string', 'Name of field in DCE', true);
        $this->registerArgument(
            'contentObject',
            'array',
            'Content object data array, which is stored in {contentObject} in dce template.',
            true
        );
        $this->registerArgument(
            'localizedUid',
            'boolean',
            'If true the uid gets localized (in frontend context)',
            false,
            true
        );
        $this->registerArgument(
            'tableName',
            'string',
            'If you want to specify another table than tt_content',
            false,
            'tt_content'
        );
        $this->registerArgument(
            'uid',
            'integer',
            'If positive, it overwrites the (localized) uid from contentObject',
            false,
            0
        );
    }

    /**
     * Gets FileReference objects (FAL)
     * Do not use FAL Viewhelper for DCE images anymore. Just use it when you need to access e.g. tt_address FAL images.
     *
     * @return array|string String or array with found media
     */
    public function render()
    {
        $contentObjectUid = (int) $this->arguments['contentObject']['uid'];
        if ($this->arguments['localizeUid']) {
            $contentObjectUid = (int) $this->arguments['contentObject']['_LOCALIZED_UID'] !== null
                ? $this->arguments['contentObject']['_LOCALIZED_UID']
                : $this->arguments['contentObject']['uid'];
        }

        if ($this->arguments['uid'] > 0) {
            $contentObjectUid = $this->arguments['uid'];
        }

        /** @var \TYPO3\CMS\Frontend\Page\PageRepository $pageRepository */
        $pageRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
        $rows = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
            'uid',
            'sys_file_reference',
            'tablenames="' . DatabaseUtility::getDatabaseConnection()->fullQuoteStr($this->arguments['tableName']) .
            '" AND uid_foreign=' . $contentObjectUid . ' AND fieldname="' .
            DatabaseUtility::getDatabaseConnection()->fullQuoteStr($this->arguments['field']) . '" ' .
            $pageRepository->enableFields('sys_file_reference', $pageRepository->showHiddenRecords),
            '',
            'sorting_foreign',
            '',
            'uid'
        );

        /** @var \TYPO3\CMS\Core\Resource\FileRepository $fileRepository */
        $fileRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\FileRepository::class);
        $result = [];
        foreach ($rows as $referenceUid) {
            $result[] = $fileRepository->findFileReferenceByUid((int) $referenceUid['uid']);
        }
        return $result;
    }
}
