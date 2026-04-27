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
            if ($dce['wizard_enable']) {
                $dceIdentifier = $dce['identifier'];

                $iconIdentifierCode = $dce['hasCustomWizardIcon']
                    ? 'ext-dce-' . $dceIdentifier . '-customwizardicon'
                    : $dce['wizard_icon'];

                $wizardCategory = $dce['wizard_category'] ?? '';
                $flexformLabel = $dce['flexform_label'] ?? '';
                $title = addcslashes($dce['title'] ?? '', "'\"");
                $description = addcslashes($dce['wizard_description'] ?? '', "'\"");

                $pageTsConfig .= <<<tsconfig
                    mod.wizards.newContentElement.wizardItems.$wizardCategory.elements.$dceIdentifier {
                        iconIdentifier = $iconIdentifierCode
                        title = $title
                        description = $description
                        tt_content_defValues {
                            CType = $dceIdentifier
                        }
                    }
                    mod.wizards.newContentElement.wizardItems.$wizardCategory.show := addToList($dceIdentifier)
                    TCEFORM.tt_content.pi_flexform.types.$dceIdentifier.label = $flexformLabel

                    tsconfig;
            }
        }

        return $pageTsConfig;
    }
}
