<?php

namespace T3\Dce\UserFunction\FormEngineNode;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Components\TemplateRenderer\ViewFactory;
use T3\Dce\Event\ModifyConfigurationTemplateCodeSnippetsEvent;
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;

class DceCodeEditorSnippetWizard extends AbstractNode
{
    public function __construct(private readonly EventDispatcher $eventDispatcher, private PackageManager $packageManager)
    {
    }

    public function render(): array
    {
        $resultArray = $this->initializeResultArray();
        $options = $this->data['renderData']['fieldWizardOptions'] ?? [];
        $snippetType = $options['snippetType'] ?? '';
        if (!in_array($snippetType, ['configuration', 'fluid'], true)) {
            return $resultArray;
        }

        $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create(
            '@t3/dce/code-editor-snippet-wizard'
        );
        $resultArray['stylesheetFiles'][] = 'EXT:dce/Resources/Public/Css/DceCodeEditorSnippetWizard.css';
        $resultArray['html'] = $this->getSnippetWizardHtml($snippetType, (bool)($options['showFields'] ?? true));

        return $resultArray;
    }

    private function getSnippetWizardHtml(string $snippetType, bool $showFields): string
    {
        /** @var ViewFactory $viewFactory */
        $viewFactory = GeneralUtility::makeInstance(ViewFactory::class);
        /** @var FluidViewAdapter $fluidTemplate */
        $fluidTemplate = $viewFactory->makeNewDceView($this->data['request'] ?? null);
        $fluidTemplate->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename(
            GeneralUtility::getFileAbsFileName(
                'EXT:dce/Resources/Private/Templates/DceUserFields/CodeEditorSnippetWizard.html'
            )
        );

        $fluidTemplate->assign(
            'uniqueIdentifier',
            md5((string)($this->data['parameterArray']['itemFormElName'] ?? ''))
        );
        $fluidTemplate->assign('showTemplates', 'configuration' === $snippetType);

        if ('fluid' === $snippetType) {
            if ($showFields) {
                $fluidTemplate->assign('availableFields', $this->getAvailableFields());
            }
            $fluidTemplate->assign('showFields', $showFields);
            $fluidTemplate->assign('famousViewHelpers', $this->getFamousViewHelpers());
            $fluidTemplate->assign('dceViewHelpers', $this->getDceViewHelpers());
        } else {
            $fluidTemplate->assign('availableTemplates', $this->getAvailableTemplates());
        }

        return $fluidTemplate->render();
    }

    private function getAvailableFields(): array
    {
        $fields = [];
        $rowFields = GeneralUtility::trimExplode(',', $this->data['databaseRow']['fields']);
        if (!empty($rowFields) && !empty($rowFields[0])) {
            $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
                'tx_dce_domain_model_dcefield'
            );
            $rows = $queryBuilder
                ->select('*')
                ->from('tx_dce_domain_model_dcefield')
                ->where(
                    $queryBuilder->expr()->eq(
                        'pid',
                        $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->or(
                        $queryBuilder->expr()->eq(
                            'type',
                            $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                        ),
                        $queryBuilder->expr()->eq(
                            'type',
                            $queryBuilder->createNamedParameter(2, Connection::PARAM_INT)
                        )
                    ),
                    $queryBuilder->expr()->in(
                        'uid',
                        $queryBuilder->createNamedParameter($rowFields, Connection::PARAM_INT_ARRAY)
                    )
                )
                ->orderBy('sorting', 'ASC')
                ->executeQuery()
                ->fetchAllAssociative();

            foreach ($rows as $row) {
                if ('2' === $row['type']) {
                    $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
                        'tx_dce_domain_model_dcefield'
                    );
                    $sectionFields = $queryBuilder
                        ->select('*')
                        ->from('tx_dce_domain_model_dcefield')
                        ->where(
                            $queryBuilder->expr()->eq(
                                'parent_field',
                                $queryBuilder->createNamedParameter($row['uid'], Connection::PARAM_INT)
                            )
                        )
                        ->orderBy('sorting', 'ASC')
                        ->executeQuery()
                        ->fetchAllAssociative();
                    $row['hasSectionFields'] = true;
                    $row['sectionFields'] = $sectionFields;
                }
                $fields[] = $row;
            }
        }

        return $fields;
    }

    private function getAvailableTemplates(): array
    {
        $path = ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/ConfigurationTemplates/';
        $templates = GeneralUtility::get_dirs($path);
        $templates = array_flip($templates);

        foreach (array_keys($templates) as $key) {
            $files = [];
            foreach (GeneralUtility::getFilesInDir($path . $key) as $file) {
                $filename = preg_replace('/(.*)\.xml/i', '$1', $file);
                $files[$filename] = file_get_contents($path . $key . '/' . $file);
            }
            $keyNoNumber = preg_replace('/.*? (.*)/', '$1', $key);

            unset($templates[$key]);
            $templates['TYPE: ' . $keyNoNumber] = $files;
        }

        if ($this->packageManager->isPackageActive('vici')) {
            $templates['TYPE: inline'] = [
                'VICI table' => <<<XML
                    <config>
                        <type>inline</type>
                        <foreign_table>tx_vici_custom_NAME</foreign_table>
                        <dce_load_schema>1</dce_load_schema>
                    </config>
                    XML,
            ];
        }

        $event = new ModifyConfigurationTemplateCodeSnippetsEvent($templates);
        $this->eventDispatcher->dispatch($event);

        return $event->getTemplates();
    }

    private function getFamousViewHelpers(): array
    {
        return $this->getViewHelpers(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/FamousViewHelpers/'
        );
    }

    private function getDceViewHelpers(): array
    {
        return $this->getViewHelpers(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/DceViewHelpers/'
        );
    }

    private function getViewHelpers(string $path): array
    {
        $files = GeneralUtility::getFilesInDir($path);
        $viewHelpers = [];
        foreach ($files as $file) {
            $name = preg_replace('/(.*)\.html/i', '$1', $file);
            $value = file_get_contents($path . $file);
            $viewHelpers[$name] = $value;
        }
        ksort($viewHelpers);

        return $viewHelpers;
    }
}
