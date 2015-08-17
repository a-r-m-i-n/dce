<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * AfterSave Hook
 *
 * @package ArminVieweg\Dce
 */
class AfterSaveHook
{
    /** @var \TYPO3\CMS\Core\DataHandling\DataHandler */
    protected $dataHandler = null;

    /** @var int uid of current record */
    protected $uid = 0;

    /** @var array all properties of current record */
    protected $fieldArray = array();

    /** @var array extension settings */
    protected $extConfiguration = array();


    /**
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $cObj
     * @return void
     */
    public function processDatamap_beforeStart(\TYPO3\CMS\Core\DataHandling\DataHandler $cObj)
    {
        if (array_key_exists('tx_dce_domain_model_dce', $cObj->datamap)) {
            $this->extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);
            $datamap = $cObj->datamap;

            $dceIdentifier = reset(array_keys($datamap['tx_dce_domain_model_dce']));
            if (is_numeric($dceIdentifier) || strpos($dceIdentifier, 'NEW') === 0) {
                return;
            }

            $path = $this->extConfiguration['filebasedDcePath'];
            if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
                $path .= DIRECTORY_SEPARATOR;
            }
            $newValues = reset($datamap['tx_dce_domain_model_dce']);
            $newIdentifier = $newValues['identifier'];
            $dceFolderPath = PATH_site . $path . $newIdentifier . DIRECTORY_SEPARATOR;

            /** @var \ArminVieweg\Dce\Utility\StaticDce $staticDceUtility */
            $staticDceUtility = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\StaticDce');

            $realDceIdentifier = substr($dceIdentifier, 4);
            $oldValues = $staticDceUtility->getStaticDceData($realDceIdentifier);

            if (!empty($oldValues)) {
                $oldIdentifier = $oldValues['identifier'];
            }

            $renamed = false;
            if (isset($oldIdentifier) && $oldIdentifier !== $newIdentifier) {
                if (file_exists($dceFolderPath)) {
                    \ArminVieweg\Dce\Utility\FlashMessage::add(
                        'Another static DCE with name "' . $newIdentifier . '" already exists.',
                        'Renaming failed',
                        \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
                    );
                    $newIdentifier = $oldIdentifier;
                    $dceFolderPath = PATH_site . $path . $newIdentifier . DIRECTORY_SEPARATOR;
                } else {
                    // Rename
                    rename(PATH_site . $path . $oldIdentifier . DIRECTORY_SEPARATOR, $dceFolderPath);
                    $renamed = true;
                }
            } else {
                // Create
                if (!file_exists($dceFolderPath) && !is_dir($dceFolderPath)) {
                    mkdir($dceFolderPath, 0777, true);
                    GeneralUtility::fixPermissions($dceFolderPath);
                }
            }

            unset($newValues['identifier']);

            $fields = array();
            foreach (GeneralUtility::trimExplode(',', $newValues['fields'], true) as $fieldId) {
                $fieldSettings = $datamap['tx_dce_domain_model_dcefield'][$fieldId];

                if (intval($fieldSettings['type']) === 2) {
                    $sectionFields = array();
                    $sectionFieldIds = GeneralUtility::trimExplode(',', $fieldSettings['section_fields'], true);
                    foreach ($sectionFieldIds as $sectionFieldId) {
                        $sectionFieldVariable = $datamap['tx_dce_domain_model_dcefield'][$sectionFieldId]['variable'];
                        if ($sectionFieldId !== $sectionFieldVariable) {
                            $sectionFields[$sectionFieldVariable] =
                                $datamap['tx_dce_domain_model_dcefield'][$sectionFieldId];
                        } else {
                            $sectionFields[$sectionFieldId] = $datamap['tx_dce_domain_model_dcefield'][$sectionFieldId];
                        }
                    }
                    $fieldSettings['section_fields'] = $sectionFields;
                }

                if ($fieldId !== $fieldSettings['variable']) {
                    $fields[$this->getVariableNameFromFieldSettings($fieldSettings)] = $fieldSettings;
                } else {
                    $fields[$fieldId] = $fieldSettings;
                }
            }

