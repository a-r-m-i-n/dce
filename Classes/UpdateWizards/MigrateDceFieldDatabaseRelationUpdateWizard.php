<?php

declare(strict_types = 1);

namespace T3\Dce\UpdateWizards;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migrate m:n-relation of dce fields to 1:n-relation.
 */
class MigrateDceFieldDatabaseRelationUpdateWizard implements UpgradeWizardInterface
{
    /** @var string */
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
        return (bool)$this->update();
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
            if (null === $dceFieldTableName || null === $secionFieldTableName) {
                $this->description = 'FATAL ERROR!' . PHP_EOL .
                    'The script was not able to find source tables!!! ' .
                    'Two of these tables are missing (one of each group):' . PHP_EOL .
                    '- tx_dce_dce_dcefield_mm' . PHP_EOL .
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

    /**
     * @param string|array|mixed $customMessages Used in older TYPO3 versions
     *
     * @return bool
     */
    public function update($customMessages = null): ?bool
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dce');
        $queryBuilder
            ->select('uid')
            ->from('tx_dce_domain_model_dce');
        $availableDces = DatabaseUtility::getRowsFromQueryBuilder($queryBuilder, 'uid');

        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
            $this->getSourceTableNameForSectionField()
        );
        $sectionFieldRelations = $queryBuilder
            ->select('*')
            ->from($this->getSourceTableNameForSectionField())
            ->execute()
            ->fetchAll();

        foreach ($sectionFieldRelations as $sectionFieldRelation) {
            $updateValues = [
                'parent_field' => $sectionFieldRelation['uid_local'],
                'sorting' => $sectionFieldRelation['sorting'],
            ];
            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dcefield');
            $connection->update(
                'tx_dce_domain_model_dcefield',
                $updateValues,
                [
                    'uid' => (int)$sectionFieldRelation['uid_foreign'],
                ]
            );
        }

