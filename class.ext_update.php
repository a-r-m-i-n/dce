<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility as ExtMgm;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Update class for the extension manager.
 *
 * @package ArminVieweg\Dce
 */
class ext_update
{
    /** Old DCE namespace (before 1.0) */
    const NAMESPACE_OLD = '{namespace dce=Tx_Dce_ViewHelpers}';
    /** New DCE namespace (since 1.0) */
    const NAMESPACE_NEW = '{namespace dce=ArminVieweg\Dce\ViewHelpers}';

    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * @var array
     */
    protected $dceStatus = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->databaseConnection = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();
    }

    /**
     * Main update function called by the extension manager.
     *
     * @return string
     */
    public function main()
    {
        $this->processUpdates();
        return $this->generateOutput();
    }

    /**
     * Called by the extension manager to determine if the update menu entry
     * should by showed.
     *
     * @return bool
     */
    public function access()
    {
        return true;
    }

    /**
     * The actual update function. Add your update task in here.
     *
     * @return void
     */
    protected function processUpdates()
    {
        $dceRows = $this->databaseConnection->exec_SELECTgetRows('*', 'tx_dce_domain_model_dce', 'deleted = 0');
        foreach ($dceRows as $dceRow) {
            $this->dceStatus[$dceRow['uid']] = array();
            $this->dceStatus[$dceRow['uid']]['row'] = $dceRow;
            $this->dceStatus[$dceRow['uid']]['columns'] = array();

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
    }

    /**
     * Updates inline templates in given DCE row
     *
     * @param array $dceRow
     * @param $column
     */
    protected function updateInlineTemplate(array $dceRow, $column)
    {
        $templateContent = $dceRow[$column];
        if ($this->templateNeedUpdate($templateContent)) {
            $updatedTemplateContent = $this->performTemplateUpdates($templateContent);

            $updateStatus = $this->databaseConnection->exec_UPDATEquery(
                'tx_dce_domain_model_dce',
                'uid = ' . (int) $dceRow['uid'],
                array(
                    $column => $updatedTemplateContent
                )
            );

            if (!$updateStatus) {
                $this->dceStatus[$dceRow['uid']]['columns'][$column]['status'] = 'error';
                $this->dceStatus[$dceRow['uid']]['columns'][$column]['error'] = 'Update in database failed (sql).';
            } else {
                $this->dceStatus[$dceRow['uid']]['columns'][$column]['status'] = 'updated';
            }
        } else {
            $this->dceStatus[$dceRow['uid']]['columns'][$column]['status'] = 'ok';
        }
        $this->dceStatus[$dceRow['uid']]['columns'][$column]['type'] = 'inline';
    }

    /**
     * Updates file based templates in given DCE row
     *
     * @param array $dceRow
     * @param $column
     */
    protected function updateFileTemplate(array $dceRow, $column)
    {
        $file = \ArminVieweg\Dce\Utility\File::getFilePath($dceRow[$column]);
        $this->dceStatus[$dceRow['uid']]['columns'][$column]['type'] = 'file';
        if (!is_writeable($file)) {
            $this->dceStatus[$dceRow['uid']]['columns'][$column]['status'] = 'error';
            $this->dceStatus[$dceRow['uid']]['columns'][$column]['error'] = 'Template ("' . $file . '") not writeable!';
            return;
        }

        $templateContent = file_get_contents($file);
        if ($this->templateNeedUpdate($templateContent)) {
            $updatedTemplateContent = $this->performTemplateUpdates($templateContent);
            $updateStatus = file_put_contents(PATH_site . $file, $updatedTemplateContent);

            if (!$updateStatus) {
                $this->dceStatus[$dceRow['uid']]['columns'][$column]['status'] = 'error';
                $this->dceStatus[$dceRow['uid']]['columns'][$column]['status'] = 'Unknown error.';
            } else {
                $this->dceStatus[$dceRow['uid']]['columns'][$column]['status'] = 'updated';
            }
        } else {
            $this->dceStatus[$dceRow['uid']]['columns'][$column]['status'] = 'ok';
        }
    }

    /**
     * Generates output by using flash messages
     *
     * @return string
     */
    protected function generateOutput()
    {
        $view = new \ArminVieweg\Dce\Utility\FluidTemplate();
        $view->setTemplatePathAndFilename(
            ExtMgm::extPath('dce') . 'Resources/Private/Templates/DceModule/ExtensionManagerUpdate.html'
        );

        $view->assign('includeBootstrap', !\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.4'));
        $view->assign('status', $this->dceStatus);
        return $view->render();
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

    /**
     * Checks if given code needs an update
     *
     * @param string $templateContent
     * @return bool
     */
    protected function templateNeedUpdate($templateContent) {
        return strpos($templateContent, self::NAMESPACE_OLD) ||
                strpos($templateContent, 'dce:format.raw') ||
                strpos($templateContent, 'dce:image') ||
                strpos($templateContent, 'dce:uri.image');
    }
}
