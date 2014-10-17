<?php
namespace ArminVieweg\Dce\Utility;

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
	 * @var \Tx_Dce_Utility_TypoScript
	 */
	static protected $typoscriptUtility = NULL;


	/**
	 * Constructor
	 */
	public function __construct() {
		static::$extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);
		static::$typoscriptUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Dce_Utility_TypoScript');
	}



	public function getStaticDce($identifier = '') {
		$path = static::$extConfiguration['filebasedDcePaths'];
		if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
			$path .= DIRECTORY_SEPARATOR;
		}
		$dceFolderPath = PATH_site . $path . $identifier . DIRECTORY_SEPARATOR;
		if (is_dir($dceFolderPath) && file_exists($dceFolderPath . 'Dce.ts')) {
			$dceConfiguration = file_get_contents($dceFolderPath . 'Dce.ts');
			$configurationArray = static::$typoscriptUtility->parseTypoScriptString($dceConfiguration, TRUE);

			$frontendTemplateFile = $dceFolderPath . 'Frontend.html';
			if (file_exists($frontendTemplateFile)) {
				$configurationArray['tx_dce']['static'][$identifier]['template_content'] = file_get_contents($frontendTemplateFile);
			}

			$backendHeaderTemplateFile = $dceFolderPath . 'BackendHeader.html';
			if (file_exists($backendHeaderTemplateFile)) {
				$configurationArray['tx_dce']['static'][$identifier]['header_preview'] = file_get_contents($backendHeaderTemplateFile);
			}

			$backendBodytextTemplateFile = $dceFolderPath . 'BackendBodytext.html';
			if (file_exists($backendBodytextTemplateFile)) {
				$configurationArray['tx_dce']['static'][$identifier]['bodytext_preview'] = file_get_contents($backendBodytextTemplateFile);
			}

			$backendBodytextTemplateFile = $dceFolderPath . 'Detailpage.html';
			if (file_exists($backendBodytextTemplateFile)) {
				$configurationArray['tx_dce']['static'][$identifier]['detailpage_template'] = file_get_contents($backendBodytextTemplateFile);
			}

			$configurationArray['tx_dce']['static'][$identifier]['identifier'] = $identifier;
			$configurationArray['tx_dce']['static'][$identifier]['template_type'] = 'inline';
			$configurationArray['tx_dce']['static'][$identifier]['preview_template_type'] = 'inline';
			$configurationArray['tx_dce']['static'][$identifier]['detailpage_template_type'] = 'inline';


			return $configurationArray['tx_dce']['static'][$identifier];
		}
	}

}