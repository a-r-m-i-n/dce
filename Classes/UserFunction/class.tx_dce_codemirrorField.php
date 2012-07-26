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
 *
 * @package dce
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class tx_dce_codemirrorField {
	/**
	 * @var array Field parameters
	 */
	protected $parameter = array();

	/**
	 * @param $parameter
	 * @return string
	 */
	function getCodemirrorField($parameter) {
		/** @var $extConfiguration array */
		$extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);

		$this->parameter = $parameter;

		/** @var $fluidTemplate Tx_Dce_Utility_FluidTemplate */
		$fluidTemplate = t3lib_div::makeInstance('Tx_Dce_Utility_FluidTemplate');

		$fluidTemplate->setLayoutRootPath(t3lib_div::getFileAbsFileName('EXT:dce/Resources/Private/Layouts/'));
		$fluidTemplate->setPartialRootPath(t3lib_div::getFileAbsFileName('EXT:dce/Resources/Private/Partials/'));
		$fluidTemplate->setTemplatePathAndFilename(t3lib_div::getFileAbsFileName('EXT:dce/Resources/Private/Templates/DceFieldConfiguration/Index.html'));

		$fluidTemplate->assign('name', $this->parameter['itemFormElName']);
		$fluidTemplate->assign('value', $this->parameter['itemFormElValue']);
		$fluidTemplate->assign('onChangeFunc', htmlspecialchars(implode('', $this->parameter['fieldChangeFunc'])));
		$fluidTemplate->assign('onFocus', $this->parameter['onFocus']);

		$fluidTemplate->assign('uniqueIdentifier', uniqid());
		$fluidTemplate->assign('parameters', $this->parameter['fieldConf']['config']['parameters']);
		$fluidTemplate->assign('disableCodemirror', $extConfiguration['DISABLECODEMIRROR']);


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
	protected function getAvailableFields() {
		$fields = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_dce_domain_model_dcefield',
			'hidden=0 AND deleted=0 AND pid=0 AND type=0 AND uid IN (' . $this->parameter['row']['fields'] . ')',
			'',
			'variable asc'
		);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$fields[] = $row;
		}
		return $fields;
	}

	/**
	 *
	 *
	 * @return array
	 */
	protected function getAvailableTemplates() {
		$path = t3lib_extMgm::extPath('dce') . 'Resources/Public/CodeSnippets/ConfigurationTemplates/';

		$templates = t3lib_div::get_dirs($path);
		$templates = array_flip($templates);

		foreach($templates as $key => $unused) {
			$files = array();
			foreach(t3lib_div::getFilesInDir($path . $key) as $file) {
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
	protected function getFamousViewHelpers() {
		return $this->getViewhelpers(t3lib_extMgm::extPath('dce') . 'Resources/Public/CodeSnippets/FamousViewHelpers/');
	}

	/**
	 * @return array
	 */
	protected function getDceViewHelpers() {
		return $this->getViewhelpers(t3lib_extMgm::extPath('dce') . 'Resources/Public/CodeSnippets/DceViewHelpers/');
	}

	/**
	 * @param $path
	 * @return array
	 */
	protected function getViewhelpers($path) {
		$files = t3lib_div::getFilesInDir($path);

		$viewhelpers = array();
		foreach($files as $file) {
			$name = preg_replace('/(.*)\.html/i', '$1', $file);
			$value = file_get_contents($path . $file);
			$viewhelpers[$name] = $value;
		}
		ksort($viewhelpers);
		return $viewhelpers;
	}

}
?>