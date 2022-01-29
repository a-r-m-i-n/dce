<?php

namespace T3\Dce\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Receives FAL FileReference objects.
 */
class FalViewHelper extends AbstractViewHelper
{
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
        $contentObjectUid = (int)$this->arguments['contentObject']['uid'];
        if ($this->arguments['localizedUid']) {
            $contentObjectUid = (int)(null !== $this->arguments['contentObject']['_LOCALIZED_UID'])
                ? $this->arguments['contentObject']['_LOCALIZED_UID']
                : $this->arguments['contentObject']['uid'];
        }

        if ($this->arguments['uid'] > 0) {
            $contentObjectUid = $this->arguments['uid'];
        }

        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder
            ->select('uid')
            ->from('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq(
                    'tablenames',
                    $queryBuilder->createNamedParameter($this->arguments['tableName'], \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'fieldname',
                    $queryBuilder->createNamedParameter($this->arguments['field'], \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($contentObjectUid, \PDO::PARAM_INT)
                )
            )
            ->orderBy('sorting_foreign', 'ASC');
        $rows = DatabaseUtility::getRowsFromQueryBuilder($queryBuilder, 'uid');

        /** @var FileRepository $fileRepository */
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $result = [];
        foreach ($rows as $referenceUid) {
            $result[] = $fileRepository->findFileReferenceByUid((int)$referenceUid['uid']);
        }

        return $result;
    }
}
