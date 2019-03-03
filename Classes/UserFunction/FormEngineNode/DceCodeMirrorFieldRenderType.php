<?php
namespace T3\Dce\UserFunction\FormEngineNode;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Components\TemplateRenderer\StandaloneViewFactory;
use T3\Dce\Utility\File;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Codemirror text area field
 */
class DceCodeMirrorFieldRenderType implements \TYPO3\CMS\Backend\Form\NodeInterface
{
    /**
     * Global options from NodeFactory
     *
     * @var array
     */
    protected $data;

    /**
     * Main render method
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render()
    {
        return [
            'html' => $this->getCodemirrorFieldHtml($this->data),
            'additionalInlineLanguageLabelFiles' => [],
            'stylesheetFiles' => [
                'EXT:dce/Resources/Public/JavaScript/Contrib/codemirror/lib/codemirror.css',
                'EXT:dce/Resources/Public/Css/custom_codemirror.css'
            ],
            'requireJsModules' => [
                'TYPO3/CMS/Dce/DceCodemirror',
            ],
        ];
    }

    /**
     * All nodes get an instance of the NodeFactory and the main data array
     *
     * @param NodeFactory $nodeFactory
     * @param array $data
     */
    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        $this->data = $data;
    }

    /**
     * Uses a Fluid template to render the HTML code required for the Codemirror field and helpful dropdown.
     *
     * @param array $data
     * @return string
     */
    public function getCodemirrorFieldHtml(array $data) : string
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
        $fluidTemplate->assign('uniqueIdentifier', uniqid());
        $fluidTemplate->assign('parameters', $data['parameterArray']['fieldConf']['config']['parameters']);

        if ($data['parameterArray']['fieldConf']['config']['parameters']['mode'] === 'htmlmixed') {
            if (!(bool) $data['parameterArray']['fieldConf']['config']['parameters']['doNotShowFields']) {
                $fluidTemplate->assign('availableFields', $this->getAvailableFields());
            }
            $fluidTemplate->assign(
                'showFields',
                !(bool) $data['parameterArray']['fieldConf']['config']['parameters']['doNotShowFields']
            );
            $fluidTemplate->assign('famousViewHelpers', $this->getFamousViewHelpers());
            $fluidTemplate->assign('dceViewHelpers', $this->getDceViewHelpers());
        } else {
            $fluidTemplate->assign('availableTemplates', $this->getAvailableTemplates());
        }

        return $fluidTemplate->render();
    }

    /**
     * Get fields which can be used as variables
     *
     * @return array
     */
    protected function getAvailableFields() : array
    {
        $fields = [];
        $rowFields = $this->data['databaseRow']['fields'];
        if (!empty($rowFields)) {
            $db = \T3\Dce\Utility\DatabaseUtility::getDatabaseConnection();
            $rows = $db->exec_SELECTgetRows(
                '*',
                'tx_dce_domain_model_dcefield',
                'hidden=0 AND deleted=0 AND pid=0 AND (type=0 OR type=2) AND uid IN (' . $rowFields . ')',
                '',
                'sorting asc'
            );

            if (\is_array($rows)) {
                foreach ($rows as $row) {
                    if ($row['type'] === '2') {
                        $sectionFields = $db->exec_SELECTgetRows(
                            '*',
                            'tx_dce_domain_model_dcefield',
                            'deleted=0 AND parent_field=' . $row['uid'],
                            '',
                            'sorting asc'
                        );
                        $row['hasSectionFields'] = true;
                        $row['sectionFields'] = $sectionFields;
                    }
                    $fields[] = $row;
                }
            }
        }
        return $fields;
    }

    /**
     * @return array
     */
    protected function getAvailableTemplates() : array
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

    /**
     * @return array
     */
    protected function getFamousViewHelpers() : array
    {
        return $this->getViewhelpers(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/FamousViewHelpers/'
        );
    }

    /**
     * @return array
     */
    protected function getDceViewHelpers() : array
    {
        return $this->getViewhelpers(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/DceViewHelpers/'
        );
    }
    /**
     * @param string $path
     * @return array
     */
    protected function getViewhelpers(string $path) : array
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
