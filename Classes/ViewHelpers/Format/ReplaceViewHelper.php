<?php
namespace ArminVieweg\Dce\ViewHelpers\Format;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2018 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Performs str_replace on given subject
 *
 */
class ReplaceViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @param string $search
     * @param string $replace
     * @param string|null $subject
     * @return string
     */
    public function render($search, $replace, $subject = null)
    {
        if ($subject === null) {
            $subject = (string) $this->renderChildren();
        }
        return str_replace($search, $replace, $subject);
    }
}
