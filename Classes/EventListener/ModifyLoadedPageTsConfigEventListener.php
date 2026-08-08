<?php

declare(strict_types = 1);

namespace T3\Dce\EventListener;

use T3\Dce\Components\ContentElementGenerator\InputDatabase;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\ModifyLoadedPageTsConfigEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

#[AsEventListener]
readonly class ModifyLoadedPageTsConfigEventListener
{
    public function __construct(private InputDatabase $input)
    {
    }

    public function __invoke(ModifyLoadedPageTsConfigEvent $event): void
    {
        $dceNewContentElementWizardConfig = $this->getDceWizardNewContentElementWizardPageTsConfig();
        if (!empty($dceNewContentElementWizardConfig)) {
            $event->addTsConfig($dceNewContentElementWizardConfig);
        }

        if (ExtensionManagementUtility::isLoaded('linkvalidator')) {
            $event->addTsConfig('mod.linkvalidator.searchFields.tt_content := addToList(pi_flexform)');
        }

    }

    private function getDceWizardNewContentElementWizardPageTsConfig(): string
    {
        $pageTsConfig = '';

        foreach ($this->input->getDces() as $dce) {
            if ($dce['hidden'] || $dce['deleted']) {
                continue;
            }

            $dceIdentifier = $dce['identifier'];
            $flexformLabel = $dce['flexform_label'] ?? '';
            $pageTsConfig .= "TCEFORM.tt_content.pi_flexform.types.$dceIdentifier.label = $flexformLabel\n";

            if (!$dce['wizard_enable']) {
                $pageTsConfig .= "mod.wizards.newContentElement.wizardItems.removeItems := addToList($dceIdentifier)\n";
            }
        }

        return $pageTsConfig;
    }
}
