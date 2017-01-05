<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Receives FAL FileReference objects
 *
 * @package ArminVieweg\Dce
 */
class FalViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Gets FileReference objects (FAL)
     * Do not use FAL Viewhelper for DCE images anymore. Just use it when you need to access e.g. tt_address FAL images.
     *
     * @param string $field Name of field in DCE
     * @param array $contentObject Content object data array, which is stored in {contentObject} in dce template.
     * @param bool $localizeUid If true the uid gets localized (in frontend context)
     * @param string $tableName If you want to specify another table than tt_content
     * @param int $uid If positive, it overwrites the (localized) uid from contentObject
     * @return array|string String or array with found media
     */
    public function render($field, array $contentObject, $localizeUid = true, $tableName = 'tt_content', $uid = 0)
    {
        $contentObjectUid = intval($contentObject['uid']);
        if ($localizeUid) {
            $contentObjectUid = intval(
                $contentObject['_LOCALIZED_UID'] != null ? $contentObject['_LOCALIZED_UID'] : $contentObject['uid']
            );
        }

        if ($uid > 0) {
            $contentObjectUid = $uid;
        }

        /** @var \TYPO3\CMS\Frontend\Page\PageRepository $pageRepository */
        $pageRepository = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Page\PageRepository');
        $rows = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetRows(
            'uid',
            'sys_file_reference',
            'tablenames=' . DatabaseUtility::getDatabaseConnection()->fullQuoteStr($tableName, 'sys_file_reference') .
            ' AND uid_foreign=' . $contentObjectUid .
            ' AND fieldname=' . DatabaseUtility::getDatabaseConnection()->fullQuoteStr($field, 'sys_file_reference') .
            $pageRepository->enableFields('sys_file_reference', $pageRepository->showHiddenRecords),
            '',
            'sorting_foreign',
            '',
            'uid'
        );

        /** @var \TYPO3\CMS\Core\Resource\FileRepository $fileRepository */
        $fileRepository = GeneralUtility::makeInstance('TYPO3\CMS\Core\Resource\FileRepository');
        $result = [];
        foreach ($rows as $referenceUid) {
            $result[] = $fileRepository->findFileReferenceByUid(intval($referenceUid['uid']));
        }
        return $result;
    }
}
