<?php

namespace T3\Dce\EventListener;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Backend\Search\Event\ModifyQueryForLiveSearchEvent;

class ModifyQueryForLiveSearchEventListener
{
    public function extendSearchStringConstraints(ModifyQueryForLiveSearchEvent $event): void
    {
        if ('tt_content' === $event->getTableName()) {
            $queryBuilder = $event->getQueryBuilder();

            $parameters = $queryBuilder->getParameters();
            $firstParameter = trim(reset($parameters), '%');

            $expression = $queryBuilder->expr()->and(
                $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('dce_%')),
                $queryBuilder->expr()->like(
                    'tx_dce_index',
                    $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($firstParameter) . '%')
                )
            );

            $queryBuilder->orWhere($expression);
        }
    }
}
