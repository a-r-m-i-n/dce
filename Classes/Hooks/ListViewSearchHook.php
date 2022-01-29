<?php

namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2019-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ListViewSearchHook
{
    public function makeSearchStringConstraints(
        QueryBuilder $queryBuilder,
        array $constraints,
        string $searchString,
        string $table
    ): array {
        if ('tt_content' === $table) {
            $dceConstraint = $queryBuilder->expr()->andX(
                $queryBuilder->expr()->like('CType', '"dce_%"'),
                $queryBuilder->expr()->like(
                    'tx_dce_index',
                    $queryBuilder->quote('%' . $queryBuilder->escapeLikeWildcards($searchString) . '%')
                )
            );
            $constraints[] = $dceConstraint;
        }

        return $constraints;
    }
}
