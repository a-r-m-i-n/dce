<?php

namespace T3\Dce\UserFunction\FormEngineNode;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Components\TemplateRenderer\StandaloneViewFactory;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\File;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Codemirror text area field.
 */
class DceCodeMirrorFieldRenderType extends AbstractFormElement
{
    /**
     * @var array
     */
    protected $resultArray;

    /**
     * @var string
     */
    protected $uniqueIdentifier;

    /**
     * Main render method.
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render()
    {
        $mode = $this->data['parameterArray']['fieldConf']['config']['parameters']['mode'];
        $this->resultArray = $this->initializeResultArray();
        $this->resultArray['stylesheetFiles'][] =
            'EXT:dce/Resources/Public/JavaScript/Contrib/codemirror/lib/codemirror.css';
        $this->resultArray['stylesheetFiles'][] =
            'EXT:dce/Resources/Public/Css/custom_codemirror.css';
        $this->resultArray['requireJsModules'][] = [
            'TYPO3/CMS/Dce/DceCodemirror' => 'function(DceCodemirror, $) { DceCodemirror.initCodeMirrorEditor("#codemirror_' .
                $this->uniqueIdentifier . '", "' . $mode . '"); }',
        ];
        $this->resultArray['html'] = $this->getCodemirrorFieldHtml($this->data);

        return $this->resultArray;
    }

    /**
     * All nodes get an instance of the NodeFactory and the main data array.
     */
    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        parent::__construct($nodeFactory, $data);
        $this->data = $data;
        $this->uniqueIdentifier = str_replace('.', '', uniqid('', true));
    }

    /**
     * Uses a Fluid template to render the HTML code required for the Codemirror field and helpful dropdown.
     */
    public function getCodemirrorFieldHtml(array $data): string
    {
        /** @var StandaloneViewFactory $viewFactory */
        $viewFactory = GeneralUtility::makeInstance(StandaloneViewFactory::class);
        /** @var StandaloneView $fluidTemplate */
        $fluidTemplate = $viewFactory->makeNewDceView();
        $fluidTemplate->setTemplatePathAndFilename(File::get(
            'EXT:dce/Resources/Private/Templates/DceUserFields/Codemirror.html'
        ));

        $fluidTemplate->assign('name', $data['parameterArray']['itemFormElName']);
        $fluidTemplate->assign('value', $data['parameterArray']['itemFormElValue']);
        $fluidTemplate->assign(
            'onChangeFunc',
            htmlspecialchars(implode('', $data['parameterArray']['fieldChangeFunc']))
        );
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

    /**
     * Get fields which can be used as variables.
     */
    protected function getAvailableFields(): array
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
                        $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->eq(
                            'type',
                            $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                        ),
                        $queryBuilder->expr()->eq(
                            'type',
                            $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                        )
                    ),
                    $queryBuilder->expr()->in(
                        'uid',
                        $queryBuilder->createNamedParameter($rowFields, Connection::PARAM_INT_ARRAY)
                    )
                )
                ->orderBy('sorting', 'ASC')
                ->execute()
                ->fetchAll();

            if (\is_array($rows)) {
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
                                    $queryBuilder->createNamedParameter($row['uid'], \PDO::PARAM_INT)
                                )
                            )
                            ->orderBy('sorting', 'ASC')
                            ->execute()
                            ->fetchAll();
                        $row['hasSectionFields'] = true;
                        $row['sectionFields'] = $sectionFields;
                    }
                    $fields[] = $row;
                }
            }
        }

        return $fields;
    }

    protected function getAvailableTemplates(): array
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
            $templates[$keyNoNumber] = $files;
        }

        return $templates;
    }

    protected function getFamousViewHelpers(): array
    {
        return $this->getViewhelpers(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/FamousViewHelpers/'
        );
    }

    protected function getDceViewHelpers(): array
    {
        return $this->getViewhelpers(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/DceViewHelpers/'
        );
    }

    protected function getViewhelpers(string $path): array
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
