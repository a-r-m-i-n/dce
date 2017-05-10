<?php
namespace ArminVieweg\Dce\ViewHelpers\Format;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * WrapWithCurlyBraces Viewhelper
 *
 * @package ArminVieweg\Dce
 */
class WrapWithCurlyBracesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Returns the given string with encircling curly braces
     *
     * @param string $subject
     * @param string $prepend
     * @param string $append
     * @return string
     */
    public function render($subject = null, $prepend = '', $append = '')
    {
        if ($subject === null) {
            $subject = $this->renderChildren();
        }
        return '{' . $prepend . $subject . $append . '}';
    }
}
