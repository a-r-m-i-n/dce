<?php
namespace DceTeam\Dce\ViewHelpers;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is free software and is                          *
 *  | licensed under GNU General Public License.                                                                ♥php  *
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>                                                          */

/**
 * This view helper handles parameter strings using typolink function of TYPO3.
 * It creates the whole <a>-Tag.
 *
 * @package DceTeam\Dce
 */
class TypolinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * Create a typolink.
	 *
	 * @param string $parameter Parameter string, which can be handled by typolink functionality
	 * @param string $subject Link text
	 * @return string Rendered HTML <a>-tag
	 */
	public function render($parameter, $subject = NULL) {
		if ($subject === NULL) {
			$subject = $this->renderChildren();
		}

		/** @var $cObj \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer */
		$cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
		return $cObj->getTypoLink($subject, $parameter);
	}
}