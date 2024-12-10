<?php

namespace T3\Dce\EventListener;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2019-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Backend\View\Event\ModifyDatabaseQueryForRecordListingEvent;

class ModifyDatabaseQueryForRecordListingEventListener
{
    public function extendSearchStringConstraints(ModifyDatabaseQueryForRecordListingEvent $event): void
    {
        if ('tt_content' === $event->getTable()) {
            $queryBuilder = $event->getQueryBuilder();
            $expression = $queryBuilder->expr()->and(
                $event->getQueryBuilder()->expr()->like('CType', '"dce_%"'),
                $event->getQueryBuilder()->expr()->like(
                    'tx_dce_index',
                    $event->getQueryBuilder()->quote('%' . $event->getQueryBuilder()->escapeLikeWildcards($event->getDatabaseRecordList()->searchString) . '%')
                ),
                $event->getQueryBuilder()->expr()->in('pid', $queryBuilder->getParameters()['dcValue1'])
            );
            $queryBuilder->orWhere($expression);
            $event->setQueryBuilder($queryBuilder);
        }
    }
}
