<?php
namespace ArminVieweg\Dce\ViewHelpers\Format;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Stripslashes Viewhelper
 *
 * @package ArminVieweg\Dce
 */
class StripslashesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Add slashes to a given string using the php function "stripslashes".
     *
     * @param string $subject To remove slashes to
     * @param bool $performTrim If TRUE a trim will be made on subject before stripping slashes
     * @return string without slashes
     * @see http://www.php.net/manual/function.addcslashes.php
     */
    public function render($subject = null, $performTrim = false)
    {
        if ($subject === null) {
            $subject = $this->renderChildren();
        }
        if ($performTrim === true) {
            $subject = trim($subject);
        }
        return stripslashes($subject);
    }
}
