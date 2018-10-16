<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2018 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

// phpcs:disable

/**
 * Very basic backport of old DatabaseConnection ($GLOBALS['TYPO3_DB'])
 * based on Doctrine DBAL
 */
class DatabaseConnection implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var ConnectionPool
     */
    protected $connectionPool;

    /**
     * @var QueryBuilder[] key is table name
     */
    protected $queryBuilderStorage = [];

    /**
     * @var array
     */
    private $tableNames = [];

    /**
     * @var QueryBuilder
     */
    private $lastUsedQueryBuilder;

    /**
     * DeployableRecordRepository constructor
     */
    public function __construct()
    {
        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * Returns an instance of QueryBuilder for given table
     * and caches it statically
     *
     * @param string $table
     * @return QueryBuilder
     */
    protected function getQueryBuilderForTable(string $table) : QueryBuilder
    {
        if (!array_key_exists($table, $this->queryBuilderStorage)) {
            $this->queryBuilderStorage[$table] = $this->connectionPool->getQueryBuilderForTable($table);
            $this->queryBuilderStorage[$table]->getRestrictions()->removeAll();
        }
        $this->lastUsedQueryBuilder = $this->queryBuilderStorage[$table];
        return $this->queryBuilderStorage[$table];
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function admin_get_tables() : array
    {
        if (empty($this->tableNames)) {
            $connectioNames = $this->connectionPool->getConnectionNames();
            $connection = $this->connectionPool->getConnectionByName(reset($connectioNames));
            $this->tableNames = $connection->getSchemaManager()->listTableNames();
        }
        return $this->tableNames;
    }

    /**
     * @param string $tableName
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager|\Doctrine\DBAL\Schema\Column[]|\TYPO3\CMS\Core\Database\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function admin_get_fields($tableName) : array
    {
        $connectionNames = $this->connectionPool->getConnectionNames();
        $connection = $this->connectionPool->getConnectionByName(reset($connectionNames));

        $fields = [];
        foreach ($connection->getSchemaManager()->listTableColumns($tableName) as $column) {
            $fields[$column->getName()] = [
                'Type' => $column->getType()->getName()
            ];
        }
        return $fields;
    }

    /**
     * @return string
     */
    public function debug_lastBuiltQuery()
    {
        if ($this->lastUsedQueryBuilder) {
            return $this->lastUsedQueryBuilder->getSQL();
        }
        return '';
    }


    public function fullQuoteStr($string)
    {
        return addcslashes($string, '\'"`´');
    }

    /**
     * Creates and executes a SELECT SQL-statement AND traverse result set and returns array with records in.
     *
     * @param string $select_fields List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
     * @param string $from_table Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param string $where_clause Additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string $groupBy Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit Optional LIMIT value ([begin,]max), if none, supply blank string.
     * @param string $uidIndexField If set, the result array will carry this field names value as index. Requires that field to be selected of course!
     * @return array|null Array of rows, or NULL in case of SQL error
     * @see exec_SELECTquery()
     * @throws \InvalidArgumentException
     */
    public function exec_SELECTgetRows($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '', $uidIndexField = '')
    {
        $tables = GeneralUtility::trimExplode(',', $from_table, true);
        if (\count($tables) > 1) {
            $from_table = $tables[0];
        }

        $queryBuilder = $this->getQueryBuilderForTable($from_table);
        $query = $queryBuilder->select($select_fields);

        foreach ($tables as $fromTable) {
            $query->from($fromTable);
        }

        $query->where($where_clause);

        if ($groupBy) {
            $query->groupBy([$groupBy]);
        }

        if ($orderBy) {
            $splitted = GeneralUtility::trimExplode(' ', $orderBy, true);
            $sortBy = $splitted[0];
            $direction = 'asc';
            if (isset($splitted[1])) {
                $direction = $splitted[1];
            }
            $query->orderBy($sortBy, $direction);
        }

        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        if ($uidIndexField) {
            $indexedResult = [];
            foreach ($result as $entry) {
                $indexedResult[$entry[$uidIndexField]] = $entry;
            }
            return $indexedResult;
        }

        return $result;
    }

    /**
     * Creates and executes a SELECT SQL-statement AND gets a result set and returns an array with a single record in.
     * LIMIT is automatically set to 1 and can not be overridden.
     *
     * @param string $select_fields List of fields to select from the table.
     * @param string $from_table Table(s) from which to select.
     * @param string $where_clause Optional additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     * @param string $groupBy Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy Optional ORDER BY field(s), if none, supply blank string.
     * @return array|false|null Single row, FALSE on empty result, NULL on error
     */
    public function exec_SELECTgetSingleRow($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '')
    {
        return reset($this->exec_SELECTgetRows($select_fields, $from_table, $where_clause, $groupBy, $orderBy));
    }

    /**
     * Creates and executes a SELECT query, selecting fields ($select) from two/three tables joined
     * Use $mm_table together with $local_table or $foreign_table to select over two tables. Or use all three tables to
     * select the full MM-relation. The JOIN is done with
     * [$local_table].uid <--> [$mm_table].uid_local  / [$mm_table].uid_foreign <--> [$foreign_table].uid
     * The function is very useful for selecting MM-relations between tables adhering to the MM-format used by TCE.
     * See the section on $GLOBALS['TCA'] in Inside TYPO3 for more details.
     *
     * @param string $select Field list for SELECT
     * @param string $local_table Tablename, local table
     * @param string $mm_table Tablename, relation table
     * @param string $foreign_table Tablename, foreign table
     * @param string $whereClause Optional additional WHERE clauses put in the end of the query.
     * @param string $groupBy Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit Optional LIMIT value ([begin,]max), if none, supply blank string.
     * @return array
     * @see exec_SELECTquery()
     */
    public function exec_SELECT_mm_query(
        $select,
        $local_table,
        $mm_table,
        $foreign_table,
        $whereClause = '',
        $groupBy = '',
        $orderBy = '',
        $limit = ''
    ) {
        $queryParts = $this->getSelectMmQueryParts(
            $select,
            $local_table,
            $mm_table,
            $foreign_table,
            $whereClause,
            $groupBy,
            $orderBy,
            $limit
        );
        return $this->exec_SELECTgetRows(
            $queryParts['SELECT'],
            $queryParts['FROM'],
            $queryParts['WHERE'],
            $queryParts['GROUPBY'],
            $queryParts['ORDERBY'],
            $queryParts['LIMIT']
        );
    }


    protected function getSelectMmQueryParts(
        $select,
        $local_table,
        $mm_table,
        $foreign_table,
        $whereClause = '',
        $groupBy = '',
        $orderBy = '',
        $limit = ''
    ) {
        $foreign_table_as = $foreign_table == $local_table ? $foreign_table . StringUtility::getUniqueId('_join') : '';
        $mmWhere = $local_table ? $local_table . '.uid=' . $mm_table . '.uid_local' : '';
        $mmWhere .= ($local_table and $foreign_table) ? ' AND ' : '';
        $tables = ($local_table ? $local_table . ',' : '') . $mm_table;
        if ($foreign_table) {
            $mmWhere .= ($foreign_table_as ?: $foreign_table) . '.uid=' . $mm_table . '.uid_foreign';
            $tables .= ',' . $foreign_table . ($foreign_table_as ? ' AS ' . $foreign_table_as : '');
        }
        return [
            'SELECT' => $select,
            'FROM' => $tables,
            'WHERE' => $mmWhere . ' ' . $whereClause,
            'GROUPBY' => $groupBy,
            'ORDERBY' => $orderBy,
            'LIMIT' => $limit
        ];
    }

    /**
     * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
     * Using this function specifically allows us to handle BLOB and CLOB fields depending on DB
     *
     * @param string $table Table name
     * @param array $fields_values Field values as key=>value pairs. Values will be escaped internally.
     *              Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it
     *              to this function as argument.
     * @param bool|array|string $no_quote_fields See fullQuoteArray()
     * @return bool
     */
    public function exec_INSERTquery($table, $fields_values, $no_quote_fields = false)
    {
        $query = $this->getQueryBuilderForTable($table);
        $query
            ->insert($table)
            ->values($fields_values)
            ->execute();
        return true;
    }

    /**
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function sql_insert_id()
    {
        $connectionNames = $this->connectionPool->getConnectionNames();
        $connection = $this->connectionPool->getConnectionByName(reset($connectionNames));
        return $connection->lastInsertId();
    }

    /**
     * Creates and executes an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array
     * with field/value pairs $fields_values. Using this function specifically allow us to handle BLOB and CLOB fields
     * depending on DB
     *
     * @param string $table Database tablename
     * @param string $where WHERE clause, eg. "uid=1"
     * @param array $fields_values Field values as key=>value pairs. Values will be escaped internally.
     *              Typically you would fill an array like "$updateFields" with 'fieldname'=>'value' and pass it
     *              to this function as argument.
     * @param bool|array|string $no_quote_fields See fullQuoteArray()
     * @return bool
     */
    public function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = false)
    {
        $query = $this->getQueryBuilderForTable($table);
        $query
            ->update($table)
            ->where($where);

        foreach ($fields_values as $key => $value) {
            $query->set($key, $value);
        }
        $query->execute();
        return true;
    }
}
// phpcs:enable
