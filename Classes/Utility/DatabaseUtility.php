<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

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
        return intval(substr($contentElement['CType'], strlen('dce_dceuid')));
    }
}
