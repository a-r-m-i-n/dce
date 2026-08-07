<?php

declare(strict_types = 1);

namespace T3\Dce\EventListener;

use T3\Dce\Components\ContentElementGenerator\Generator;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Cache\Event\CacheFlushEvent;

#[AsEventListener(identifier: 'ext-dce/cache-flush-event-listener')]
final readonly class CacheFlushEventListener
{
    public function __construct(private Generator $generator)
    {
    }

    public function __invoke(CacheFlushEvent $event): void
    {
        if ($event->hasGroup('system')) {
            $this->generator->rebuild();
        }
    }
}
