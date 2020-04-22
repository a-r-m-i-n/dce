<?php
namespace T3\Dce\Updates;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\UpdateWizards\Traits\FixMalformedDceFieldVariableNamesTrait;

/**
 * Fix dce fields with malformed variable names.
 *
 * In older versions of DCE the userfunction, which checks and corrects entered variable names to be lowerCamelCase,
 * did not work properly. But when editing such old DCEs the variables became corrected and does not match with
 * flexform structure used in tt_content.
 *
 * This update checks if such fields exist and correct them in tt_content's pi_flexform column and in DceFields.
 * It does not correct the fluid templates for you!
 *
 * @deprecated Not used since TYPO3 10 anymore
 * @see \T3\Dce\UpdateWizards\FixMalformedDceFieldVariableNamesUpdateWizard
 */
class FixMalformedDceFieldVariableNamesUpdate extends AbstractUpdate
{
    use FixMalformedDceFieldVariableNamesTrait;

    /**
     * @var string
     */
    protected $title = 'EXT:dce Fix malformed DceField variable names';

    /**
     * @var string
     */
    protected $identifier = 'dceFixMalformedDceFieldVariableNamesUpdate';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        $malformedDceFields = $this->getDceFieldsWithMalformedVariableNames();

        $description .= 'Found <b>' . \count($malformedDceFields) . ' malformed DceFields</b>! This update does not ' .
            'update malformed variable names in fluid templates! But it updates the DceField record and all ' .
            'tt_content records based on this DCE.<br>' .
            'Caution! Please make sure that you\'ve migrated the mm-relation of dce fields to 1:n ' .
            'before executing this update wizard.<br><br>';

        return \count($malformedDceFields) > 0;
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
        return $this->update();
    }
}
