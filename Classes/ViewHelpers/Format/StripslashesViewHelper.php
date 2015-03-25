<?php
namespace DceTeam\Dce\ViewHelpers\Format;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is free software and is                          *
 *  | licensed under GNU General Public License.                                                                ♥php  *
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>                                                          */

/**
 * Stripslashes Viewhelper
 *
 * @package DceTeam\Dce
 */
class  StripslashesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * Add slashes to a given string using the php function "stripslashes".
	 *
	 * @param string $subject To remove slashes to
	 * @param bool $performTrim If TRUE a trim will be made on subject before stripping slashes
	 * @return string without slashes
	 * @see http://www.php.net/manual/function.addcslashes.php
	 */
	public function render($subject = NULL, $performTrim = FALSE) {
		if ($subject === NULL) {
			$subject = $this->renderChildren();
		}
		if ($performTrim === TRUE) {
			$subject = trim($subject);
		}
		return stripslashes($subject);
	}
}