<?php
namespace T3\Dce\Updates;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2020 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\UpdateWizards\Traits\GetDceIdentifierTrait;

/**
 * Migrate m:n-relation of dce fields to 1:n-relation
 *
 * @deprecated Not used since TYPO3 10 anymore
 */
abstract class AbstractUpdate extends \TYPO3\CMS\Install\Updates\AbstractUpdate
{
    use GetDceIdentifierTrait;

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
     * @param string|array &$customMessages TYPO3 7.6 uses an array, 8.7 uses a string
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        return parent::performUpdate($dbQueries, $customMessages);
    }
}
