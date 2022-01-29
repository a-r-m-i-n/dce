<?php

namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2016-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Domain\Repository\DceRepository;
use T3\Dce\Utility\DatabaseUtility;

/**
 * ke_search Hook.
 */
class KeSearchHook
{
    /**
     * Renders DCE frontend output and returns it as bodytext value.
     *
     * @param string $bodytext Referenced content, which may get modified by this hook
     * @param array  $row      tt_content row
     */
    public function modifyContentFromContentElement(&$bodytext, array $row): void
    {
        $dceUid = DceRepository::extractUidFromCTypeOrIdentifier($row['CType']);
        if (!$dceUid) {
            return;
        }

        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dcefield');
        $dceFieldsWithMappingsAmount = $queryBuilder
            ->count('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->eq(
                    'parent_dce',
                    $queryBuilder->createNamedParameter($dceUid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'map_to',
                    $queryBuilder->createNamedParameter('tx_dce_index', \PDO::PARAM_STR)
                )
            )
            ->execute()
            ->fetchColumn(0);

        if (!$dceFieldsWithMappingsAmount) {
            return;
        }

        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
        $fullRow = $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($row['uid'], \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        if (is_array($fullRow) && $fullRow['tx_dce_index']) {
            $bodytext = $this->sanitizeBodytext($fullRow['tx_dce_index']);
        }
    }

    /**
     * Performing the same bodytext replacements like ke_search itself.
     *
     * @see \TeaminmediasPluswerk\KeSearch\Indexer\Types\Page::getContentFromContentElement()
     */
    protected function sanitizeBodytext(string $bodytext): string
    {
        // following lines prevents having words one after the other like: HelloAllTogether
        $bodytext = str_replace('<td', ' <td', $bodytext);
        $bodytext = str_replace('<br', ' <br', $bodytext);
        $bodytext = str_replace('<p', ' <p', $bodytext);
        $bodytext = str_replace('<li', ' <li', $bodytext);

        return strip_tags($bodytext);
    }
}
