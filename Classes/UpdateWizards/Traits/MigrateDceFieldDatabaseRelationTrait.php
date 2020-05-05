<?php declare(strict_types=1);
namespace T3\Dce\UpdateWizards\Traits;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\Connection;

trait MigrateDceFieldDatabaseRelationTrait
{
    /**
     * @param string|array|mixed $customMessages Used in older TYPO3 versions
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
                'sorting' => $sectionFieldRelation['sorting']
            ];
            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dcefield');
            $connection->update(
                'tx_dce_domain_model_dcefield',
                $updateValues,
                [
                    'uid' => (int) $sectionFieldRelation['uid_foreign']
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

                    if ((int) $dceField['type'] === 2) {
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
                            'uid_foreign' => $dceFieldInsertUid
                        ],
                        [
                            'uid_local' => (int) $dceFieldRelation['uid_local'],
                            'uid_foreign' => (int) $dceFieldRelation['uid_foreign']
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
                'sorting' => $dceFieldRelation['sorting']
            ];
            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dcefield');
            $connection->update(
                'tx_dce_domain_model_dcefield',
                $updateValues,
                [
                    'uid' => (int) $dceFieldRelation['uid_foreign']
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
                ->set('deleted', 1)
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
     * Returns DceFields without set parent field
     *
     * @return array DceField row
     */
    protected function getUpdatableDceFields() : array
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
     * @return string|null
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getSourceTableNameForDceField() : ?string
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
     * @return string|null
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getSourceTableNameForSectionField() : ?string
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
}
