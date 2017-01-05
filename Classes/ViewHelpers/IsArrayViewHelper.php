<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Checks if the given subject is an array
 *
 * @package ArminVieweg\Dce
 */
class IsArrayViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Checks if the given subject is an array
     *
     * @param mixed $subject to check if is array
     * @return bool True if given subject is an array, otherwise false
     */
    public function render($subject = null)
    {
        if ($subject === null) {
            $subject = $this->renderChildren();
        }
        return is_array($subject);
    }
}
