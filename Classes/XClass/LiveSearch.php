<?php
namespace T3\Dce\XClass;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * XClass LiveSearch
 */
class LiveSearch extends \TYPO3\CMS\Backend\Search\LiveSearch\LiveSearch
{
    /**
     * Includes DCE content elements to CTypes which should get search by field "bodytext"
     *
     * @param QueryBuilder $queryBuilder
     * @param string $tableName
     * @param array $fieldsToSearchWithin
     * @return string
     */
    protected function makeQuerySearchByTable(
        QueryBuilder &$queryBuilder,
        string $tableName,
        array $fieldsToSearchWithin
    ) {
        $whereClause = (string) parent::makeQuerySearchByTable($queryBuilder, $tableName, $fieldsToSearchWithin);
        $searchString = 'CType=\'text\' OR CType=\'textpic\'';
        $dceAppendix = ' OR CType LIKE \'dce_%\'';
        if (strpos($whereClause, $searchString) !== false) {
            $whereClause = str_replace($searchString, $searchString . $dceAppendix, $whereClause);
        }
        return $whereClause;
    }
}
