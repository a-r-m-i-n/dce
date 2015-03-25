<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is free software and is                          *
 *  | licensed under GNU General Public License.                                                                ♥php  *
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>                                                          */

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('dce') . '/Classes/Hooks/tx_saveDce.php');

/**
 * Update content element via ajax request
 *
 * @package DceTeam\Dce
 * @TODO: Remove that!
 */
class tx_update_contentelement {

	public function updateContentElement() {
		$uid = intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uid'));
		$dceUid = intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('dceUid'));

		/** @var $tx_saveDce tx_saveDce */
		$tx_saveDce = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_saveDce');
		$updatedPreviewFields = $tx_saveDce->ajaxGenerateDcePreview($uid, $dceUid);

		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tt_content',
			'uid=' . $uid,
			$updatedPreviewFields
		);

		echo json_encode($updatedPreviewFields);
	}
}