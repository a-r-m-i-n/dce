<?php
namespace ArminVieweg\Dce\Updates;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Migrate m:n-relation of dce fields to 1:n-relation
 *
 * @package ArminVieweg\Dce
 */
class AbstractUpdate extends \TYPO3\CMS\Install\Updates\AbstractUpdate
{
    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        return parent::checkForUpdate($description);
    }

    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries Queries done in this update
     * @param mixed &$customMessages Custom messages
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        return parent::performUpdate($dbQueries, $customMessages);
    }

    /**
     * Adds last query to given referenced array
     *
     * @param array $dbQueries
     * @return void
     */
    protected function storeLastQuery(&$dbQueries)
    {
        $dbQueries[] = $this->getDatabaseConnection()->debug_lastBuiltQuery;
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
