<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Hook for docHeaderButtons
 *

 *
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
		$editGetParam = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('edit');
		$editGetParam = isset($editGetParam['tt_content']) ? $editGetParam['tt_content'] : NULL;
		if ($editGetParam === NULL || !is_array($editGetParam)) {
			return;
		}
		$uidWithComma = current(array_keys($editGetParam));

		if ($editGetParam[$uidWithComma] === 'edit') {
			$uid = intval($uidWithComma);

			/** @var $tceMain t3lib_TCEmain */
			$tceMain = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_TCEmain');
			$contentRecord = $tceMain->recordInfo('tt_content', $uid, 'CType');
			$cType = current($contentRecord);
			$this->requireDceRepository();
			$dceUid = \ArminVieweg\Dce\Domain\Repository\DceRepository::extractUidFromCtype($cType);

			if ($dceUid !== FALSE) {
				$buttonCode = '';
				if ($GLOBALS['BE_USER']->isAdmin()) {
					$linkToDce = 'alt_doc.php?&returnUrl=close.html&edit[tx_dce_domain_model_dce][' . $dceUid . ']=edit';
					$pathToImage = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('dce') . 'Resources/Public/Icons/docheader_icon.png';
					$titleTag = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('quickDcePopup', 'Dce');
					$buttonCode = '<div class="buttongroup"><a href="#" onclick="window.open(\'' . $linkToDce .
						'\', \'editDcePopup\', \'height=600,width=820,status=0,menubar=0,scrollbars=1\')"><img src="' .
						$pathToImage . '" alt="" title="' . $titleTag . '" /></a></div>';
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
		require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('dce') . 'Classes/Domain/Repository/DceRepository.php');
	}


	/**
	 * Adds stylesheet when editing dce instance. Not nice solved, but it works.
	 * @return string
	 */
	protected function getCustomStylesheet() {
		$file = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('dce') . 'Resources/Public/CSS/dceInstance.css';
		$content = file_get_contents($file);
		return '<style type="text/css">' . $content . '</style>';
	}
}