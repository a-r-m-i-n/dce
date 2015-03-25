<?php
namespace DceTeam\Dce\ViewHelpers\Be;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is free software and is                          *
 *  | licensed under GNU General Public License.                                                                ♥php  *
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>                                                          */

/**
 * This view helper adds css file to pagerenderer
 *
 * @package DceTeam\Dce
 */
class IncludeCssFileViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper {

	/**
	 * Adds css file to pagerenderer
	 *
	 * @param string $path to css file
	 * @return void
	 */
	public function render($path) {
		$doc = $this->getDocInstance();
		$pageRenderer = $doc->getPageRenderer();
		$pageRenderer->addCssFile($path);
	}
}