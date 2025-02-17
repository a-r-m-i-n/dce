<?php

namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Domain\Repository\DceRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseUtility
{
    /**
     * Get TYPO3s Connection Pool.
     */
    public static function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * Give it a non executed QueryBuilder to fetch result as array
     * You have the possibility to add a col as ArrayKey.
     */
    public static function getRowsFromQueryBuilder(QueryBuilder $queryBuilder, string $columnAsKey = ''): array
    {
        $statement = $queryBuilder->executeQuery();
        $rows = [];
        while ($row = $statement->fetchAssociative()) {
            if (!empty($columnAsKey)) {
                $rows[$row[$columnAsKey]] = $row;
            } else {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Get all tables and table configuration of all configured databases.
     */
    public static function adminGetTables(): array
    {
        $tables = [];
        foreach (static::getConnectionPool()->getConnectionNames() as $connectionName) {
            $connection = static::getConnectionPool()->getConnectionByName($connectionName);
            foreach ($connection->createSchemaManager()->listTableNames() as $tableName) {
                $tables[$tableName] = $tableName;
            }
        }

        return $tables;
    }

    /**
     * Get all fields and field configuration of given $tableName.
     *
     * @return array Key is column name, value is an assoc array with "Type" key
     */
    public static function adminGetFields(string $tableName): array
    {
        $columns = static::getConnectionPool()->getConnectionForTable($tableName)->createSchemaManager()
            ->listTableColumns($tableName);

        $fields = [];
        foreach ($columns as $column) {
            $fields[$column->getName()] = [
                'Type' => $column->getType(),
            ];
        }

        return $fields;
    }

    /**
     * Gets dce uid by content element uid.
     *
     * @param array $row of tt_content record
     *
     * @return int uid of DCE used for this content element
     */
    public static function getDceUidByContentElementRow(array $row): int
    {
        return DceRepository::extractUidFromCTypeOrIdentifier($row['CType']) ?? 0;
    }

    /**
     * Creates DCE domain object for a given content element.
     *
     * @param array|int|string|null $contentElement The content element database record (or UID)
     *
     * @return Dce|null The constructed DCE object or null
     */
    public static function getDceObjectForContentElement(mixed $contentElement = null, bool $doNotCache = false): ?Dce
    {
        if (null === $contentElement || (is_string($contentElement) && str_starts_with($contentElement, 'NEW'))) {
            throw new \InvalidArgumentException('This is a new content element, can\'t create DCE instance from it.');
        }
        // Make this method more comfortable:
        // Retrieve content element record if only UID is given.
        if (is_numeric($contentElement)) {
            $contentElement = BackendUtility::getRecordWSOL(
                'tt_content',
                $contentElement,
                '*',
                '',
                false
            );
        }

        // If "pi_flexform" field is not set in the passed content element record
        // retrieve the whole tt_content record
        if (!isset($contentElement['pi_flexform'])) {
            $contentElement = BackendUtility::getRecordWSOL(
                'tt_content',
                $contentElement['uid'],
                '*',
                '',
                false
            );
        }

        /** @var DceRepository $dceRepository */
        $dceRepository = GeneralUtility::makeInstance(DceRepository::class);

        // Convert flexform XML to array
        $flexData = FlexformService::get()->convertFlexFormContentToArray($contentElement['pi_flexform']);

        // Retrieve DCE domain model object
        $dceUid = self::getDceUidByContentElementRow($contentElement);

        return $dceRepository->findAndBuildOneByUid(
            $dceUid,
            $flexData['settings'] ?? [],
            $contentElement,
            $doNotCache
        );
    }
}
