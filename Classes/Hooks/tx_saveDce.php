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
	/** @var t3lib_TCEmain */
	protected $tcemain = NULL;

	/** @var integer uid of current record */
	protected $uid = 0;

	/** @var array all properties of current record */
	protected $fieldArray = array();

	/** @var array extension settings */
	protected $extConfiguration = array();


	/**
	 * Hook action
	 *
	 * @param $status
	 * @param $table
	 * @param $id
	 * @param array $fieldArray
	 * @param t3lib_TCEmain $pObj
	 *
	 * @return void
	 */
    public function processDatamap_afterDatabaseOperations($status, $table, $id, array $fieldArray, t3lib_TCEmain $pObj) {
		$this->extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);
		$this->tcemain = $pObj;
		$this->fieldArray = array();
        foreach ($fieldArray as $key => $value) {
			if (!empty($key)) {
				$this->fieldArray[$key] = $value;
			}
        }
		$this->uid = $this->getUid($id, $status, $pObj);

		if ($table === 'tt_content' && $this->isDceContentElement($pObj)) {
			$this->checkAndUpdateDceRelationField();
			if (!isset($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress'])) {
				$this->performPreviewAutoupdateOnContentElementSave();
			}
		}

		if ($table === 'tx_dce_domain_model_dce' && $status === 'update') {
			if (!isset($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress'])) {
				$this->performPreviewAutoupdateBatchOnDceChange();
				Tx_Dce_Controller_DceModuleController::removePreviewRecords();
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
				'header' => Tx_Extbase_Utility_Localization::translate('contentElementCreatedByFrontendHeader', 'dce'),
				'bodytext' => Tx_Extbase_Utility_Localization::translate('contentElementCreatedByFrontendBodytext', 'dce', array(t3lib_div::_GP('eID'))),
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
					'header' => Tx_Extbase_Utility_Localization::translate('autoupdateDisabledHeader', 'dce'),
					'bodytext' => Tx_Extbase_Utility_Localization::translate('autoupdateDisabledBodytext', 'dce', array($js)),
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
			'header' => $this->runExtbaseController('Dce', 'Dce', 'renderPreview', 'tools_DceDcemodule', array_merge($settings, array('previewType' => 'header'))),
			'bodytext' => $this->runExtbaseController('Dce', 'Dce', 'renderPreview', 'tools_DceDcemodule', array_merge($settings, array('previewType' => 'bodytext'))),
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
			'header' => $this->runExtbaseController('Dce', 'Dce', 'renderPreview', 'tools_DceDcemodule', array_merge($settings, array('previewType' => 'header'))),
			'bodytext' => $this->runExtbaseController('Dce', 'Dce', 'renderPreview', 'tools_DceDcemodule', array_merge($settings, array('previewType' => 'bodytext'))),
		);
	}

	/**
	 * Initializes and runs an extbase controller
	 *
	 * @param string $extensionName Name of extension, in UpperCamelCase
	 * @param string $controller Name of controller, in UpperCamelCase
	 * @param string $action Optional name of action, in lowerCamelCase (without 'Action' suffix). Default is 'index'.
	 * @param string $pluginName Optional name of plugin. Default is 'Pi1'.
	 * @param array $settings Optional array of settings to use in controller and fluid template. Default is array().
	 *
	 * @return string output of controller's action
	 */
	protected function runExtbaseController($extensionName, $controller, $action = 'index', $pluginName = 'Pi1', $settings = array()) {
		$bootstrap = new Tx_Extbase_Core_Bootstrap();
		$bootstrap->cObj = t3lib_div::makeInstance('tslib_cObj');

		$configuration = array(
			'pluginName' => $pluginName,
			'extensionName' => $extensionName,
			'controller' => $controller,
			'action' => $action,
			'settings' => $settings,
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
	 * @return boolean
	 */
	protected function isDceContentElement(t3lib_TCEmain $pObj) {
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
	protected function getUid($id, $status, $pObj) {
		$uid = $id;
		if ($status === 'new') {
			if (!$pObj->substNEWwithIDs[$id]) {
				//postProcessFieldArray
				$uid = 0;
			} else {
				//afterDatabaseOperations
				$uid = $pObj->substNEWwithIDs[$id];
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
				'tx_dce_dce' => Tx_Dce_Domain_Repository_DceRepository::extractUidFromCType($row['CType'])
			));
		}
	}
}
?>