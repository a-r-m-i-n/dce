<?php
namespace ArminVieweg\Dce\ViewHelpers\Format;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2018 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Wraps given subject with CDATA.
 * Good for fluid templates which render XML.
 */
class CdataViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @param string $subject
     * @return string
     */
    public function render($subject = null)
    {
        if ($subject === null) {
            $subject = (string) $this->renderChildren();
        }
        return '<![CDATA[' . $subject . ']]>';
    }
}
