<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Database utility
 *
 * @package ArminVieweg\Dce
 */
class DatabaseUtility
{
    /**
     * Returns a valid DatabaseConnection object that is connected and ready
     * to be used static
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    public static function getDatabaseConnection()
    {
        if (!$GLOBALS['TYPO3_DB']) {
            \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->initializeTypo3DbGlobal();
        }
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * Gets dce uid by content element uid
     *
     * @param int $uid of tt_content record
     * @return int uid of DCE used for this content element
     */
    public static function getDceUidByContentElementUid($uid)
    {
        $contentElement = DatabaseUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
            'CType',
            'tt_content',
            'uid = ' . $uid
        );

        if (!StringUtility::beginsWith($contentElement['CType'], 'dce_dceuid')) {
            return 0;
        }
        return intval(substr($contentElement['CType'], strlen('dce_dceuid')));
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
                'TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer'
            );
            $enableFields = $contentObjectRenderer->enableFields($tableName);
            return $enableFields;
        }
    }

    /**
     * Creates DCE domain object for a given content element
     *
     * @param array|integer $contentElement The content element database record (or UID)
     * @return \ArminVieweg\Dce\Domain\Model\Dce The constructed DCE object
     */
    public static function getDceObjectForContentElement($contentElement)
    {
        // Make this method more comfortable:
        // Retrieve content element record if only UID is given.
        if (is_numeric($contentElement)) {
            $contentElement = BackendUtility::getRecord('tt_content', $contentElement);
        }

        // If "pi_flexform" field is not set in the passed contenet element record
        // retrieve the whole tt_content record
        if (!isset($contentElement['pi_flexform'])) {
            $contentElement = BackendUtility::getRecord('tt_content', $contentElement['uid']);
        }

        // Make instance of "DceRepository" and "FlexFormService"
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \ArminVieweg\Dce\Domain\Repository\DceRepository $dceRepository */
        $dceRepository = $objectManager->get('ArminVieweg\Dce\Domain\Repository\DceRepository');
        /** @var \TYPO3\CMS\Extbase\Service\FlexFormService $flexFormService */
        $flexFormService = $objectManager->get('TYPO3\CMS\Extbase\Service\FlexFormService');

        // Convert flexform XML to array
        $flexData = $flexFormService->convertFlexFormContentToArray($contentElement['pi_flexform'], 'lDEF', 'vDEF');

        // Retrieve DCE domain model object
        $dceUid = self::getDceUidByContentElementUid($contentElement['uid']);
        $dce = $dceRepository->findAndBuildOneByUid(
            $dceUid,
            $flexData['settings'],
            $contentElement
        );
        return $dce;
    }
}
