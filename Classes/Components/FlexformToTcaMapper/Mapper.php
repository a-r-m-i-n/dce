<?php

namespace T3\Dce\Components\FlexformToTcaMapper;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlexformToTcaMapper
 * Get SQL for DCE fields which extend tt_content table.
 */
class Mapper
{
    /**
     * Returns SQL to add new fields in tt_content.
     *
     * @return string SQL CREATE TABLE statement
     */
    public static function getSql(): string
    {
        $fields = [];
        foreach (static::getDceFieldMappings() as $fieldName => $fieldType) {
            $fields[] = $fieldName . ' ' . $fieldType;
        }
        if (!empty($fields)) {
            return 'CREATE TABLE tt_content (' . PHP_EOL . implode(',' . PHP_EOL, $fields) . PHP_EOL . ');';
        }

        return '';
    }

    /**
     * Returns all DceFields which introduce new columns to tt_content.
     *
     * @return array of DceField rows or empty array
     */
    public static function getDceFieldRowsWithNewTcaColumns(): array
    {
        try {
            $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
                'tx_dce_domain_model_dcefield'
            );
            /** @var array|bool $rows */
            $rows = $queryBuilder
                ->select('*')
                ->from('tx_dce_domain_model_dcefield')
                ->where(
                    $queryBuilder->expr()->eq(
                        'map_to',
                        $queryBuilder->createNamedParameter('*newcol', \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'type',
                        $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->neq(
                        'new_tca_field_name',
                        $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->neq(
                        'new_tca_field_type',
                        $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                    )
                )
                ->execute()
                ->fetchAll();
        } catch (\Exception $exception) {
            return [];
        }

        if (false === $rows) {
            return [];
        }

        return $rows;
    }

    /**
     * Returns array with DceFields (in key) and their sql type (value).
     */
    public static function getDceFieldMappings(): array
    {
        $fieldMappings = [];
        foreach (static::getDceFieldRowsWithNewTcaColumns() as $dceFieldRow) {
            if ('auto' === $dceFieldRow['new_tca_field_type']) {
                $fieldMappings[$dceFieldRow['new_tca_field_name']] = static::getAutoFieldType($dceFieldRow);
            } else {
                $fieldMappings[$dceFieldRow['new_tca_field_name']] = $dceFieldRow['new_tca_field_type'];
            }
        }

        return $fieldMappings;
    }

    /**
     * Determines database field type for given DceField, based on the defined field configuration.
     *
     * @return string Determined SQL type of given dceFieldRow
     */
    protected static function getAutoFieldType(array $dceFieldRow): string
    {
        $fieldConfiguration = GeneralUtility::xml2array($dceFieldRow['configuration']);
        switch ($fieldConfiguration['type']) {
            case 'input':
                return 'varchar(255) DEFAULT \'\' NOT NULL';
            case 'check':
            case 'radio':
                return 'tinyint(4) unsigned DEFAULT \'0\' NOT NULL';
            case 'text':
            case 'select':
            case 'group':
            default:
                return 'text';
        }
    }

    /**
     * Check if DceFields has been mapped with TCA columns
     * and writes values to columns in database, if so.
     *
     * @param string $piFlexform
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\CMS\Core\Exception
     */
    public static function saveFlexformValuesToTca(array $row, $piFlexform): void
    {
        $dceUid = DatabaseUtility::getDceUidByContentElementRow($row);
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dcefield');
        $dceFieldsWithMapping = $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->eq(
                    'parent_dce',
                    $queryBuilder->createNamedParameter($dceUid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->neq(
                    'map_to',
                    $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                )
            )
            ->execute()
            ->fetchAll();

        if (empty($piFlexform) || 0 === \count($dceFieldsWithMapping)) {
            return;
        }

        $flexFormArray = GeneralUtility::xml2array($piFlexform);
        if (!\is_array($flexFormArray)) {
            return;
        }

        /** @var array $fieldToTcaMappings */
        $fieldToTcaMappings = [];
        foreach ($dceFieldsWithMapping as $dceField) {
            $mapTo = $dceField['map_to'];
            if ('*newcol' === $mapTo) {
                $mapTo = $dceField['new_tca_field_name'];
                if (empty($mapTo)) {
                    throw new \InvalidArgumentException('No "new_tca_field_name" given in DCE field configuration.');
                }
            }
            $fieldToTcaMappings[$dceField['variable']] = $mapTo;
        }

        $updateData = [];
        $flatFlexFormData = ArrayUtility::flatten($flexFormArray);
        foreach ($flatFlexFormData as $key => $value) {
            $fieldName = preg_replace('/.*settings\.(.*?)\.vDEF$/', '$1', $key);
            if (array_key_exists($fieldName, $fieldToTcaMappings)) {
                if (empty($updateData[$fieldToTcaMappings[$fieldName]])) {
                    $updateData[$fieldToTcaMappings[$fieldName]] = $value;
                } else {
                    $updateData[$fieldToTcaMappings[$fieldName]] .= PHP_EOL . PHP_EOL . $value;
                }
            }
        }

        if (!empty($updateData)) {
            $databaseColumns = DatabaseUtility::adminGetFields('tt_content');
            foreach (array_keys($updateData) as $columnName) {
                if (!array_key_exists($columnName, $databaseColumns)) {
                    throw new \InvalidArgumentException('You\'ve mapped the DCE field "' . ($fieldName ?? '') . '" (of DCE with uid ' . $dceUid . ') to the ' . 'non-existing tt_content column "' . $columnName . '". Please update your mapping or ensure ' . 'that the tt_content column is existing.');
                }
            }
            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tt_content');
            $connection->update(
                'tt_content',
                $updateData,
                [
                    'uid' => (int)$row['uid'],
                ]
            );
        }
    }
}
