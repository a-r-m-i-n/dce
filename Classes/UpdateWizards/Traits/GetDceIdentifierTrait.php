<?php declare(strict_types=1);
namespace T3\Dce\UpdateWizards\Traits;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Utility\DatabaseUtility;

trait GetDceIdentifierTrait
{
    /**
     * Returns the identifier of dce with given uid
     *
     * @param int $dceUid
     * @return string
     */
    protected function getDceIdentifier(int $dceUid) : string
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dce');
        $dce = $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dce')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($dceUid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        return is_array($dce) && !empty($dce['identifier']) ? 'dce_' . $dce['identifier'] : 'dce_dceuid' . $dceUid;
    }
}
