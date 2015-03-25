<?php
namespace DceTeam\Dce\ViewHelpers;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is free software and is                          *
 *  | licensed under GNU General Public License.                                                                ♥php  *
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>                                                          */

/**
 * This view helper returns the url of current page
 *
 * @package DceTeam\Dce
 */
class ThisUrlViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * Returns the current url
	 *
	 * @param bool $showHost If TRUE the hostname will be included
	 * @param bool $showRequestedUri If TRUE the requested uri will be included
	 * @param bool $urlencode If TRUE the whole result will be URI encoded
	 * @return string url
	 */
	public function render($showHost = TRUE, $showRequestedUri = TRUE, $urlencode = FALSE) {
		$url = '';

		if ($showHost) {
			$url .= ($_SERVER['HTTPS']) ? 'https://' : 'http://';
			$url .= $_SERVER['SERVER_NAME'];
		}
		if ($showRequestedUri) {
			$url .= $_SERVER['REQUEST_URI'];
		}
		if ($urlencode) {
			$url = urlencode($url);
		}

		return $url;
	}
}