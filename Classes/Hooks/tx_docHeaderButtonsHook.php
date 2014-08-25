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
 * Hook for docHeaderButtons
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class tx_docHeaderButtonsHook {

	/**
	 * Adds a new button to docheader. This affects just DCE instances. The button will be not visible if the current
	 * backend user is no administrator.
	 *
	 * @param array $params
	 * @return void
	 */
	public function addQuickDcePopupButton(array &$params) {
		$editGetParam = t3lib_div::_GP('edit');
		$editGetParam = isset($editGetParam['tt_content']) ? $editGetParam['tt_content'] : NULL;
		if ($editGetParam === NULL || !is_array($editGetParam)) {
			return;
		}
		$uidWithComma = current(array_keys($editGetParam));

		if ($editGetParam[$uidWithComma] === 'edit') {
			$uid = intval($uidWithComma);

			/** @var $tceMain t3lib_TCEmain */
			$tceMain = t3lib_div::makeInstance('t3lib_TCEmain');
			$contentRecord = $tceMain->recordInfo('tt_content', $uid, 'CType');
			$CType = current($contentRecord);
			$this->requireDceRepository();
			$dceUid = Tx_Dce_Domain_Repository_DceRepository::extractUidFromCType($CType);

			if ($dceUid !== FALSE) {
				$buttonCode = '';
				if ($GLOBALS['BE_USER']->isAdmin()) {
					$linkToDce = 'alt_doc.php?&returnUrl=close.html&edit[tx_dce_domain_model_dce][' . $dceUid . ']=edit';
					$pathToImage = t3lib_extMgm::extRelPath('dce') . 'Resources/Public/Icons/docheader_icon.png';
					$titleTag = Tx_Extbase_Utility_Localization::translate('quickDcePopup', 'Dce');
					$buttonCode = '<div class="buttongroup"><a href="#" onclick="window.open(\'' . $linkToDce . '\', \'editDcePopup\', \'height=600,width=820,status=0,menubar=0,scrollbars=1\')"><img src="' . $pathToImage . '" alt="" title="' . $titleTag . '" /></a></div>';
				}
				$params['markers']['BUTTONLIST_LEFT'] .= $buttonCode . $this->getCustomStylesheet();
			}
		}
	}

	/**
	 * Includes the dce repository for TYPO3 4.5
	 * @return void
	 */
	protected function requireDceRepository() {
		require_once(t3lib_extMgm::extPath('dce') . 'Classes/Domain/Repository/DceRepository.php');
	}


	/**
	 * Adds stylesheet when editing dce instance. Not nice solved, but it works.
	 * @return string
	 */
	protected function getCustomStylesheet() {
		$file = t3lib_extMgm::extPath('dce') . 'Resources/Public/CSS/dceInstance.css';
		$content = file_get_contents($file);
		return '<style type="text/css">' . $content . '</style>';
	}
}