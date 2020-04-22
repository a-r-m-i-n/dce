<?php declare(strict_types=1);
namespace T3\Dce\UpdateWizards;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\UpdateWizards\Traits\MigrateOldNamespacesInFluidTemplateTrait;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migrates old namespaces in fluid templates
 */
class MigrateOldNamespacesInFluidTemplateUpdateWizard implements UpgradeWizardInterface
{
    use MigrateOldNamespacesInFluidTemplateTrait;

    protected $description = '';

    public function getIdentifier(): string
    {
        return 'dceMigrateOldNamespacesInFluidTemplateUpdate';
    }

    public function getTitle(): string
    {
        return 'EXT:dce Migrate old namespaces in fluid templates';
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function executeUpdate(): bool
    {
        return (bool) $this->update();
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dce');
        $dceRows = $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dce')
            ->execute()
            ->fetchAll();

        $updateTemplates = 0;
        foreach ($dceRows as $dceRow) {
            // Frontend Template
            if ($dceRow['template_type'] === 'file') {
                $updateTemplates += (int) $this->doesFileTemplateRequiresUpdate($dceRow, 'template_file');
            } else {
                $updateTemplates += (int) $this->doesInlineTemplateRequiresUpdate($dceRow, 'template_content');
            }

            // Backend Templates
            if ($dceRow['backend_template_type'] === 'file') {
                $updateTemplates += (int) $this->doesFileTemplateRequiresUpdate($dceRow, 'backend_template_file');
            } else {
                $updateTemplates += (int) $this->doesInlineTemplateRequiresUpdate($dceRow, 'backend_template_content');
            }

            // Detail Template
            if ($dceRow['detailpage_template_type'] === 'file') {
                $updateTemplates += (int) $this->doesFileTemplateRequiresUpdate($dceRow, 'detailpage_template_file');
            } else {
                $updateTemplates += (int) $this->doesInlineTemplateRequiresUpdate($dceRow, 'detailpage_template');
            }

            if ($dceRow['enable_container']) {
                if ($dceRow['container_template_type'] === 'file') {
                    $updateTemplates += (int) $this->doesFileTemplateRequiresUpdate(
                        $dceRow,
                        'container_template_file'
                    );
                } else {
                    $updateTemplates += (int) $this->doesInlineTemplateRequiresUpdate(
                        $dceRow,
                        'container_template'
                    );
                }
            }
        }

        if ($updateTemplates > 0) {
            $this->description = 'You have ' . $updateTemplates . ' DCE template(s) with old namespace. ' .
                'They need to get updated.';
            return true;
        }
        return false;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
