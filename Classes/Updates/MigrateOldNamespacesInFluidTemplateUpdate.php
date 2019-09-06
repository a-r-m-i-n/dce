<?php
namespace T3\Dce\Updates;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\File;

/**
 * Migrates old namespaces in fluid templates
 */
class MigrateOldNamespacesInFluidTemplateUpdate extends AbstractUpdate
{
    /** Old DCE namespace (before 1.0) */
    public const NAMESPACE_OLD = '{namespace dce=Tx_Dce_ViewHelpers}';
    /** New DCE namespace (since 1.0) */
    public const NAMESPACE_OLD2 = '{namespace dce=ArminVieweg\Dce\ViewHelpers}';
    /** New DCE namespace (since 2.0) */
    public const NAMESPACE_OLD3 = '{namespace dce=T3\Dce\ViewHelpers}';
    /** New DCE namespace (since 1.7) */
    public const NAMESPACE_NEW = '';

    /**
     * @var string
     */
    protected $title = 'EXT:dce Migrate old namespaces in fluid templates';

    /**
     * @var string
     */
    protected $identifier = 'dceMigrateOldNamespacesInFluidTemplateUpdate';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
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
            $description = 'You have <b>' . $updateTemplates . ' DCE templates</b> with old namespace. ' .
                            'They need to get updated.';
            return true;
        }
        return false;
    }

    /**
     * Checks if given inline template requires update
     *
     * @param array $dceRow
     * @param string $column
     * @return bool
     */
    protected function doesInlineTemplateRequiresUpdate(array $dceRow, string $column) : bool
    {
        return $this->templateNeedUpdate($dceRow[$column] ?? '');
    }

    /**
     * Checks if given file template requires update
     *
     * @param array $dceRow
     * @param string $column
     * @return bool
     */
    protected function doesFileTemplateRequiresUpdate(array $dceRow, string $column) : bool
    {
        $file = File::get($dceRow[$column]);
        if (empty($file)) {
            return false;
        }
        return $this->templateNeedUpdate(file_get_contents($file));
    }


    /**
     * Checks if given code needs an update
     *
     * @param string $templateContent
     * @return bool
     */
    protected function templateNeedUpdate(string $templateContent) : bool
    {
        return strpos($templateContent, self::NAMESPACE_OLD) !== false ||
                strpos($templateContent, self::NAMESPACE_OLD2) !== false ||
                strpos($templateContent, self::NAMESPACE_OLD3) !== false ||
                strpos($templateContent, 'dce:format.raw') !== false ||
                strpos($templateContent, 'dce:image') !== false  ||
                strpos($templateContent, 'dce:uri.image') !== false ;
    }


    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries Queries done in this update
     * @param string|array &$customMessages Custom messages
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dce');
        $dceRows = $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dce')
            ->execute()
            ->fetchAll();

        foreach ($dceRows as $dceRow) {
            // Frontend Template
            if ($dceRow['template_type'] === 'file') {
                $this->updateFileTemplate($dceRow, 'template_file');
            } else {
                $this->updateInlineTemplate($dceRow, 'template_content');
            }

            // Backend Templates
            if ($dceRow['backend_template_type'] === 'file') {
                $this->updateFileTemplate($dceRow, 'backend_template_file');
            } else {
                $this->updateInlineTemplate($dceRow, 'backend_template_content');
            }

            // Detail Template
            if ($dceRow['detailpage_template_type'] === 'file') {
                $this->updateFileTemplate($dceRow, 'detailpage_template_file');
            } else {
                $this->updateInlineTemplate($dceRow, 'detailpage_template');
            }

            // Container Template
            if ($dceRow['enable_container']) {
                if ($dceRow['container_template_type'] === 'file') {
                    $this->updateFileTemplate($dceRow, 'container_template_file');
                } else {
                    $this->updateInlineTemplate($dceRow, 'container_template');
                }
            }
        }
        return true;
    }

    /**
     * Updates inline templates in given DCE row
     *
     * @param array $dceRow
     * @param string $column
     * @return bool|null Returns true on success, false on error and null if no update has been performed.
     */
    protected function updateInlineTemplate(array $dceRow, string $column) : ?bool
    {
        $templateContent = $dceRow[$column] ?? '';
        if ($this->templateNeedUpdate($templateContent)) {
            $updatedTemplateContent = $this->performTemplateUpdates($templateContent);

            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dce');
            return (bool)$connection->update(
                'tx_dce_domain_model_dce',
                [
                    $column => $updatedTemplateContent
                ],
                [
                    'uid' => (int) $dceRow['uid']
                ]
            );
        }
        return null;
    }

    /**
     * Updates file based templates in given DCE row
     *
     * @param array $dceRow
     * @param string $column
     * @return bool|null Returns true on success, false on error and null if no update has been performed.
     */
    protected function updateFileTemplate(array $dceRow, string $column) : ?bool
    {
        $file = File::get($dceRow[$column]);
        if (!is_writeable($file)) {
            return false;
        }

        $templateContent = file_get_contents($file);
        if ($this->templateNeedUpdate($templateContent)) {
            $updatedTemplateContent = $this->performTemplateUpdates($templateContent);
            if (!file_exists($file)) {
                $file = PATH_site . $file;
            }
            return (bool) file_put_contents($file, $updatedTemplateContent);
        }
        return null;
    }

    /**
     * Performs updates to given DCE template code
     *
     * @param string $templateContent
     * @return string
     */
    protected function performTemplateUpdates(string $templateContent) : string
    {
        $content = str_replace(
            [self::NAMESPACE_OLD, self::NAMESPACE_OLD2, self::NAMESPACE_OLD3],
            [self::NAMESPACE_NEW, self::NAMESPACE_NEW, self::NAMESPACE_NEW],
            $templateContent
        );
        $content = str_replace('dce:format.raw', 'f:format.raw', $content);
        $content = str_replace('dce:image', 'f:image', $content);
        $content = str_replace('dce:uri.image', 'f:uri.image', $content);
        return $content;
    }
}