            $newValues['fields'] = $fields;

            file_put_contents($dceFolderPath . 'Frontend.html', $newValues['template_content']);
            file_put_contents($dceFolderPath . 'BackendHeader.html', $newValues['header_preview']);
            file_put_contents($dceFolderPath . 'BackendBodytext.html', $newValues['bodytext_preview']);
            file_put_contents($dceFolderPath . 'Detailpage.html', $newValues['detailpage_template']);

            GeneralUtility::fixPermissions($dceFolderPath, true);

            unset($newValues['type']);
            unset($newValues['template_type']);
            unset($newValues['template_content']);
            unset($newValues['detailpage_template_type']);
            unset($newValues['detailpage_template']);
            unset($newValues['preview_template_type']);
            unset($newValues['header_preview']);
            unset($newValues['bodytext_preview']);

            /** @var \ArminVieweg\Dce\Utility\TypoScript $typoScriptUtility */
            $typoScriptUtility = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\TypoScript');
            $dceTypoScript = $typoScriptUtility->convertArrayToTypoScript($newValues, 'tx_dce.static');

            file_put_contents($dceFolderPath . 'Dce.ts', $dceTypoScript);

            $cObj->datamap = array();

            $saveOnly = GeneralUtility::_GP('_savedok_x') && GeneralUtility::_GP('_savedok_y');
            if ($saveOnly === true && $renamed === true) {
                ob_clean();
                header(
                    'Location: alt_doc.php?edit[tx_dce_domain_model_dce][dce_' . $newIdentifier .
                    ']=edit&returnUrl=' . urlencode(GeneralUtility::_GP('returnUrl'))
                );
                die;
            }
        }
    }

    /**
     * If variable in given fieldSettings is set, it will be returned.
     * Otherwise a new variableName will be returned, based on the type of the field.
     *
     * @param array $fieldSettings
     * @return string
     */
    protected function getVariableNameFromFieldSettings(array $fieldSettings)
    {
        if (!isset($fieldSettings['variable']) || empty($fieldSettings['variable'])) {
            switch ($fieldSettings['type']) {
                default:
                case 0:
                    return uniqid('field_');

                case 1:
                    return uniqid('tab_');

                case 2:
                    return uniqid('section_');
            }
        }
        return $fieldSettings['variable'];
    }

    /**
     * Hook action
     *
     * @param $status
     * @param $table
     * @param $id
     * @param array $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     *
     * @return void
     */
    public function processDatamap_afterDatabaseOperations(
        $status,
        $table,
        $id,
        array $fieldArray,
        \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
    ) {
        $this->extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);
        $this->dataHandler = $pObj;
        $this->fieldArray = array();
        foreach ($fieldArray as $key => $value) {
            if (!empty($key)) {
                $this->fieldArray[$key] = $value;
            }
        }
        $this->uid = $this->getUid($id, $table, $status, $pObj);

        if ($table === 'tt_content' && $this->isDceContentElement($pObj)) {
            $this->checkAndUpdateDceRelationField();
            if (!isset($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress'])) {
                $this->performPreviewAutoupdateOnContentElementSave();
            }
        }

        if ($table === 'tx_dce_domain_model_dce' && $status === 'update') {
            if (!isset($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress'])) {
                if (array_key_exists('hidden', $fieldArray) && $fieldArray['hidden'] == '1') {
                    $this->hideContentElementsBasedOnDce();
                } else {
                    $this->performPreviewAutoupdateBatchOnDceChange();
                }
            }
        }

        // Clear cache if dce or dcefield has been created or updated
        if ($this->extConfiguration['disableAutoClearCache'] == 0
            && in_array($table, array('tx_dce_domain_model_dce', 'tx_dce_domain_model_dcefield'))
            && in_array($status, array('update', 'new'))
        ) {
            $pObj->clear_cacheCmd('all');
        }
    }

    /**
     * On save on content element, which based on dce, its preview texts become updated. If change is made in
     * frontend context, they can not get rendered. Instead a message will appear, which informs the user in backend
     * about this circumstance.
     *
     * @return void
     */
    protected function performPreviewAutoupdateOnContentElementSave()
    {
        if (TYPO3_MODE === 'BE') {
            $mergedFieldArray = array_merge($this->fieldArray, $this->generateDcePreview($this->uid));
            $this->dataHandler->updateDB('tt_content', $this->uid, $mergedFieldArray);
        } else {
            // Preview texts can not created in frontend context
            $this->dataHandler->updateDB('tt_content', $this->uid, array_merge($this->fieldArray, array(
                'header' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'contentElementCreatedByFrontendHeader',
                    'dce'
                ),
                'bodytext' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'contentElementCreatedByFrontendBodytext',
                    'dce',
                    array(GeneralUtility::_GP('eID'))
                ),
            )));
        }
    }

    /**
     * If this function has not been disabled in extension settings, it performs an update of all existing content
     * elements, which based on DCE. The preview texts will be updated. This could become delicate if is existing a
     * high amount of such elements.
     *
     * @return void
     */
    protected function performPreviewAutoupdateBatchOnDceChange()
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'uid',
            'tt_content',
            'CType="dce_dceuid' . $this->uid . '" AND deleted=0'
        );
        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
            if (!$GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress']) {
                $fieldArray = $this->generateDcePreview($row['uid']);
                $this->dataHandler->updateDB('tt_content', $row['uid'], $fieldArray);
            } else {
                unset($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress']);
            }
        }
    }

    /**
     * Disables content elements based on this deactivated DCE. Also display flash message
     * about the amount of content elements affected and a notice, that these content elements
     * will not get re-enabled when enabling the DCE again.
     *
     * @return void
     */
    protected function hideContentElementsBasedOnDce()
    {
        $whereStatement = 'CType="dce_dceuid' . $this->uid . '" AND deleted=0 AND hidden=0';
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tt_content', $whereStatement);
        $updatedContentElementsCount = 0;
        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
            $this->dataHandler->updateDB('tt_content', $row['uid'], array('hidden' => 1));
            $updatedContentElementsCount++;
        }

        if ($updatedContentElementsCount === 0) {
            return;
        }

        $pathToLocallang = 'LLL:EXT:dce/Resources/Private/Language/locallang_mod.xml:';
        $message = LocalizationUtility::translate(
            $pathToLocallang . 'hideContentElementsBasedOnDce',
            'Dce',
            array('count' => $updatedContentElementsCount)
        );
        FlashMessage::add(
            $message,
            LocalizationUtility::translate($pathToLocallang . 'caution', 'Dce'),
            \TYPO3\CMS\Core\Messaging\FlashMessage::INFO
        );
    }

    /**
     * Generates the preview texts (header and bodytext) of dce
     *
     * @param int $uid uid of content element
     * @return array
     *
     * @TODO Reduce redundancy of extbase controller call
     */
    protected function generateDcePreview($uid)
    {
        $settings = array(
            'contentElementUid' => $uid,
            'dceUid' => $this->getDceUidByContentElementUid($uid),
        );
        return array(
            'header' => $this->runExtbaseController(
                'ArminVieweg',
                'Dce',
                'Dce',
                'renderPreview',
                'tools_DceDcemodule',
                array_merge($settings, array('previewType' => 'header'))
            ),
            'bodytext' => $this->runExtbaseController(
                'ArminVieweg',
                'Dce',
                'Dce',
                'renderPreview',
                'tools_DceDcemodule',
                array_merge($settings, array('previewType' => 'bodytext'))
            ),
        );
    }

    /**
     * @param int $contentElementUid
     * @param int $dceUid
     * @return array
     */
    public function ajaxGenerateDcePreview($contentElementUid, $dceUid)
    {
        $settings = array(
            'contentElementUid' => $contentElementUid,
            'dceUid' => $dceUid,
        );
        return array(
            'header' => $this->runExtbaseController(
                'ArminVieweg',
                'Dce',
                'renderPreview',
                'tools_DceDcemodule',
                array_merge($settings, array('previewType' => 'header'))
            ),
            'bodytext' => $this->runExtbaseController(
                'ArminVieweg',
                'Dce',
                'renderPreview',
                'tools_DceDcemodule',
                array_merge($settings, array('previewType' => 'bodytext'))
            ),
        );
    }

    /**
     * Initializes and runs an extbase controller
     *
     * @param string $vendorName Name of vendor
     * @param string $extensionName Name of extension, in UpperCamelCase
     * @param string $controller Name of controller, in UpperCamelCase
     * @param string $action Optional name of action, in lowerCamelCase (without 'Action' suffix). Default is 'index'.
     * @param string $pluginName Optional name of plugin. Default is 'Pi1'.
     * @param array $settings Optional array of settings to use in controller and fluid template. Default is array().
     *
     * @return string output of controller's action
     * @TODO: Is there a better to call extbase?
     */
    protected function runExtbaseController(
        $vendorName,
        $extensionName,
        $controller,
        $action = 'index',
        $pluginName = 'Pi1',
        $settings = array()
    ) {
        $bootstrap = new \TYPO3\CMS\Extbase\Core\Bootstrap();
        $bootstrap->cObj = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
        $configuration = array(
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'controller' => $controller,
            'action' => $action,
            'pluginName' => $pluginName,
            'settings' => $settings
        );

        \ArminVieweg\Dce\Utility\ForbiddenUtility::setExtbaseRelatedPostParameters($controller, $action);
        return $bootstrap->run('', $configuration);
    }

    /**
     * Gets dce uid by content element uid
     *
     * @return int
     */
    protected function getDceUidByContentElementUid($uid)
    {
        $cType = current($this->dataHandler->recordInfo('tt_content', $uid, 'CType'));
        return intval(substr($cType, strlen('dce_dceuid')));
    }

    /**
     * Checks the CType of current content element and return TRUE if it is a dce. Otherwise return FALSE.
     *
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     * @return bool
     */
    protected function isDceContentElement(\TYPO3\CMS\Core\DataHandling\DataHandler $pObj)
    {
        $datamap = reset(reset($pObj->datamap));
        return (strpos($datamap['CType'], 'dce_dceuid') !== false);
    }

    /**
     * Investigates the uid of entry
     *
     * @param $id
     * @param $status
     * @param $pObj
     *
     * @return int
     */
    protected function getUid($id, $table, $status, $pObj)
    {
        $uid = $id;
        if ($status === 'new') {
            if (!$pObj->substNEWwithIDs[$id]) {
                //postProcessFieldArray
                $uid = 0;
            } else {
                //afterDatabaseOperations
                $uid = $pObj->substNEWwithIDs[$id];
                if (isset($pObj->autoVersionIdMap[$table][$uid])) {
                    $uid = $pObj->autoVersionIdMap[$table][$uid];
                }
            }
        }
        return intval($uid);
    }

    /**
     * Checks if dce relation (field tx_dce_dce) is empty. If it is empty, it will be filled by CType.
     * @return void
     */
    protected function checkAndUpdateDceRelationField()
    {
        $row = $this->dataHandler->recordInfo('tt_content', $this->uid, 'CType,tx_dce_dce');
        if (empty($row['tx_dce_dce'])) {
            $this->dataHandler->updateDB('tt_content', $this->uid, array(
                'tx_dce_dce' => \ArminVieweg\Dce\Domain\Repository\DceRepository::extractUidFromCtype($row['CType'])
            ));
        }
    }
}
