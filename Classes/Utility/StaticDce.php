<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility for StaticDce
 *
 * @package ArminVieweg\Dce
 */
class StaticDce
{
    /**
     * @var array
     */
    static protected $extConfiguration = array();

    /**
     * @var \ArminVieweg\Dce\Utility\TypoScript
     */
    static protected $typoscriptUtility = null;


    /**
     * Constructor
     */
    public function __construct()
    {
        static::$extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);
        static::$typoscriptUtility = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\TypoScript');
    }

    /**
     * @param array $configurationArray
     * @return array
     */
    protected function addTabsAndNestFieldsInIt(array $configurationArray)
    {
        if (TYPO3_MODE === 'FE') {
            $generalTabLabel = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('generaltab', 'dce');
        } else {
            $generalTabLabel = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'LLL:EXT:dce/Resources/Private/Language/locallang.xml:generaltab',
                'dce'
            );
        }
        $tabs = array(0 => array('title' => $generalTabLabel, 'fields' => array()));
        $index = 0;
        foreach ($configurationArray['tx_dce']['static']['fields'] as $variable => $field) {
            if ($field['type'] === '1') {
                $tabs[++$index] = array(
                    'title' => $field['title'],
                    'fields' => array()
                );
                continue;
            }
            $tabs[$index]['fields'][$variable] = $field;
        }
        if (empty($tabs[0]['fields'])) {
            unset($tabs[0]);
        }
        $configurationArray['tx_dce']['static']['tabs'] = $tabs;
        return $configurationArray;
    }

    /**
     * @param string $identifier
     * @param bool $nestFieldsInTabs
     * @return array|bool FALSE in error case
     */
    public function getStaticDceData($identifier = '', $nestFieldsInTabs = false)
    {
        $path = static::$extConfiguration['filebasedDcePath'];
        if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }
        $dceFolderPath = PATH_site . $path . $identifier . DIRECTORY_SEPARATOR;

        if (is_dir($dceFolderPath) && file_exists($dceFolderPath . 'Dce.ts')) {
            $dceConfiguration = file_get_contents($dceFolderPath . 'Dce.ts');
            $configurationArray = static::$typoscriptUtility->parseTypoScriptString($dceConfiguration, true);
            if ($nestFieldsInTabs) {
                $configurationArray = $this->addTabsAndNestFieldsInIt($configurationArray);
            }

            $frontendTemplateFile = $dceFolderPath . 'Frontend.html';
            if (file_exists($frontendTemplateFile)) {
                $configurationArray['tx_dce']['static']['template_content'] = file_get_contents($frontendTemplateFile);
            }

            $backendHeaderTemplateFile = $dceFolderPath . 'BackendHeader.html';
            if (file_exists($backendHeaderTemplateFile)) {
                $configurationArray['tx_dce']['static']['header_preview'] =
                    file_get_contents($backendHeaderTemplateFile);
            }

            $backendBodytextTemplateFile = $dceFolderPath . 'BackendBodytext.html';
            if (file_exists($backendBodytextTemplateFile)) {
                $configurationArray['tx_dce']['static']['bodytext_preview'] =
                    file_get_contents($backendBodytextTemplateFile);
            }

            $backendBodytextTemplateFile = $dceFolderPath . 'Detailpage.html';
            if (file_exists($backendBodytextTemplateFile)) {
                $configurationArray['tx_dce']['static']['detailpage_template'] =
                    file_get_contents($backendBodytextTemplateFile);
            }

            $configurationArray['tx_dce']['static']['identifier'] = $identifier;
            $configurationArray['tx_dce']['static']['pid'] = '0';
            $configurationArray['tx_dce']['static']['type'] = '1';
            $configurationArray['tx_dce']['static']['template_type'] = 'inline';
            $configurationArray['tx_dce']['static']['preview_template_type'] = 'inline';
            $configurationArray['tx_dce']['static']['detailpage_template_type'] = 'inline';
            $configurationArray['tx_dce']['static']['hasCustomWizardIcon'] =
                $configurationArray['tx_dce']['static']['wizard_icon'] === 'custom';

            return $configurationArray['tx_dce']['static'];
        }
        return false;
    }

    /**
     * @param string $identifier
     * @return \ArminVieweg\Dce\Domain\Model\Dce
     */
    public function getStaticDceModel($identifier)
    {
        $data = $this->getStaticDceData($identifier);

        /** @var \ArminVieweg\Dce\Domain\Model\Dce $dce */
        $dce = GeneralUtility::makeInstance('ArminVieweg\Dce\Domain\Model\Dce');

        foreach ($data as $attribute => $value) {
            if ($attribute === 'fields') {
                continue;
            }
            $this->setAttribute($dce, $attribute, $value);
        }

        foreach ($data['fields'] as $fieldData) {
            /** @var \ArminVieweg\Dce\Domain\Model\DceField $dceField */
            $dceField = GeneralUtility::makeInstance('ArminVieweg\Dce\Domain\Model\DceField');
            foreach ($fieldData as $attribute => $value) {
                if ($attribute === 'type' && $value === '2') {
                    // Section field
                    /** @var \ArminVieweg\Dce\Domain\Model\DceField $sectionField */
                    $sectionField = GeneralUtility::makeInstance('ArminVieweg\Dce\Domain\Model\DceField');
                    foreach ($fieldData['section_fields'] as $sectionFieldData) {
                        foreach ($sectionFieldData as $attribute2 => $value2) {
                            $this->setAttribute($sectionField, $attribute2, $value2);
                        }
                    }
                    $dceField->addSectionField($sectionField);
                }

                $this->setAttribute($dceField, $attribute, $value);
            }
            $dce->addField($dceField);
        }
        return $dce;
    }

    protected function setAttribute(
        \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $domainObject,
        $attribute,
        $value
    ) {
        if ($attribute !== 'fields' && $attribute !== 'section_fields') {
            $setter = 'set' . GeneralUtility::underscoredToUpperCamelCase($attribute);
            if (method_exists($domainObject, $setter)) {
                $domainObject->$setter($value);
            }
        }
    }

    /**
     * Returns static DCEs
     *
     * @param $nestFieldsInTabs
     * @return array
     * @TODO: Other extensions must be able to extend this list
     */
    public function getAll($nestFieldsInTabs = false)
    {
        if (empty(self::$extConfiguration['filebasedDcePath'])
            || !is_dir(PATH_site . self::$extConfiguration['filebasedDcePath'])
        ) {
            return array();
        }

        $staticDces = array();
        $path = PATH_site . self::$extConfiguration['filebasedDcePath'];
        foreach (scandir($path) as $folder) {
            if ($folder === '.' || $folder === '..') {
                continue;
            }
            if (is_dir($path . DIRECTORY_SEPARATOR . $folder)) {
                $staticDces[$folder] = $this->getStaticDceData($folder, $nestFieldsInTabs);
            }
        }
        return $staticDces;
    }
}
