<?php
namespace ArminVieweg\Dce\Updates;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Migrates old namespaces in fluid templates
 *
 * @package ArminVieweg\Dce
 */
class MigrateOldNamespacesInFluidTemplateUpdate extends AbstractUpdate
{
    /** Old DCE namespace (before 1.0) */
    const NAMESPACE_OLD = '{namespace dce=Tx_Dce_ViewHelpers}';
    /** New DCE namespace (since 1.0) */
    const NAMESPACE_NEW = '{namespace dce=ArminVieweg\Dce\ViewHelpers}';

    /**
     * @var string
     */
    protected $title = 'EXT:dce Migrate old namespaces in fluid templates';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        $dceRows = $this->getDatabaseConnection()->exec_SELECTgetRows('*', 'tx_dce_domain_model_dce', 'deleted=0');
        $updateTemplates = 0;
        foreach ($dceRows as $dceRow) {
            // Frontend Template
            if ($dceRow['template_type'] === 'file') {
                $updateTemplates += (int) $this->doesFileTemplateRequiresUpdate($dceRow, 'template_file');
            } else {
                $updateTemplates += (int) $this->doesInlineTemplateRequiresUpdate($dceRow, 'template_content');
            }

            // Backend Templates
            if ($dceRow['preview_template_type'] === 'file') {
                $updateTemplates += (int) $this->doesFileTemplateRequiresUpdate(
                    $dceRow,
                    'header_preview_template_file'
                );
                $updateTemplates += (int) $this->doesFileTemplateRequiresUpdate(
                    $dceRow,
                    'bodytext_preview_template_file'
                );
            } else {
                $updateTemplates += (int) $this->doesInlineTemplateRequiresUpdate($dceRow, 'header_preview');
                $updateTemplates += (int) $this->doesInlineTemplateRequiresUpdate($dceRow, 'bodytext_preview');
            }

            // Detail Template
            if ($dceRow['detailpage_template_type'] === 'file') {
                $updateTemplates += (int) $this->doesFileTemplateRequiresUpdate($dceRow, 'detailpage_template_file');
            } else {
                $updateTemplates += (int) $this->doesInlineTemplateRequiresUpdate($dceRow, 'detailpage_template');
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
    protected function doesInlineTemplateRequiresUpdate(array $dceRow, $column)
    {
        return $this->templateNeedUpdate($dceRow[$column]);
    }

    /**
     * Checks if given file template requires update
     *
     * @param array $dceRow
     * @param string $column
     * @return bool
     */
    protected function doesFileTemplateRequiresUpdate(array $dceRow, $column)
    {
        $file = \ArminVieweg\Dce\Utility\File::getFilePath($dceRow[$column]);
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
    protected function templateNeedUpdate($templateContent)
    {
        return strpos($templateContent, self::NAMESPACE_OLD) !== false ||
                strpos($templateContent, 'dce:format.raw') !== false ||
                strpos($templateContent, 'dce:image') !== false  ||
                strpos($templateContent, 'dce:uri.image') !== false ;
    }


    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries Queries done in this update
     * @param mixed &$customMessages Custom messages
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        $dceRows = $this->getDatabaseConnection()->exec_SELECTgetRows('*', 'tx_dce_domain_model_dce', 'deleted=0');
        foreach ($dceRows as $dceRow) {
            // Frontend Template
            if ($dceRow['template_type'] === 'file') {
                $this->updateFileTemplate($dceRow, 'template_file');
            } else {
                $this->updateInlineTemplate($dceRow, 'template_content');
            }

            // Backend Templates
            if ($dceRow['preview_template_type'] === 'file') {
                $this->updateFileTemplate($dceRow, 'header_preview_template_file');
                $this->updateFileTemplate($dceRow, 'bodytext_preview_template_file');
            } else {
                $this->updateInlineTemplate($dceRow, 'header_preview');
                $this->updateInlineTemplate($dceRow, 'bodytext_preview');
            }

            // Detail Template
            if ($dceRow['detailpage_template_type'] === 'file') {
                $this->updateFileTemplate($dceRow, 'detailpage_template_file');
            } else {
                $this->updateInlineTemplate($dceRow, 'detailpage_template');
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
    protected function updateInlineTemplate(array $dceRow, $column)
    {
        $templateContent = $dceRow[$column];
        if ($this->templateNeedUpdate($templateContent)) {
            $updatedTemplateContent = $this->performTemplateUpdates($templateContent);

            return (bool) $this->getDatabaseConnection()->exec_UPDATEquery(
                'tx_dce_domain_model_dce',
                'uid = ' . (int) $dceRow['uid'],
                [
                    $column => $updatedTemplateContent
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
    protected function updateFileTemplate(array $dceRow, $column)
    {
        $file = \ArminVieweg\Dce\Utility\File::getFilePath($dceRow[$column]);
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
    protected function performTemplateUpdates($templateContent)
    {
        $content = str_replace(self::NAMESPACE_OLD, self::NAMESPACE_NEW, $templateContent);
        $content = str_replace('dce:format.raw', 'f:format.raw', $content);
        $content = str_replace('dce:image', 'f:image', $content);
        $content = str_replace('dce:uri.image', 'f:uri.image', $content);
        return $content;
    }
}
