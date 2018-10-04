<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2018 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
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
    public static function getDatabaseConnection()
    {
        return GeneralUtility::makeInstance(DatabaseConnection::class);
    }

    /**
     * Gets dce uid by content element uid
     *
     * @param array $row of tt_content record
     * @return int uid of DCE used for this content element
     */
    public static function getDceUidByContentElementRow(array $row)
    {
        if (!StringUtility::beginsWith($row['CType'], 'dce_dceuid')) {
            return 0;
        }
        return (int) substr($row['CType'], \strlen('dce_dceuid'));
    }

    /**
     * Get enabledFields for given table name, respecting TYPO3_MODE. Includes deleteClause
     *
     * @param string $tableName
     * @return string SQL where part containing enabled fields
     */
    public static function getEnabledFields($tableName)
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
     * @param array|integer $contentElement The content element database record (or UID)
     * @return \ArminVieweg\Dce\Domain\Model\Dce|null The constructed DCE object or null
     */
    public static function getDceObjectForContentElement($contentElement)
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
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class
        );
        /** @var \ArminVieweg\Dce\Domain\Repository\DceRepository $dceRepository */
        $dceRepository = $objectManager->get(\ArminVieweg\Dce\Domain\Repository\DceRepository::class);
        /** @var \TYPO3\CMS\Extbase\Service\FlexFormService $flexFormService */
        $flexFormService = $objectManager->get('TYPO3\CMS\Extbase\Service\FlexFormService');

        // Convert flexform XML to array
        $flexData = $flexFormService->convertFlexFormContentToArray($contentElement['pi_flexform'], 'lDEF', 'vDEF');

        // Retrieve DCE domain model object
        $dceUid = self::getDceUidByContentElementRow($contentElement);
        $dce = $dceRepository->findAndBuildOneByUid(
            $dceUid,
            $flexData['settings'],
            $contentElement
        );
        return $dce;
    }
}
