<?php

declare(strict_types = 1);

namespace T3\Dce\Components\DetailPage;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2022 Armin Vieweg <armin@v.ieweg.de>
 */

use Doctrine\DBAL\Driver\Statement;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Domain\Repository\DceRepository;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\DceExpressionUtility;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class SlugGenerator
{
    /**
     * @var DceRepository
     */
    protected $dceRepository;

    /**
     * @var SlugHelper
     */
    protected $slugHelper;

    public function __construct(
        DceRepository $dceRepository = null,
        SlugHelper $slugHelper = null
    ) {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        if (!$dceRepository) {
            $dceRepository = $objectManager->get(DceRepository::class);
        }
        $this->dceRepository = $dceRepository;

        if (!$slugHelper) {
            $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'tt_content', 'tx_dce_slug', []);
        }
        $this->slugHelper = $slugHelper;
    }

    public static function getSlugFromDce(Dce $dce, ?string $expression = null): ?string
    {
        /** @var DceExpressionUtility $dceExpressionUtility */
        $dceExpressionUtility = GeneralUtility::makeInstance(DceExpressionUtility::class);
        if (!$expression) {
            $expression = $dce->getDetailpageSlugExpression();
        }

        return $dceExpressionUtility->evaluate($expression, $dce); // Generated slug
    }

    /**
     * @param int $uid Given tt_content uid must be a DCE content element
     */
    public function makeSlug(int $uid, int $pid, string $expression): SlugValue
    {
        $dce = $this->dceRepository->getDceInstance($uid);

        $slug = self::getSlugFromDce($dce, $expression);
        $sanitizedSlug = $this->slugHelper->sanitize((string)$slug);
        $sanitizedSlug = $this->replaceSlashesFromSlug($sanitizedSlug);
        $finalSlug = $this->checkForUniqueSlug($sanitizedSlug, $uid, $pid);
        if (empty($finalSlug)) {
            throw new EmptySlugException('Unable to generate slug for DCE content element (tt_content) with uid ' . $uid . '. ' . 'Used expression: ' . $expression);
        }

        return new SlugValue($finalSlug, $finalSlug === $sanitizedSlug);
    }

    protected function replaceSlashesFromSlug(string $slug): string
    {
        $replaced = preg_replace('/[^a-z0-9\-]/i', '-', $slug);

        return preg_replace('/\-{2,}/', '-', $replaced);
    }

    protected function checkForUniqueSlug(string $slug, int $uid, int $pid): string
    {
        if (empty($slug)) {
            return $slug;
        }
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
        /** @var Statement $statement */
        $statement = $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'tx_dce_slug',
                    $queryBuilder->createNamedParameter($slug, \PDO::PARAM_STR)
                )
            )
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)
                )
            )
            ->andWhere(
                $queryBuilder->expr()->neq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute();
        if ($statement->rowCount() > 0) {
            $slug .= '-' . $uid;
        }

        return $slug;
    }
}
