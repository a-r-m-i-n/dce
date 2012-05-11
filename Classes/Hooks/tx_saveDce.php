<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
		$this->tcemain = $pObj;
		$uid = $this->getUid($id, $status, $pObj);

			// Updates header and bodytext of content element
		if ($table === 'tt_content' && $this->isDceContentElement($pObj)) {
			$fieldArray = array_merge($fieldArray, $this->generateDcePreview($uid));
			$pObj->updateDB('tt_content', $uid, $fieldArray);
		}

			// Update preview output of all content elements using this dce, if dce gets updated
		if ($table === 'tx_dce_domain_model_dce' && $status === 'update') {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tt_content', 'CType="dce_dceuid' . intval($uid). '"');
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$fieldArray = $this->generateDcePreview($row['uid']);
				$pObj->updateDB('tt_content', $row['uid'], $fieldArray);
			}
		}

			// Clear cache if dce or dcefield has been created or updated
		if (in_array($table, array('tx_dce_domain_model_dce', 'tx_dce_domain_model_dcefield'))
				&& in_array($status, array('update', 'new'))) {
			t3lib_extMgm::removeCacheFiles('temp_CACHED_dce');
		}
    }

	/**
	 * @param integer $contentElementUid
	 *
	 * @return array
	 */
	protected function generateDcePreview($contentElementUid) {
		$settings = array(
			'contentElementUid' => $contentElementUid,
			'dceUid' => $this->getDceUidByContentElementUid($contentElementUid),
		);
		return array(
			'header' => $this->runExtbaseController('Dce', 'Dce', 'renderPreview', 'DceHide_DcePi1', array_merge($settings, array('previewType' => 'header'))),
			'bodytext' => $this->runExtbaseController('Dce', 'Dce', 'renderPreview', 'DceHide_DcePi1', array_merge($settings, array('previewType' => 'bodytext'))),
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
		return $bootstrap->run('', $configuration);
	}

	/**
	 * Gets dce uid by content element uid
	 *
	 * @param integer $contentElementUid
	 * @return integer
	 */
	protected function getDceUidByContentElementUid($contentElementUid) {
		$cType = current($this->tcemain->recordInfo('tt_content', $contentElementUid, 'CType'));
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
}
?>