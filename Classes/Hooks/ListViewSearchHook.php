<?php
namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2019 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ListViewSearchHook
{
    public function makeSearchStringConstraints(
        QueryBuilder $queryBuilder,
        array $constraints,
        $searchString,
        $table,
        $currentPid
    ): array {
        if ($table === 'tt_content') {
            $dceConstraint = $queryBuilder->expr()->andX(
                'CType LIKE "dce_%" AND tx_dce_index LIKE "%' . $searchString . '%"'
            );
            $constraints[] = $dceConstraint;
        }
        return $constraints;
    }
}
