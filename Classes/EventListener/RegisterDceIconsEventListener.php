<?php

declare(strict_types = 1);

namespace T3\Dce\EventListener;

use T3\Dce\Components\CustomIconProvider;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;
use TYPO3\CMS\Core\Imaging\IconRegistry;

#[AsEventListener(identifier: 'ext-dce/register-dce-icons-event-listener')]
final readonly class RegisterDceIconsEventListener
{
    public function __construct(
        private CustomIconProvider $customIconProvider,
        private IconRegistry $iconRegistry
    ) {
    }

    public function __invoke(BootCompletedEvent $event): void
    {
        foreach ($this->customIconProvider->getIcons() as $identifier => $source) {
            $this->iconRegistry->registerIcon(
                $identifier,
                $this->iconRegistry->detectIconProvider($source),
                ['source' => $source]
            );
        }
    }
}
