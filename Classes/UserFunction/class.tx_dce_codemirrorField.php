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

		/** @var $fluidTemplate \DceTeam\Dce\Utility\FluidTemplate */
		$fluidTemplate = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\DceTeam\Dce\Utility\FluidTemplate');

		$fluidTemplate->setLayoutRootPath(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:dce/Resources/Private/Layouts/'));
		$fluidTemplate->setPartialRootPath(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:dce/Resources/Private/Partials/'));
		$fluidTemplate->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:dce/Resources/Private/Templates/DceUserFields/Coldmirror.html'));

		$fluidTemplate->assign('name', $this->parameter['itemFormElName']);
		$fluidTemplate->assign('value', $this->parameter['itemFormElValue']);
		$fluidTemplate->assign('onChangeFunc', htmlspecialchars(implode('', $this->parameter['fieldChangeFunc'])));
		$fluidTemplate->assign('onFocus', $this->parameter['onFocus']);

		$fluidTemplate->assign('uniqueIdentifier', uniqid());
		$fluidTemplate->assign('parameters', $this->parameter['fieldConf']['config']['parameters']);
		$fluidTemplate->assign('disableCodemirror', $extConfiguration['disableCodemirror']);


		if ($parameter['fieldConf']['config']['parameters']['mode'] === 'htmlmixed') {
			if ($this->parameter['row'] == 0) {
				$fluidTemplate->assign('availableFields', $this->getAvailableFields());
			}
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

		$rowFields = $this->parameter['row']['fields'];
		if (!empty($rowFields)) {
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'*',
				'tx_dce_domain_model_dcefield',
				'hidden=0 AND deleted=0 AND pid=0 AND (type=0 OR type=2) AND uid IN (' . $rowFields . ')',
				'',
				'variable asc'
			);

			if (is_array($rows)) {
				foreach ($rows as $row) {
					if ($row['type'] === '2') {
						$res2 = $GLOBALS['TYPO3_DB']->sql_query('
							SELECT title, variable

							FROM tx_dce_domain_model_dcefield
							JOIN tx_dce_dcefield_sectionfields_mm
							ON uid = uid_foreign

							WHERE deleted = 0  AND uid_local = ' . $row['uid'] . '
							ORDER BY sorting asc
						');

						$sectionFields = array();
						while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
							$sectionFields[] = $row2;
						}
						$row['hasSectionFields'] = TRUE;
						$row['sectionFields'] = $sectionFields;
					}
					$fields[] = $row;
				}
			}
		}
		return $fields;
	}

	/**
	 *
	 *
	 * @return array
	 */
	protected function getAvailableTemplates() {
		$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/ConfigurationTemplates/';

		$templates = \TYPO3\CMS\Core\Utility\GeneralUtility::get_dirs($path);
		$templates = array_flip($templates);

		foreach($templates as $key => $unused) {
			$files = array();
			foreach(\TYPO3\CMS\Core\Utility\GeneralUtility::getFilesInDir($path . $key) as $file) {
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
		return $this->getViewhelpers(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/FamousViewHelpers/');
	}

	/**
	 * @return array
	 */
	protected function getDceViewHelpers() {
		return $this->getViewhelpers(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CodeSnippets/DceViewHelpers/');
	}

	/**
	 * @param $path
	 * @return array
	 */
	protected function getViewhelpers($path) {
		$files = \TYPO3\CMS\Core\Utility\GeneralUtility::getFilesInDir($path);

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