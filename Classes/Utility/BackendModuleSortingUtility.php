<?php

declare(strict_types=1);

namespace T3\Dce\Utility;

use TYPO3\CMS\Core\Session\SessionManager;
use TYPO3\CMS\Core\Session\UserSession;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class BackendModuleSortingUtility
{
    /**
     * Return the current DCEs sorting field and order direction
     * from request or session cookie
     *
     * @param $request
     * @return array
     * @throws \TYPO3\CMS\Core\Session\Backend\Exception\SessionNotUpdatedException
     */
    public static function getSortingAndOrdering($request): array
    {
        /** @var UserSession $session */
        $session = $GLOBALS['BE_USER']->getSession();
        $backendSessionManager = GeneralUtility::makeInstance(SessionManager::class)->getSessionBackend('BE');
        $args = $request->getArguments();

        // update cookie on module sort/order change
        if (isset($args['updateSorting'])) {
            $sorting = $args['sorting'] ?? 'sorting';
            $ordering = $args['ordering'] ?? QueryInterface::ORDER_ASCENDING;
            $session->set('sorting', $sorting);
            $session->set('ordering', $ordering);
            $backendSessionManager->update($session->getIdentifier(), $session->toArray());
        } else {
            // get sorting from cookie or default
            $sorting = $session->get('sorting') ?? 'sorting';
            $ordering = $session->get('ordering') ?? QueryInterface::ORDER_ASCENDING;
        }

        return ['sorting' => $sorting, 'ordering' => $ordering];
    }
}
