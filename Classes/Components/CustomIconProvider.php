<?php

declare(strict_types = 1);

namespace T3\Dce\Components;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

final readonly class CustomIconProvider
{
    private const CACHE_IDENTIFIER = 'dce-custom-icons';
    private const TABLE_NAME = 'tx_dce_domain_model_dce';

    public function __construct(
        private CacheManager $cacheManager,
        private ConnectionPool $connectionPool
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getIcons(): array
    {
        $cache = $this->cacheManager->getCache('assets');
        $icons = $cache->get(self::CACHE_IDENTIFIER);
        if (\is_array($icons)) {
            return $icons;
        }

        $icons = $this->fetchIcons();
        if (null === $icons) {
            return [];
        }
        $cache->set(self::CACHE_IDENTIFIER, $icons);

        return $icons;
    }

    public function flushCache(): void
    {
        $this->cacheManager->getCache('assets')->remove(self::CACHE_IDENTIFIER);
    }

    /**
     * @return array<string, string>|null
     */
    private function fetchIcons(): ?array
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE_NAME);
        if (!$connection->createSchemaManager()->tablesExist([self::TABLE_NAME])) {
            return null;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();
        $rows = $queryBuilder
            ->select('uid', 'identifier', 'wizard_custom_icon')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'deleted',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'wizard_icon',
                    $queryBuilder->createNamedParameter('custom')
                ),
                $queryBuilder->expr()->neq(
                    'wizard_custom_icon',
                    $queryBuilder->createNamedParameter('')
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $icons = [];
        foreach ($rows as $row) {
            $dceIdentifier = !empty($row['identifier'])
                ? 'dce_' . $row['identifier']
                : 'dce_dceuid' . $row['uid'];
            $icons['ext-dce-' . $dceIdentifier . '-customwizardicon'] = $row['wizard_custom_icon'];
        }

        return $icons;
    }
}
