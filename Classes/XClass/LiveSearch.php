<?php

namespace T3\Dce\XClass;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * XClass LiveSearch.
 */
class LiveSearch extends \TYPO3\CMS\Backend\Search\LiveSearch\LiveSearch
{
    /**
     * @var string
     */
    private $queryString;

    /**
     * Includes DCE content elements to CTypes which should get search by field "bodytext".
     *
     * @param string $tableName
     *
     * @return CompositeExpression|string
     */
    protected function makeQuerySearchByTable(QueryBuilder &$queryBuilder, $tableName, array $fieldsToSearchWithin)
    {
        $whereClause = (string)parent::makeQuerySearchByTable($queryBuilder, $tableName, $fieldsToSearchWithin);
        if ('tt_content' === $tableName) {
            $whereClause .= ' OR ' . $queryBuilder->expr()->andX(
                $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('dce_%')),
                $queryBuilder->expr()->like(
                    'tx_dce_index',
                    $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($this->queryString) . '%')
                )
            );
        }

        return $whereClause;
    }

    /**
     * Setter for the search query string.
     *
     * @param string $queryString
     */
    public function setQueryString($queryString): void
    {
        parent::setQueryString($queryString);
        $this->queryString = $queryString;
    }
}
