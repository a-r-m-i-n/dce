<?php
namespace ArminVieweg\Dce\ViewHelpers\Format;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Addcslashes Viewhelper
 *
 * @package ArminVieweg\Dce
 */
class AddcslashesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Add slashes to a given string using the php function "addcslashes".
     *
     * @param string $subject To add slashes to
     * @param string $charlist A list of characters to be escaped
     * @return string with slashes
     * @see http://www.php.net/manual/function.addcslashes.php
     */
    public function render($subject = null, $charlist = "'")
    {
        if ($subject === null) {
            $subject = $this->renderChildren();
        }
        return addcslashes($subject, $charlist);
    }
}
