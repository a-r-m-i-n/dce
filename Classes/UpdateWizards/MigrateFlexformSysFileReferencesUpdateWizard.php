<?php

declare(strict_types = 1);

namespace T3\Dce\UpdateWizards;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2023-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('dceMigrateFlexformSysFileReferencesUpdateWizard')]
class MigrateFlexformSysFileReferencesUpdateWizard implements UpgradeWizardInterface
{
    /** @var string */
    protected $description = '';

    public function getIdentifier(): string
    {
        return 'dceMigrateFlexformSysFileReferencesUpdateWizard';
    }

    public function getTitle(): string
    {
        return 'Migrates records in sys_file_reference table, used by DCE (FlexForm)';
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function executeUpdate(): bool
    {
        return (bool)$this->update();
    }

    public function updateNecessary(): bool
    {
        $affectedRows = $this->getAffectedSysFileReferenceRows();
        $this->description = 'Found ' . \count($affectedRows) . ' rows in sys_file_reference with old naming!';

        return count($affectedRows) > 0;
    }

    public function update(): ?bool
    {
        $affectedFieldRows = $this->getAffectedSysFileReferenceRows();

        foreach ($affectedFieldRows as $affectedFieldRow) {
            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('sys_file_reference');
            $connection->update(
                'sys_file_reference',
                ['fieldname' => 'settings.' . $affectedFieldRow['fieldname']],
                ['uid' => $affectedFieldRow['uid']]
            );
        }

        return true;
    }

    private function getAffectedSysFileReferenceRows(): array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('r.*')
            ->from('sys_file_reference', 'r')
            ->join('r', 'tt_content', 't', 'r.uid_foreign = t.uid')
            ->join('t', 'tx_dce_domain_model_dce', 'dce', 'dce.uid = t.tx_dce_dce')
            ->join('dce', 'tx_dce_domain_model_dcefield', 'dcefield', 'dce.uid = dcefield.parent_dce')
            ->where(
                $queryBuilder->expr()->like('t.CType', $queryBuilder->createNamedParameter('dce_%'))
            )
            ->andWhere(
                $queryBuilder->expr()->notLike(
                    'r.fieldname',
                    $queryBuilder->createNamedParameter('settings.%')
                )
            )
            ->andWhere('dcefield.variable = r.fieldname')
            ->executeQuery()
            ->fetchAllAssociative();
    }
}
