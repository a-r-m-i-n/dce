<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Utility class for handling Database Connection
 * This is needed as the TYPO3 Connection handling is different in 6.1+
 * and the DCE extensions needs to connect to the DB at startup time (ext_localconf.php)
 *
 * @package ArminVieweg\Dce
 */
class DatabaseUtility {

	/**
	 * Returns a valid DatabaseConnection object that is connected and ready to be
	 * used static
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	public static function getDatabaseConnection() {
		if (!$GLOBALS['TYPO3_DB']) {
			\TYPO3\CMS\Core\Core\Bootstrap::getInstance()->initializeTypo3DbGlobal();
		}
		return $GLOBALS['TYPO3_DB'];
	}
}