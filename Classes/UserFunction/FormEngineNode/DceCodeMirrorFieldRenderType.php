<?php

namespace T3\Dce\UserFunction\FormEngineNode;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Components\TemplateRenderer\StandaloneViewFactory;
use T3\Dce\Event\ModifyConfigurationTemplateCodeSnippetsEvent;
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Note: Currently (since DCE 3.0) the CodeMirror editor is not included in DCE extension anymore
 *       It is planned to reimplement CodeMirror integration (ES6) in future DCE versions (based on EXT:t3editor).
 *
 * @see \T3\Dce\EventListener\AfterFormEnginePageInitializedEventListener::loadDceCodeEditor
 */
class DceCodeMirrorFieldRenderType extends AbstractFormElement
{
    private string $uniqueIdentifier;

    public function __construct(private readonly EventDispatcher $eventDispatcher)
    {
        $this->uniqueIdentifier = str_replace('.', '', uniqid('', true));
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function render(): array
    {
        $resultArray = $this->initializeResultArray();
        $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create('@t3/dce/code-editor');

        $renderedLabel = $this->renderLabel('dce-code-editor-' . $this->uniqueIdentifier);
        $resultArray['labelHasBeenHandled'] = true;
        $resultArray['html'] = $renderedLabel . $this->getCodeEditorFieldHtml($this->data);

        return $resultArray;
    }

    /**
     * Uses a Fluid template to render the HTML code required for the Codemirror field and helpful dropdown.
     */
    public function getCodeEditorFieldHtml(array $data): string
    {
        /** @var StandaloneViewFactory $viewFactory */
        $viewFactory = GeneralUtility::makeInstance(StandaloneViewFactory::class);
        /** @var StandaloneView $fluidTemplate */
        $fluidTemplate = $viewFactory->makeNewDceView();
        $fluidTemplate->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:dce/Resources/Private/Templates/DceUserFields/Codemirror.html'
        ));

        $fluidTemplate->assign('name', $data['parameterArray']['itemFormElName']);
        $fluidTemplate->assign('value', $data['parameterArray']['itemFormElValue']);
        $fluidTemplate->assign('uniqueIdentifier', $this->uniqueIdentifier);
        $fluidTemplate->assign('parameters', $data['parameterArray']['fieldConf']['config']['parameters']);

        if ('htmlmixed' === $data['parameterArray']['fieldConf']['config']['parameters']['mode']) {
            if (!isset($data['parameterArray']['fieldConf']['config']['parameters']['doNotShowFields'])) {
                $fluidTemplate->assign('availableFields', $this->getAvailableFields());
            }
            $fluidTemplate->assign(
                'showFields',
                !isset($data['parameterArray']['fieldConf']['config']['parameters']['doNotShowFields'])
            );
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
