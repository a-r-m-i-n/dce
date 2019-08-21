<?php
namespace T3\Dce\XClass;

/*  | This extension is made with love for TYPO3 CMS and is licensed
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
     * @var string
     */
    private $queryString;

    /**
     * Includes DCE content elements to CTypes which should get search by field "bodytext"
     *
     * @param QueryBuilder $queryBuilder
     * @param string $tableName
     * @param array $fieldsToSearchWithin
     * @return string
     */
    protected function makeQuerySearchByTable(QueryBuilder &$queryBuilder, $tableName, array $fieldsToSearchWithin)
    {
        $whereClause = (string) parent::makeQuerySearchByTable($queryBuilder, $tableName, $fieldsToSearchWithin);
        if ($tableName === 'tt_content') {
            $whereClause .= ' OR ' .
                $queryBuilder->expr()->orX('CType LIKE "dce_%" AND tx_dce_index LIKE "%' . $this->queryString . '%"');
        }
        return $whereClause;
    }

    /**
     * Setter for the search query string.
     *
     * @param string $queryString
     */
    public function setQueryString($queryString)
    {
        parent::setQueryString($queryString);
        $this->queryString = $queryString;
    }
}
