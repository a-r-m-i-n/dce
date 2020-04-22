<?php
namespace T3\Dce\Updates;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2020 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\UpdateWizards\Traits\MigrateFlexformSheetIdentifierTrait;

/**
 * Migrate Flexform sheet identifier
 *
 * In the past DCE named tabs in flexform configuration like this:
 * <sheet0></sheet0>
 *
 * But this has the effect that all your data is broken, when you change
 * the order of tabs in a DCE. Now the sheets have a named identifier. You
 * can set the identifier in the variable field which is also visible for
 * tab fields, now.
 *
 * The flexform configuration looks like this now:
 * <sheet.tabGeneral></sheet.tabGeneral>
 *
 * The very first sheet has the identifier/variable "tabGeneral" by default.
 *
 * Please migrate the field database relations first, before executing this update!
 *
 * @deprecated Not used since TYPO3 10 anymore
 * @see \T3\Dce\UpdateWizards\MigrateFlexformSheetIdentifierUpdateWizard
 */
class MigrateFlexformSheetIdentifierUpdate extends AbstractUpdate
{
    use MigrateFlexformSheetIdentifierTrait;

    /**
     * @var string
     */
    protected $title = 'EXT:dce Migrate flexform sheet identifiers';

    /**
     * @var string
     */
    protected $identifier = 'dceMigrateFlexformSheetIdentifierUpdate';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        $tabsWithoutIdentifier = $this->getUpdatableDceFields();
        $contentElementsWithWrongXml = $this->getUpdatableContentElements();

        $description .= 'There are <b>' . \count($tabsWithoutIdentifier) . ' tab fields</b> without identifier and ' .
                        '<b>' . \count($contentElementsWithWrongXml) . ' content elements</b> with old xml structure ' .
                        'existing.<br>' .
                        'Caution! Please make sure that you\'ve migrated the mm-relation of dce fields to 1:n ' .
                        'before executing this update wizard.<br><br>';

        return \count($tabsWithoutIdentifier) > 0 || \count($contentElementsWithWrongXml) > 0;
    }

    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries Queries done in this update
     * @param string|array &$customMessages Custom messages
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        return (bool) $this->update();
    }
}
