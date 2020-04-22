<?php
namespace T3\Dce\Updates;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2020 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\UpdateWizards\Traits\MigrateDceFieldDatabaseRelationTrait;
use T3\Dce\Utility\DatabaseUtility;

/**
 * Migrate m:n-relation of dce fields to 1:n-relation
 *
 * @deprecated Not used since TYPO3 10 anymore
 * @see \T3\Dce\UpdateWizards\MigrateDceFieldDatabaseRelationUpdateWizard
 */
class MigrateDceFieldDatabaseRelationUpdate extends AbstractUpdate
{
    use MigrateDceFieldDatabaseRelationTrait;

    /**
     * @var string
     */
    protected $title = 'EXT:dce Migrate m:n-relation of dce fields to 1:n-relation';

    /**
     * @var string
     */
    protected $identifier = 'dceMigrateDceFieldDatabaseRelationUpdate';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     * @throws \Doctrine\DBAL\DBALException
     */
    public function checkForUpdate(&$description)
    {
        // Check if "parent" and "sorting" fields are existing in DceField table
        $dceFieldTableFields = DatabaseUtility::adminGetFields('tx_dce_domain_model_dcefield');
        if (!array_key_exists('parent_dce', $dceFieldTableFields) ||
            !array_key_exists('parent_field', $dceFieldTableFields) ||
            !array_key_exists('sorting', $dceFieldTableFields)
        ) {
            $description .= '<div class="alert alert-warning"><strong>WARNING</strong><br>' .
                'The database table of DceFields has no <em>parent_dce</em> and/or <em>parent_field</em> and/or ' .
                '<em>sorting</em> column. Please execute <em>Compare current database with ' .
                'specification</em> in Important Actions section here in Install Tool.</div>';
            return true;
        }

        // Get updatable dce fields
        $updatableDceFields = $this->getUpdatableDceFields();
        if (\count($updatableDceFields) > 0) {
            // Check of source table is existing
            $dceFieldTableName = $this->getSourceTableNameForDceField();
            $secionFieldTableName = $this->getSourceTableNameForSectionField();
            if ($dceFieldTableName === null || $secionFieldTableName === null) {
                $description = '<div class="alert alert-danger"><strong>FATAL ERROR</strong><br> ' .
                    'The script was not able to find source tables!!! ' .
                    'Two of these tables are missing (one of each group): <ul>' .
                    '<ul style="margin-top: 10px; margin-bottom: 10px;">' .
                    '<li>tx_dce_dce_dcefield_mm</li>' .
                    '<li>zzz_deleted_tx_dce_dce_dcefield_mm</li>' .
                    '</ul><ul>' .
                    '<li>tx_dce_dcefield_sectionfields_mm</li>' .
                    '<li>zzz_deleted_tx_dce_dcefield_sectionfields_mm</li>' .
                    '</ul></div>';
                return true;
            }
            $description = '<div class="alert alert-info">' .
                'You have <b>' . \count($updatableDceFields) . ' dce fields</b> which need to get updated. ' .
                'The old relations are taking from <em>' . $dceFieldTableName .
                '</em> and <em>' . $secionFieldTableName . '</em> table.' . '</div>';
            return true;
        }
        return false;
    }

    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries Queries done in this update
     * @param string|array &$customMessages Custom messages
     * @return bool Whether everything went smoothly or not
     * @throws \Doctrine\DBAL\DBALException
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        return $this->update($customMessages);
    }

}
