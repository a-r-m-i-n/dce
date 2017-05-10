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
}
