<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Armin Ruediger Vieweg <armin@v.ieweg.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * DCE Controller
 * Handles the output of content element based on DCEs in frontend and also in backend.
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_Dce_Controller_DceController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * dceRepository
	 * @var Tx_Dce_Domain_Repository_DceRepository
	 */
	protected $dceRepository;

	/**
	 * injectDceRepository
	 *
	 * @param Tx_Dce_Domain_Repository_DceRepository $dceRepository
	 * @return void
	 */
	public function injectDceRepository(Tx_Dce_Domain_Repository_DceRepository $dceRepository) {
		$this->dceRepository = $dceRepository;
	}

	/**
	 * action show
	 *
	 * @return string output of dce in frontend
	 */
	public function showAction() {
		$contentObject = $this->configurationManager->getContentObject()->data;
		$config = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

		/** @var $dce Tx_Dce_Domain_Model_Dce */
		$dce = $this->dceRepository->findAndBuildOneByUid(
			$this->dceRepository->extractUidFromCType($config['pluginName']),
			$this->settings,
			$contentObject
		);

		if ($dce->getEnableDetailpage() && intval($contentObject['uid']) === intval(t3lib_div::_GP($dce->getDetailpageIdentifier()))) {
			return $dce->renderDetailpage();
		} else {
			return $dce->render();
		}
	}

	/**
	 * action renderPreview
	 *
	 * @return string
	 */
	public function renderPreviewAction() {
		$uid = intval($this->settings['dceUid']);
		$contentObject = $this->getContentObject($this->settings['contentElementUid']);
		$previewType = $this->settings['previewType'];

		$this->settings = $this->simulateContentElementSettings($this->settings['contentElementUid']);

		/** @var $dce Tx_Dce_Domain_Model_Dce */
		$dce = clone $this->dceRepository->findAndBuildOneByUid(
			$uid,
			$this->settings,
			$contentObject
		);

		if ($previewType === 'header') {
			return $dce->renderHeaderPreview();
		} else {
			return $dce->renderBodytextPreview();
		}
	}

	/**
	 * @param integer $contentElementUid
	 * @return array
	 */
	protected function simulateContentElementSettings($contentElementUid) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pi_flexform', 'tt_content', 'uid = ' . $contentElementUid);
		$flexform = t3lib_div::xml2array(current($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)));

		$this->temporaryDceProperties = array();
		if(is_array($flexform)) {
			$this->dceRepository->getVDefValues($flexform, $this);
		}
		return $this->temporaryDceProperties;
	}

	/**
	 * Returns an array with properties of content element with given uid
	 *
	 * @param integer $uid of content element to get
	 * @return array with all properties of given content element uid
	 */
	protected function getContentObject($uid) {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', 'uid=' . $uid);
	}

}

?>