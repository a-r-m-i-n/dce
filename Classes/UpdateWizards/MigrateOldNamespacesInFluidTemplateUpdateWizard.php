<?php

declare(strict_types = 1);

namespace T3\Dce\UpdateWizards;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\File;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migrates old namespaces in fluid templates.
 */
class MigrateOldNamespacesInFluidTemplateUpdateWizard implements UpgradeWizardInterface
{
    /** @var string */
    protected $description = '';
    /** @var string */
    protected $namespaceOld = '{namespace dce=Tx_Dce_ViewHelpers}';
    /** @var string */
    protected $namespaceOld2 = '{namespace dce=ArminVieweg\Dce\ViewHelpers}';
    /** @var string */
    protected $namespaceOld3 = '{namespace dce=T3\Dce\ViewHelpers}';
    /** @var string */
    protected $namespaceNew = '';

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
        return (bool)$this->update();
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
            if ('file' === $dceRow['template_type']) {
                $updateTemplates += (int)$this->doesFileTemplateRequiresUpdate($dceRow, 'template_file');
            } else {
                $updateTemplates += (int)$this->doesInlineTemplateRequiresUpdate($dceRow, 'template_content');
            }

            // Backend Templates
            if ('file' === $dceRow['backend_template_type']) {
                $updateTemplates += (int)$this->doesFileTemplateRequiresUpdate($dceRow, 'backend_template_file');
            } else {
                $updateTemplates += (int)$this->doesInlineTemplateRequiresUpdate($dceRow, 'backend_template_content');
            }

            // Detail Template
            if ('file' === $dceRow['detailpage_template_type']) {
                $updateTemplates += (int)$this->doesFileTemplateRequiresUpdate($dceRow, 'detailpage_template_file');
            } else {
                $updateTemplates += (int)$this->doesInlineTemplateRequiresUpdate($dceRow, 'detailpage_template');
            }

            if ($dceRow['enable_container']) {
                if ('file' === $dceRow['container_template_type']) {
                    $updateTemplates += (int)$this->doesFileTemplateRequiresUpdate(
                        $dceRow,
                        'container_template_file'
                    );
                } else {
                    $updateTemplates += (int)$this->doesInlineTemplateRequiresUpdate(
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

    public function update(): ?bool
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dce');
        $dceRows = $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dce')
            ->execute()
            ->fetchAll();

        foreach ($dceRows as $dceRow) {
            // Frontend Template
            if ('file' === $dceRow['template_type']) {
                $this->updateFileTemplate($dceRow, 'template_file');
            } else {
                $this->updateInlineTemplate($dceRow, 'template_content');
            }

            // Backend Templates
            if ('file' === $dceRow['backend_template_type']) {
                $this->updateFileTemplate($dceRow, 'backend_template_file');
            } else {
                $this->updateInlineTemplate($dceRow, 'backend_template_content');
            }

            // Detail Template
            if ('file' === $dceRow['detailpage_template_type']) {
                $this->updateFileTemplate($dceRow, 'detailpage_template_file');
            } else {
                $this->updateInlineTemplate($dceRow, 'detailpage_template');
            }

            // Container Template
            if ($dceRow['enable_container']) {
                if ('file' === $dceRow['container_template_type']) {
                    $this->updateFileTemplate($dceRow, 'container_template_file');
                } else {
                    $this->updateInlineTemplate($dceRow, 'container_template');
                }
            }
        }

        return true;
    }

    /**
     * Checks if given inline template requires update.
     */
    protected function doesInlineTemplateRequiresUpdate(array $dceRow, string $column): bool
    {
        return $this->templateNeedUpdate($dceRow[$column] ?? '');
    }

    /**
     * Checks if given file template requires update.
     */
    protected function doesFileTemplateRequiresUpdate(array $dceRow, string $column): bool
    {
        $file = File::get($dceRow[$column]);
        if (empty($file)) {
            return false;
        }

        return $this->templateNeedUpdate(file_get_contents($file));
    }

    /**
     * Checks if given code needs an update.
     */
    protected function templateNeedUpdate(string $templateContent): bool
    {
        return false !== strpos($templateContent, $this->namespaceOld) ||
            false !== strpos($templateContent, $this->namespaceOld2) ||
            false !== strpos($templateContent, $this->namespaceOld3) ||
            false !== strpos($templateContent, 'dce:format.raw') ||
            false !== strpos($templateContent, 'dce:image') ||
            false !== strpos($templateContent, 'dce:uri.image');
    }

    /**
     * Updates inline templates in given DCE row.
     *
     * @return bool|null returns true on success, false on error and null if no update has been performed
     */
    protected function updateInlineTemplate(array $dceRow, string $column): ?bool
    {
        $templateContent = $dceRow[$column] ?? '';
        if ($this->templateNeedUpdate($templateContent)) {
            $updatedTemplateContent = $this->performTemplateUpdates($templateContent);

            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dce');

            return (bool)$connection->update(
                'tx_dce_domain_model_dce',
                [
                    $column => $updatedTemplateContent,
                ],
                [
                    'uid' => (int)$dceRow['uid'],
                ]
            );
        }

        return null;
    }

    /**
     * Updates file based templates in given DCE row.
     *
     * @return bool|null returns true on success, false on error and null if no update has been performed
     */
    protected function updateFileTemplate(array $dceRow, string $column): ?bool
    {
        $file = File::get($dceRow[$column]);
        if (!is_writable($file)) {
            return false;
        }

        $templateContent = file_get_contents($file);
        if ($this->templateNeedUpdate($templateContent)) {
            $updatedTemplateContent = $this->performTemplateUpdates($templateContent);
            if (!file_exists($file)) {
                $file = Environment::getPublicPath() . '/' . $file;
            }

            return (bool)file_put_contents($file, $updatedTemplateContent);
        }

        return null;
    }

    /**
     * Performs updates to given DCE template code.
     */
    protected function performTemplateUpdates(string $templateContent): string
    {
        $content = str_replace(
            [$this->namespaceOld, $this->namespaceOld2, $this->namespaceOld3],
            [$this->namespaceNew, $this->namespaceNew, $this->namespaceNew],
            $templateContent
        );
        $content = str_replace('dce:format.raw', 'f:format.raw', $content);
        $content = str_replace('dce:image', 'f:image', $content);
        $content = str_replace('dce:uri.image', 'f:uri.image', $content);

        return $content;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