        $sourceTableName = $this->getSourceTableNameForDceField();
        if (!$sourceTableName) {
            return false;
        }

        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
            $sourceTableName
        );
        $dceFieldRelations = $queryBuilder
            ->selectLiteral('DISTINCT A.uid_local, A.uid_foreign')
            ->from($sourceTableName, 'A')
            ->leftJoin(
                'A',
                $sourceTableName,
                'B',
                (string)$queryBuilder->expr()->eq(
                    'A.uid_foreign',
                    $queryBuilder->quoteIdentifier('B.uid_foreign')
                )
            )
            ->where(
                $queryBuilder->expr()->neq(
                    'A.uid_local',
                    $queryBuilder->quoteIdentifier('B.uid_local')
                )
            )
            ->orderBy('A.uid_foreign', 'ASC')
            ->execute()
            ->fetchAll();

        $dceFieldUid = 0;
        foreach ($dceFieldRelations as $dceFieldRelation) {
            if ($dceFieldUid == $dceFieldRelation['uid_foreign']) {
                $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
                    'tx_dce_domain_model_dcefield'
                );
                $dceFields = $queryBuilder
                    ->select('*')
                    ->from('tx_dce_domain_model_dcefield')
                    ->where(
                        $queryBuilder->expr()->eq(
                            'uid',
                            $queryBuilder->createNamedParameter($dceFieldRelation['uid_foreign'], \PDO::PARAM_INT)
                        )
                    )
                    ->execute()
                    ->fetchAll();

                foreach ($dceFields as $dceField) {
                    $dceFieldData = $dceField;
                    unset($dceFieldData['uid']);
                    $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable(
                        'tx_dce_domain_model_dcefield'
                    );
                    $connection->insert(
                        'tx_dce_domain_model_dcefield',
                        $dceFieldData
                    );
                    $dceFieldInsertUid = $connection->lastInsertId('tx_dce_domain_model_dcefield');

                    if (2 === (int)$dceField['type']) {
                        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
                            'tx_dce_domain_model_dcefield'
                        );
                        $dceSectionFields = $queryBuilder
                            ->select('B.*')
                            ->from('tx_dce_domain_model_dcefield', 'B')
                            ->leftJoin(
                                'B',
                                'tx_dce_dcefield_sectionfields_mm',
                                'A',
                                (string)$queryBuilder->expr()->eq(
                                    'B.uid',
                                    $queryBuilder->quoteIdentifier('A.uid_foreign')
                                )
                            )
                            ->where(
                                $queryBuilder->expr()->eq(
                                    'A.uid_local',
                                    $queryBuilder->createNamedParameter($dceField['uid'], \PDO::PARAM_INT)
                                )
                            )
                            ->execute()
                            ->fetchAll();

                        foreach ($dceSectionFields as $dceSectionField) {
                            $dceSectionFieldData = $dceSectionField;
                            $dceSectionFieldData['parent_field'] = $dceFieldInsertUid;
                            unset($dceSectionFieldData['uid']);
                            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable(
                                'tx_dce_domain_model_dcefield'
                            );
                            $connection->insert(
                                'tx_dce_domain_model_dcefield',
                                $dceSectionFieldData
                            );
                        }
                    }

                    $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable(
                        $sourceTableName
                    );
                    $connection->update(
                        $sourceTableName,
                        [
                            'uid_foreign' => $dceFieldInsertUid,
                        ],
                        [
                            'uid_local' => (int)$dceFieldRelation['uid_local'],
                            'uid_foreign' => (int)$dceFieldRelation['uid_foreign'],
                        ]
                    );
                }
            }
            $dceFieldUid = $dceFieldRelation['uid_foreign'];
        }

        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
            $sourceTableName
        );
        $dceFieldRelations = $queryBuilder
            ->select('*')
            ->from($sourceTableName)
            ->execute()
            ->fetchAll();

        foreach ($dceFieldRelations as $dceFieldRelation) {
            if (!array_key_exists($dceFieldRelation['uid_local'], $availableDces)) {
                continue;
            }
            $updateValues = [
                'parent_dce' => $dceFieldRelation['uid_local'],
                'sorting' => $dceFieldRelation['sorting'],
            ];
            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dcefield');
            $connection->update(
                'tx_dce_domain_model_dcefield',
                $updateValues,
                [
                    'uid' => (int)$dceFieldRelation['uid_foreign'],
                ]
            );
        }

        $remainingDceFields = $this->getUpdatableDceFields();
        if (\count($remainingDceFields) > 0) {
            $dceFieldUids = [];
            foreach ($remainingDceFields as $remainingDceField) {
                $dceFieldUids[] = $remainingDceField['uid'];
            }

            $message = 'After the update ' . \count($remainingDceFields) . ' remain without parent value. ' .
                'This means, no MM relation was existing for these fields. So they were lost in the ' .
                'past anyway. Setting deleted=1 to these fields. (uids: ' . implode(',', $dceFieldUids) . ')';

            if (\is_array($customMessages)) {
                $customMessages[] = $message;
            } else {
                $customMessages = $message;
            }

            $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
                'tx_dce_domain_model_dcefield'
            );
            $queryBuilder
                ->update('tx_dce_domain_model_dcefield')
                ->set('deleted', '1')
                ->where(
                    $queryBuilder->expr()->in(
                        'uid',
                        $queryBuilder
                            ->createNamedParameter($dceFieldUids, Connection::PARAM_INT_ARRAY)
                    )
                )
                ->execute();
        }

        return true;
    }

    /**
     * Returns DceFields without set parent field.
     *
     * @return array DceField row
     */
    protected function getUpdatableDceFields(): array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dcefield');

        return $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->eq(
                    'parent_dce',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'parent_field',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll();
    }

    /**
     * Get source table name for DceField. If no source table existing
     * the method returns null. Otherwise the table name.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getSourceTableNameForDceField(): ?string
    {
        $tables = DatabaseUtility::adminGetTables();
        if (array_key_exists('tx_dce_dce_dcefield_mm', $tables)) {
            return 'tx_dce_dce_dcefield_mm';
        }
        if (array_key_exists(
            'zzz_deleted_tx_dce_dce_dcefield_mm',
            $tables
        )) {
            return 'zzz_deleted_tx_dce_dce_dcefield_mm';
        }

        return null;
    }

    /**
     * Get source table name for Section Field. If no source table existing
     * the method returns null. Otherwise the table name.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getSourceTableNameForSectionField(): ?string
    {
        $tables = DatabaseUtility::adminGetTables();
        if (array_key_exists('tx_dce_dcefield_sectionfields_mm', $tables)) {
            return 'tx_dce_dcefield_sectionfields_mm';
        }
        if (array_key_exists(
            'zzz_deleted_tx_dce_dcefield_sectionfields_mm',
            $tables
        )) {
            return 'zzz_deleted_tx_dce_dcefield_sectionfields_mm';
        }

        return null;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
