<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012-2014 Armin Ruediger Vieweg <armin@v.ieweg.de>
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Save DCE Hook
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class tx_saveDce {
	/** @var \TYPO3\CMS\Core\DataHandling\DataHandler */
	protected $tcemain = NULL;

	/** @var integer uid of current record */
	protected $uid = 0;

	/** @var array all properties of current record */
	protected $fieldArray = array();

	/** @var array extension settings */
	protected $extConfiguration = array();


	public function processDatamap_beforeStart(\TYPO3\CMS\Core\DataHandling\DataHandler $cObj) {
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

			/** @var \DceTeam\Dce\Utility\StaticDce $staticDceUtility */
			$staticDceUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('DceTeam\Dce\Utility\StaticDce');

			$realDceIdentifier = substr($dceIdentifier, 4);
			$oldValues = $staticDceUtility->getStaticDceData($realDceIdentifier);


			if (!empty($oldValues)) {
				$oldIdentifier = $oldValues['identifier'];
			}

			$renamed = FALSE;
			if (isset($oldIdentifier) && $oldIdentifier !== $newIdentifier) {
				if (file_exists($dceFolderPath)) {
					\DceTeam\Dce\Utility\FlashMessage::add('Another static DCE with name "' . $newIdentifier . '" already exists.', 'Renaming failed', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
					$newIdentifier = $oldIdentifier;
					$dceFolderPath = PATH_site . $path . $newIdentifier . DIRECTORY_SEPARATOR;
				} else {
						// Rename
					rename(PATH_site . $path . $oldIdentifier . DIRECTORY_SEPARATOR, $dceFolderPath);
					$renamed = TRUE;
				}
			} else {
				// Create
				if (!file_exists($dceFolderPath) && !is_dir($dceFolderPath)) {
					mkdir($dceFolderPath, 0777, TRUE);
					\TYPO3\CMS\Core\Utility\GeneralUtility::fixPermissions($dceFolderPath);
				}
			}

			unset($newValues['identifier']);

			$fields = array();
			foreach (t3lib_div::trimExplode(',', $newValues['fields'], TRUE) as $fieldId) {
				$fieldSettings = $datamap['tx_dce_domain_model_dcefield'][$fieldId];

				if (intval($fieldSettings['type']) === 2) {
					$sectionFields = array();
					foreach (t3lib_div::trimExplode(',', $fieldSettings['section_fields'], TRUE) as $sectionFieldId) {
						$sectionFieldVariable = $datamap['tx_dce_domain_model_dcefield'][$sectionFieldId]['variable'];
						if ($sectionFieldId !== $sectionFieldVariable) {
							$sectionFields[$sectionFieldVariable] = $datamap['tx_dce_domain_model_dcefield'][$sectionFieldId];
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

			\TYPO3\CMS\Core\Utility\GeneralUtility::fixPermissions($dceFolderPath, TRUE);

			unset($newValues['type']);
			unset($newValues['template_type']);
			unset($newValues['template_content']);
			unset($newValues['detailpage_template_type']);
			unset($newValues['detailpage_template']);
			unset($newValues['preview_template_type']);
			unset($newValues['header_preview']);
			unset($newValues['bodytext_preview']);


			/** @var \DceTeam\Dce\Utility\TypoScript $typoScriptUtility */
			$typoScriptUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\DceTeam\Dce\Utility\TypoScript');
			$dceTypoScript = $typoScriptUtility->convertArrayToTypoScript($newValues, 'tx_dce.static');

			file_put_contents($dceFolderPath . 'Dce.ts', $dceTypoScript);

			$cObj->datamap = array();

			$saveOnly = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('_savedok_x') && \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('_savedok_y');
			if ($saveOnly === TRUE && $renamed === TRUE) {
				ob_clean();
				header('Location: alt_doc.php?edit[tx_dce_domain_model_dce][dce_' . $newIdentifier . ']=edit&returnUrl=' . urlencode(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('returnUrl')));
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
	protected function getVariableNameFromFieldSettings(array $fieldSettings) {
		if (!isset($fieldSettings['variable']) || empty($fieldSettings['variable'])) {
			switch ($fieldSettings['type']) {
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

//	public function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, &$id, $cObj) {
//		if (in_array($table, array('tx_dce_domain_model_dce', 'tx_dce_domain_model_dcefield'))) {

//			\TYPO3\CMS\Core\Utility\DebugUtility::debug($incomingFieldArray, $table . ' - ' . $id);

//			$incomingFieldArray = array();
//			$id = 0;
//		}
//	}

	/**
	 * Hook action
	 *
	 * @param $status
	 * @param $table
	 * @param $id
	 * @param array $fieldArray
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler$pObj
	 *
	 * @return void
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $id, array $fieldArray, \TYPO3\CMS\Core\DataHandling\DataHandler $pObj) {
		$this->extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);
		$this->tcemain = $pObj;
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
				$this->performPreviewAutoupdateBatchOnDceChange();
				\DceTeam\Dce\Controller\DceModuleController::removePreviewRecords();
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
	 *
	 * @TODO Add link to notice, too - like in performPreviewAutoupdateBatchOnDceChange()
	 */
	protected function performPreviewAutoupdateOnContentElementSave() {
		if (TYPO3_MODE === 'BE') {
			$mergedFieldArray = array_merge($this->fieldArray, $this->generateDcePreview($this->uid));
			$this->tcemain->updateDB('tt_content', $this->uid, $mergedFieldArray);
		} else {
			// Preview texts can not created in frontend context
			$this->tcemain->updateDB('tt_content', $this->uid, array_merge($this->fieldArray, array(
				'header' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('contentElementCreatedByFrontendHeader', 'dce'),
				'bodytext' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('contentElementCreatedByFrontendBodytext', 'dce', array(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('eID'))),
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
	protected function performPreviewAutoupdateBatchOnDceChange() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tt_content', 'CType="dce_dceuid' . $this->uid . '"');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($this->extConfiguration['disablePreviewAutoUpdate'] == 0 && !$GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress']) {
				$fieldArray = $this->generateDcePreview($row['uid']);
			} else {
				// if autoupdate of preview is disabled, show notice instead
				$uid = $row['uid'];
				$dceUid = $this->uid;

				$postBody = 'ajaxID=Dce::updateContentElement&uid=' . $uid . '&dceUid=' . $dceUid;
				$js = "var t=this, b=$(t).up('span'), h=$(b).previous('strong'); $(t).replace('<img src=\'../../../../typo3conf/ext/dce/Resources/Public/Icons/ajax-loader.gif\' alt=\'\' /> ' + $(t).innerHTML); new Ajax.Request('../../../ajax.php',{postBody:'" . $postBody ."', onSuccess:function(r){ var j=r.responseText.evalJSON(); b.update(j.bodytext); $(h).update(j.header); }}); return false;";

				$fieldArray = array(
					'header' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('autoupdateDisabledHeader', 'dce'),
					'bodytext' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('autoupdateDisabledBodytext', 'dce', array($js)),
				);
				unset($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress']);
			}
			$this->tcemain->updateDB('tt_content', $row['uid'], $fieldArray);
		}
		echo ''; // prevent a bug in 4.5 which returns no output
	}


	/**
	 * Generates the preview texts (header and bodytext) of dce
	 *
	 * @param integer $uid uid of content element
	 * @return array
	 *
	 * @TODO Reduce redundancy of extbase controller call
	 */
	protected function generateDcePreview($uid) {
		$settings = array(
			'contentElementUid' => $uid,
			'dceUid' => $this->getDceUidByContentElementUid($uid),
		);
		return array(
			'header' => $this->runExtbaseController('DceTeam', 'Dce', 'Dce', 'renderPreview', 'tools_DceDcemodule', array_merge($settings, array('previewType' => 'header'))),
			'bodytext' => $this->runExtbaseController('DceTeam', 'Dce', 'Dce', 'renderPreview', 'tools_DceDcemodule', array_merge($settings, array('previewType' => 'bodytext'))),
		);
	}

	/**
	 * @param integer $contentElementUid
	 * @param integer $dceUid
	 * @return array
	 */
	public function ajaxGenerateDcePreview($contentElementUid, $dceUid) {
		$settings = array(
			'contentElementUid' => $contentElementUid,
			'dceUid' => $dceUid,
		);
		return array(
			'header' => $this->runExtbaseController('DceTeam', 'Dce', 'renderPreview', 'tools_DceDcemodule', array_merge($settings, array('previewType' => 'header'))),
			'bodytext' => $this->runExtbaseController('DceTeam', 'Dce', 'renderPreview', 'tools_DceDcemodule', array_merge($settings, array('previewType' => 'bodytext'))),
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
	 */
	protected function runExtbaseController($vendorName, $extensionName, $controller, $action = 'index', $pluginName = 'Pi1', $settings = array()) {
		$bootstrap = new \TYPO3\CMS\Extbase\Core\Bootstrap();
		$bootstrap->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tslib_cObj');

		$configuration = array(
			'vendorName' => $vendorName,
			'extensionName' => $extensionName,
			'controller' => $controller,
			'action' => $action,
			'pluginName' => $pluginName,
			'settings' => $settings
		);

		$_POST['tx_dce_tools_dcedcemodule']['controller'] = $controller;
		$_POST['tx_dce_tools_dcedcemodule']['action'] = $action;

		return $bootstrap->run('', $configuration);
	}

	/**
	 * Gets dce uid by content element uid
	 *
	 * @return integer
	 */
	protected function getDceUidByContentElementUid($uid) {
		$cType = current($this->tcemain->recordInfo('tt_content', $uid, 'CType'));
		return intval(substr($cType, strlen('dce_dceuid')));
	}

	/**
	 * Checks the CType of current content element and return TRUE if it is a dce. Otherwise return FALSE.
	 *
	 * @param t3lib_TCEmain $pObj
	 * @return bool
	 */
	protected function isDceContentElement(\TYPO3\CMS\Core\DataHandling\DataHandler $pObj) {
		$datamap = reset(reset($pObj->datamap));
		return (strpos($datamap['CType'], 'dce_dceuid') !== FALSE);
	}

	/**
	 * Investigates the uid of entry
	 *
	 * @param $id
	 * @param $status
	 * @param $pObj
	 *
	 * @return integer
	 */
	protected function getUid($id, $table, $status, $pObj) {
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
	protected function checkAndUpdateDceRelationField() {
		$row = $this->tcemain->recordInfo('tt_content', $this->uid, 'CType,tx_dce_dce');
		if(empty($row['tx_dce_dce'])) {
			$this->tcemain->updateDB('tt_content', $this->uid, array(
				'tx_dce_dce' => \DceTeam\Dce\Domain\Repository\DceRepository::extractUidFromCType($row['CType'])
			));
		}
	}
}