<?php

namespace T3\Dce\Components\UserConditions;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Compatibility;
use T3\Dce\Utility\DatabaseUtility;

/**
 * Checks if the current page contains a DCE (instance).
 *
 * Usage in TypoScript:
 *
 * [dceOnCurrentPage("42")]
 * [dceOnCurrentPage("teaser")]
 *
 * You can pass the uid (e.g. 42) or the identifier (e.g. teaser).
 *
 * @param int|string $dceUidOrIdentifier Uid of DCE type to check for
 *
 * @return bool Returns true if the current page contains a DCE (instance)
 */
class DceOnCurrentPage
{
    public function matchCondition(array $parameters): bool
    {
        if (!Compatibility::isFrontendMode()) {
            return false;
        }

        $dceIdentifier = ltrim($parameters[0], ' =');
        if (is_numeric($dceIdentifier)) {
            $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dce');
            $dce = $queryBuilder
                ->select('*')
                ->from('tx_dce_domain_model_dce')
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($dceIdentifier, \PDO::PARAM_INT)
                    )
                )
                ->execute()
                ->fetch();

            if (!$dce) {
                return false;
            }
            $dceIdentifier = !empty($dce['identifier']) ? 'dce_' . $dce['identifier'] : 'dce_dceuid' . $dceIdentifier;
        } else {
            if (0 !== strpos($dceIdentifier, 'dce_')) {
                $dceIdentifier = 'dce_' . $dceIdentifier;
            }
        }

        $currentPageUid = $GLOBALS['TSFE']->id;
        if (isset($GLOBALS['TSFE']->page['content_from_pid']) && $GLOBALS['TSFE']->page['content_from_pid'] > 0) {
            $currentPageUid = $GLOBALS['TSFE']->page['content_from_pid'];
        }

        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');

        return \count(
            $queryBuilder
                ->select('uid')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->eq(
                        'pid',
                        $queryBuilder->createNamedParameter($currentPageUid, \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'CType',
                        $queryBuilder->createNamedParameter($dceIdentifier)
                    )
                )
                ->execute()
                ->fetchAll()
        ) > 0;
    }
}
