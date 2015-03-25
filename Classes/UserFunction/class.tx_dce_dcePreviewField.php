<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is free software and is                          *
 *  | licensed under GNU General Public License.                                                                ♥php  *
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>                                                          */

/**
 * Preview of DCEs in backend
 *
 * @package DceTeam\Dce
 */
class tx_dce_dcePreviewField {
	/** PID where all DCE previews are temporary stored with */
	const DCE_PREVIEW_PID = -5;

	/**
	 * @param array $parameter unused
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $fObj
	 * @return string
	 */
	public function getPreview($parameter, \TYPO3\CMS\Backend\Form\FormEngine $fObj) {
		/** @var $fluidTemplate \DceTeam\Dce\Utility\FluidTemplate */
		$fluidTemplate = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('DceTeam\Dce\Utility\FluidTemplate');

		$fluidTemplate->setLayoutRootPath(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:dce/Resources/Private/Layouts/'));
		$fluidTemplate->setPartialRootPath(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:dce/Resources/Private/Partials/'));
		$fluidTemplate->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:dce/Resources/Private/Templates/DceUserFields/DcePreview.html'));

		$dceUid = $this->getDceUid($fObj);
		$fluidTemplate->assign('dceUid', $dceUid);

		$previewItemUid = $this->createDceInstancePreviewItem($dceUid);
		$fluidTemplate->assign('previewItemUid', $previewItemUid);

		return $fluidTemplate->render();
	}

	/**
	 * Creates a new content item as instance of DCE with given uid, for preview purposes.
	 *
	 * @param int $dceUid
	 * @return int uid of created item
	 */
	protected function createDceInstancePreviewItem($dceUid) {
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_content', array(
			'pid' => self::DCE_PREVIEW_PID,
			'tstamp' => time(),
			'crdate' => time(),
			'list_type' => '',
			'CType' => 'dce_dceuid' . $dceUid,
		));
		return $GLOBALS['TYPO3_DB']->sql_insert_id();
	}

	/**
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $fObj
	 * @return int
	 */
	protected function getDceUid(\TYPO3\CMS\Backend\Form\FormEngine $fObj) {
		$dceRecord = current($fObj->cachedTSconfig);
		return intval($dceRecord['_THIS_UID']);
	}
}