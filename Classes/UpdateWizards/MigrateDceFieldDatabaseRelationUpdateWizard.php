<?php declare(strict_types=1);
namespace T3\Dce\UpdateWizards;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\UpdateWizards\Traits\MigrateDceFieldDatabaseRelationTrait;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use T3\Dce\Utility\DatabaseUtility;

/**
 * Migrate m:n-relation of dce fields to 1:n-relation
 */
class MigrateDceFieldDatabaseRelationUpdateWizard implements UpgradeWizardInterface
{
    use MigrateDceFieldDatabaseRelationTrait;

    protected $description = '';

    public function getIdentifier(): string
    {
        return 'dceMigrateDceFieldDatabaseRelationUpdate';
    }

    public function getTitle(): string
    {
        return 'EXT:dce Migrate m:n-relation of dce fields to 1:n-relation';
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function executeUpdate(): bool
    {
        return (bool) $this->update();
    }

    public function updateNecessary(): bool
    {
        $dceFieldTableFields = DatabaseUtility::adminGetFields('tx_dce_domain_model_dcefield');
        if (!array_key_exists('parent_dce', $dceFieldTableFields) ||
            !array_key_exists('parent_field', $dceFieldTableFields) ||
            !array_key_exists('sorting', $dceFieldTableFields)
        ) {
            $this->description = 'WARNING!' . PHP_EOL .
                'The database table of DceFields has no "parent_dce" and/or "parent_field" and/or ' .
                '"sorting" column. Please execute "Compare current database with ' .
                'specification" in Important Actions section here in Install Tool.';
            return true;
        }

        // Get updatable dce fields
        $updatableDceFields = $this->getUpdatableDceFields();
        if (\count($updatableDceFields) > 0) {
            // Check of source table is existing
            $dceFieldTableName = $this->getSourceTableNameForDceField();
            $secionFieldTableName = $this->getSourceTableNameForSectionField();
            if ($dceFieldTableName === null || $secionFieldTableName === null) {
                $this->description = 'FATAL ERROR!' . PHP_EOL .
                    'The script was not able to find source tables!!! ' .
                    'Two of these tables are missing (one of each group):' . PHP_EOL .
                    '- tx_dce_dce_dcefield_mm' . PHP_EOL.
                    '- zzz_deleted_tx_dce_dce_dcefield_mm' . PHP_EOL . PHP_EOL .
                    '- tx_dce_dcefield_sectionfields_mm' . PHP_EOL .
                    '- zzz_deleted_tx_dce_dcefield_sectionfields_mm';
                return true;
            }
            $this->description = 'You have ' . \count($updatableDceFields) . ' dce fields which need to get updated. ' .
                'The old relations are taking from "' . $dceFieldTableName . '" and "' . $secionFieldTableName .
                '" table.';
            return true;
        }
        return false;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
