<?php
namespace ArminVieweg\Dce\UserFunction\UserFields;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\File;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Codemirror text area field
 *
 * @package ArminVieweg\Dce
 */
class CodemirrorField
{
    /**
     * @var array Field parameters
     */
    protected $parameter = [];

    /**
     * @param $parameter
     * @return string
     */
    public function getCodemirrorField($parameter)
    {
        /** @var $extConfiguration array */
        $extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);

        $this->parameter = $parameter;

        /** @var $fluidTemplate \ArminVieweg\Dce\Utility\FluidTemplate */
        $fluidTemplate = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\FluidTemplate');

        $fluidTemplate->setLayoutRootPaths(
            [File::get('EXT:dce/Resources/Private/Layouts/')]
        );
        $fluidTemplate->setPartialRootPaths(
            [File::get('EXT:dce/Resources/Private/Partials/')]
        );
        $fluidTemplate->setTemplatePathAndFilename(File::get(
            'EXT:dce/Resources/Private/Templates/DceUserFields/Codemirror.html'
        ));

        $fluidTemplate->assign('name', $this->parameter['itemFormElName']);
        $fluidTemplate->assign('value', $this->parameter['itemFormElValue']);
        $fluidTemplate->assign('onChangeFunc', htmlspecialchars(implode('', $this->parameter['fieldChangeFunc'])));
        $fluidTemplate->assign('onFocus', $this->parameter['onFocus']);

        $fluidTemplate->assign('uniqueIdentifier', uniqid());
        $fluidTemplate->assign('parameters', $this->parameter['fieldConf']['config']['parameters']);
        $fluidTemplate->assign('disableCodemirror', $extConfiguration['disableCodemirror']);

        if ($parameter['fieldConf']['config']['parameters']['mode'] === 'htmlmixed') {
            if (!(bool) $parameter['fieldConf']['config']['parameters']['doNotShowFields']) {
                $fluidTemplate->assign('availableFields', $this->getAvailableFields());
            }
            $fluidTemplate->assign(
                'showFields',
                !(bool) $parameter['fieldConf']['config']['parameters']['doNotShowFields']
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
    protected function getAvailableFields()
    {
        $fields = [];
        $rowFields = $this->parameter['row']['fields'];
        if (!empty($rowFields)) {
            $db = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();
            $rows = $db->exec_SELECTgetRows(
                '*',
                'tx_dce_domain_model_dcefield',
                'hidden=0 AND deleted=0 AND pid=0 AND (type=0 OR type=2) AND uid IN (' . $rowFields . ')',
                '',
                'variable asc'
            );

            if (is_array($rows)) {
                foreach ($rows as $row) {
                    if ($row['type'] === '2') {
                        $res2 = $db->sql_query('
							SELECT title, variable
							FROM tx_dce_domain_model_dcefield
							WHERE deleted=0 AND parent_field=' . $row['uid'] . '
							ORDER BY sorting asc
						');

                        $sectionFields = [];
                        while (($row2 = $db->sql_fetch_assoc($res2))) {
                            $sectionFields[] = $row2;
                        }
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
    protected function getAvailableTemplates()
    {
        $path = ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/ConfigurationTemplates/';
        $templates = GeneralUtility::get_dirs($path);
        $templates = array_flip($templates);

        foreach (array_keys($templates) as $key) {
            $files = [];
            foreach (GeneralUtility::getFilesInDir($path . $key) as $file) {
                $filename = preg_replace('/(.*)\.xml/i', '$1', $file);
                if ($this->checkSnippetNameForVersionConstraintAndCurrentVersion($filename)) {
                    $files[$filename] = file_get_contents($path . $key . '/' . $file);
                }
            }
            $keyNoNumber = preg_replace('/.*? (.*)/i', '$1', $key);

            unset($templates[$key]);
            $templates[$keyNoNumber] = $files;
        }
        return $templates;
    }

    /**
     * @return array
     */
    protected function getFamousViewHelpers()
    {
        return $this->getViewhelpers(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/FamousViewHelpers/'
        );
    }

    /**
     * @return array
     */
    protected function getDceViewHelpers()
    {
        return $this->getViewhelpers(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/DceViewHelpers/'
        );
    }

    /**
     * @param string $path
     * @return array
     */
    protected function getViewhelpers($path)
    {
        $files = GeneralUtility::getFilesInDir($path);

        $viewHelpers = [];
        foreach ($files as $file) {
            $name = preg_replace('/(.*)\.html/i', '$1', $file);
            if ($this->checkSnippetNameForVersionConstraintAndCurrentVersion($name)) {
                $value = file_get_contents($path . $file);
                $viewHelpers[$name] = $value;
            }
        }
        ksort($viewHelpers);
        return $viewHelpers;
    }

    /**
     * Checks if given snippet name contains a version number.
     * If not this method will return true.
     *
     * If it contains a version number (e.g. "Cool Snippet (7.6)") it is checking this version number
     * against GeneralUtility::compat_version and return its return value.
     *
     * So if version number in snippet name is 8.7 but the current TYPO3 version is 7.6, the method
     * will return false. In case it's TYPO3 8.7 it would return true.
     *
     * @param string $snippetName Name of snippet which may contain TYPO3 version number (in braces)
     * @return bool True if no version number in snippet name or given version number fits current TYPO3 version
     */
    protected function checkSnippetNameForVersionConstraintAndCurrentVersion($snippetName)
    {
        preg_match('/\((\d\.\d)\)/i', $snippetName, $matches);
        return empty($matches) || !empty($matches) && TYPO3_branch === $matches[1];
    }
}
