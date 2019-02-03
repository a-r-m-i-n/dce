<?php
namespace T3\Dce\Utility;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Domain\Repository\DceRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Database utility
 */
class DatabaseUtility
{
    /**
     * Returns a custom DatabaseConnection object, which uses Doctrine DBAL API under the hood.
     *
     * @return DatabaseConnection
     */
    public static function getDatabaseConnection() : DatabaseConnection
    {
        return GeneralUtility::makeInstance(DatabaseConnection::class);
    }

    /**
     * Gets dce uid by content element uid
     *
     * @param array $row of tt_content record
     * @return int uid of DCE used for this content element
     */
    public static function getDceUidByContentElementRow(array $row) : int
    {
        return DceRepository::extractUidFromCTypeOrIdentifier($row['CType']) ?? 0;
    }

    /**
     * Get enabledFields for given table name, respecting TYPO3_MODE. Includes deleteClause
     *
     * @param string $tableName
     * @return string SQL where part containing enabled fields
     */
    public static function getEnabledFields(string $tableName) : string
    {
        if (TYPO3_MODE === 'BE') {
            $enableFields = BackendUtility::BEenableFields($tableName) . BackendUtility::deleteClause($tableName);
            return $enableFields;
        } else {
            /** @var $contentObjectRenderer ContentObjectRenderer */
            $contentObjectRenderer = GeneralUtility::makeInstance(
                ContentObjectRenderer::class
            );
            $enableFields = $contentObjectRenderer->enableFields($tableName);
            return $enableFields;
        }
    }

    /**
     * Creates DCE domain object for a given content element
     *
     * @param array|int$contentElement The content element database record (or UID)
     * @return Dce|null The constructed DCE object or null
     */
    public static function getDceObjectForContentElement($contentElement) : ?Dce
    {
        if (\is_string($contentElement) && strpos($contentElement, 'NEW') === 0) {
            throw new \InvalidArgumentException('This is a new content element, can\'t create DCE instance from it.');
        }
        // Make this method more comfortable:
        // Retrieve content element record if only UID is given.
        if (is_numeric($contentElement)) {
            $contentElement = BackendUtility::getRecordWSOL(
                'tt_content',
                $contentElement,
                '*',
                '',
                false
            );
        }

        // If "pi_flexform" field is not set in the passed contenet element record
        // retrieve the whole tt_content record
        if (!isset($contentElement['pi_flexform'])) {
            $contentElement = BackendUtility::getRecordWSOL(
                'tt_content',
                $contentElement['uid'],
                '*',
                '',
                false
            );
        }

        // Make instance of "DceRepository" and "FlexFormService"
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var DceRepository $dceRepository */
        $dceRepository = $objectManager->get(DceRepository::class);

        // Convert flexform XML to array
        $flexData = FlexformService::get()
                        ->convertFlexFormContentToArray($contentElement['pi_flexform'], 'lDEF', 'vDEF');

        // Retrieve DCE domain model object
        $dceUid = self::getDceUidByContentElementRow($contentElement);
        $dce = $dceRepository->findAndBuildOneByUid(
            $dceUid,
            $flexData['settings'] ?? [],
            $contentElement
        );
        return $dce;
    }
}
