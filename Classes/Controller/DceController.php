<?php
namespace DceTeam\Dce\Controller;
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


class DceController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * DCE Repository
	 *
	 * @var \DceTeam\Dce\Domain\Repository\DceRepository
	 */
	protected $dceRepository;

	/**
	 * TypoScript Utility
	 *
	 * @var \DceTeam\Dce\Utility\TypoScript
	 */
	protected $typoScriptUtility;

	/**
	 * Inject DCE Repository
	 *
	 * @param \DceTeam\Dce\Domain\Repository\DceRepository $dceRepository
	 * @return void
	 */
	public function injectDceRepository(\DceTeam\Dce\Domain\Repository\DceRepository $dceRepository) {
		$this->dceRepository = $dceRepository;
	}

	/**
	 * Inject TypoScript Utility
	 *
	 * @param \DceTeam\Dce\Utility\TypoScript $typoScriptUtility
	 * @return void
	 */
	public function injectTypoScriptUtility(\DceTeam\Dce\Utility\TypoScript $typoScriptUtility) {
		$this->typoScriptUtility = $typoScriptUtility;
	}

	/**
	 * Initialize Action
	 *
	 * @return void
	 */
	public function initializeAction() {
		if ($this->settings === NULL) {
			$this->settings = array();
		}
		$this->settings = $this->typoScriptUtility->renderConfigurationArray($this->settings);
	}

	/**
	 * Show Action which get called if a DCE get rendered in frontend
	 *
	 * @return string output of dce in frontend
	 */
	public function showAction() {
		$contentObject = $this->configurationManager->getContentObject()->data;
		$config = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

		/** @var $dce \DceTeam\Dce\Domain\Model\Dce */
		$dce = $this->dceRepository->findAndBuildOneByUid(
			$this->dceRepository->extractUidFromCType($config['pluginName']),
			$this->settings,
			$contentObject
		);

		if ($dce->getEnableDetailpage() && intval($contentObject['uid']) === intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP($dce->getDetailpageIdentifier()))) {
			return $dce->renderDetailpage();
		} else {
			return $dce->render();
		}
	}

	/**
	 * Render preview action
	 *
	 * @return string
	 */
	public function renderPreviewAction() {
		$uid = intval($this->settings['dceUid']);
		$contentObject = $this->getContentObject($this->settings['contentElementUid']);
		$previewType = $this->settings['previewType'];

		$this->settings = $this->simulateContentElementSettings($this->settings['contentElementUid']);

		/** @var $dce \DceTeam\Dce\Domain\Model\Dce */
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
	 * Simulates content element settings, which is necessary in backend context
	 *
	 * @param int $contentElementUid
	 * @return array
	 */
	protected function simulateContentElementSettings($contentElementUid) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pi_flexform', 'tt_content', 'uid = ' . $contentElementUid);
		$flexform = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array(current($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)));

		$this->temporaryDceProperties = array();
		if(is_array($flexform)) {
			$this->dceRepository->getVDefValues($flexform, $this);
		}
		return $this->temporaryDceProperties;
	}

	/**
	 * Returns an array with properties of content element with given uid
	 *
	 * @param int $uid of content element to get
	 * @return array with all properties of given content element uid
	 */
	protected function getContentObject($uid) {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', 'uid=' . $uid);
	}
}