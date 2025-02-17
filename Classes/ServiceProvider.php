<?php

declare(strict_types = 1);

namespace T3\Dce;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Package\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider
{
    protected static function getPackagePath(): string
    {
        return __DIR__ . '/../';
    }

    protected static function getPackageName(): string
    {
        return 't3/dce';
    }

    public function getFactories(): array
    {
        return [
            ConnectionPool::class => [static::class, 'getConnectionPool'],
        ];
    }

    public static function getConnectionPool(\Psr\Container\ContainerInterface $container): ConnectionPool
    {
        return self::new($container, ConnectionPool::class);
    }
}
