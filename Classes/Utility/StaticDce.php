<?php
namespace DceTeam\Dce\Utility;

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
 * Utility for StaticDce
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class StaticDce {
	/**
	 * @var array
	 */
	static protected $extConfiguration = array();

	/**
	 * @var \DceTeam\Dce\Utility\TypoScript
	 */
	static protected $typoscriptUtility = NULL;


	/**
	 * Constructor
	 */
	public function __construct() {
		static::$extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);
		static::$typoscriptUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('DceTeam\Dce\Utility\TypoScript');
	}

	/**
	 * @param array $configurationArray
	 * @return array
	 */
	protected function addTabsAndNestFieldsInIt(array $configurationArray) {
		if (TYPO3_MODE === 'FE') {
			$generalTabLabel = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('generaltab', 'dce');
		} else {
			$generalTabLabel = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:dce/Resources/Private/Language/locallang.xml:generaltab', 'dce');
		}
		$tabs = array(0 => array('title' => $generalTabLabel, 'fields' => array()));
		$i = 0;
		foreach ($configurationArray['tx_dce']['static']['fields'] as $variable => $field) {
			if ($field['type'] === '1') {
				$tabs[++$i] = array(
					'title' => $field['title'],
					'fields' => array()
				);
				continue;
			}
			$tabs[$i]['fields'][$variable] = $field;
		}
		if (empty($tabs[0]['fields'])) {
			unset($tabs[0]);
		}
		$configurationArray['tx_dce']['static']['tabs'] = $tabs;
		return $configurationArray;
	}


	public function getStaticDce($identifier = '', $nestFieldsInTabs = FALSE) {
		$path = static::$extConfiguration['filebasedDcePath'];
		if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
			$path .= DIRECTORY_SEPARATOR;
		}
		$dceFolderPath = PATH_site . $path . $identifier . DIRECTORY_SEPARATOR;

		if (is_dir($dceFolderPath) && file_exists($dceFolderPath . 'Dce.ts')) {
			$dceConfiguration = file_get_contents($dceFolderPath . 'Dce.ts');
			$configurationArray = static::$typoscriptUtility->parseTypoScriptString($dceConfiguration, TRUE);
			if ($nestFieldsInTabs) {
				$configurationArray = $this->addTabsAndNestFieldsInIt($configurationArray);
			}

			$frontendTemplateFile = $dceFolderPath . 'Frontend.html';
			if (file_exists($frontendTemplateFile)) {
				$configurationArray['tx_dce']['static']['template_content'] = file_get_contents($frontendTemplateFile);
			}

			$backendHeaderTemplateFile = $dceFolderPath . 'BackendHeader.html';
			if (file_exists($backendHeaderTemplateFile)) {
				$configurationArray['tx_dce']['static']['header_preview'] = file_get_contents($backendHeaderTemplateFile);
			}

			$backendBodytextTemplateFile = $dceFolderPath . 'BackendBodytext.html';
			if (file_exists($backendBodytextTemplateFile)) {
				$configurationArray['tx_dce']['static']['bodytext_preview'] = file_get_contents($backendBodytextTemplateFile);
			}

			$backendBodytextTemplateFile = $dceFolderPath . 'Detailpage.html';
			if (file_exists($backendBodytextTemplateFile)) {
				$configurationArray['tx_dce']['static']['detailpage_template'] = file_get_contents($backendBodytextTemplateFile);
			}

			$configurationArray['tx_dce']['static']['identifier'] = $identifier;
			$configurationArray['tx_dce']['static']['pid'] = '0';
			$configurationArray['tx_dce']['static']['type'] = '1';
			$configurationArray['tx_dce']['static']['template_type'] = 'inline';
			$configurationArray['tx_dce']['static']['preview_template_type'] = 'inline';
			$configurationArray['tx_dce']['static']['detailpage_template_type'] = 'inline';
			$configurationArray['tx_dce']['static']['hasCustomWizardIcon'] = $configurationArray['tx_dce']['static']['wizard_icon'] === 'custom';

			return $configurationArray['tx_dce']['static'];
		}
	}

	/**
	 * Returns static DCEs
	 *
	 * @param $nestFieldsInTabs
	 * @return array
	 * @TODO: Other extensions must be able to extend this list
	 */
	public function getAll($nestFieldsInTabs = FALSE) {
		if (empty(self::$extConfiguration['filebasedDcePath']) || !is_dir(PATH_site . self::$extConfiguration['filebasedDcePath'])) {
			return array();
		}

		$staticDces = array();
		$path = PATH_site . self::$extConfiguration['filebasedDcePath'];
		foreach(scandir($path) as $folder) {
			if ($folder === '.' || $folder === '..') {
				continue;
			}
			if (is_dir($path . DIRECTORY_SEPARATOR . $folder)) {
				$staticDces[$folder] = $this->getStaticDce($folder, $nestFieldsInTabs);
			}
		}
		return $staticDces;
	}

}