<?php
namespace ArminVieweg\Dce\UserFunction\UserFields;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
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
    protected $parameter = array();

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

        $fluidTemplate->setLayoutRootPath(GeneralUtility::getFileAbsFileName('EXT:dce/Resources/Private/Layouts/'));
        $fluidTemplate->setPartialRootPath(GeneralUtility::getFileAbsFileName('EXT:dce/Resources/Private/Partials/'));
        $fluidTemplate->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
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
            $fluidTemplate->assign('availableFields', $this->getAvailableFields());
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
        $fields = array();

        $rowFields = $this->parameter['row']['fields'];
        if (!empty($rowFields)) {
            $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                '*',
                'tx_dce_domain_model_dcefield',
                'hidden=0 AND deleted=0 AND pid=0 AND (type=0 OR type=2) AND uid IN (' . $rowFields . ')',
                '',
                'variable asc'
            );

            if (is_array($rows)) {
                foreach ($rows as $row) {
                    if ($row['type'] === '2') {
                        $res2 = $GLOBALS['TYPO3_DB']->sql_query('
							SELECT title, variable

							FROM tx_dce_domain_model_dcefield
							JOIN tx_dce_dcefield_sectionfields_mm
							ON uid = uid_foreign

							WHERE deleted = 0  AND uid_local = ' . $row['uid'] . '
							ORDER BY sorting asc
						');

                        $sectionFields = array();
                        while (($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2))) {
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
            $files = array();
            foreach (GeneralUtility::getFilesInDir($path . $key) as $file) {
                $filename = preg_replace('/(.*)\.xml/i', '$1', $file);
                $files[$filename] = file_get_contents($path . $key . '/' . $file);
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

        $viewHelpers = array();
        foreach ($files as $file) {
            $name = preg_replace('/(.*)\.html/i', '$1', $file);
            $value = file_get_contents($path . $file);
            $viewHelpers[$name] = $value;
        }
        ksort($viewHelpers);
        return $viewHelpers;
    }
}
