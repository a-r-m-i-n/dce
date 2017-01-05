<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Explode viewhelper which uses the trimExplode method of
 * \TYPO3\CMS\Core\Utility\GeneralUtility
 *
 * @package ArminVieweg\Dce
 */
class ExplodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Splits a string to an array.
     *
     * @param string $subject String to explode.
     * @param string $delimiter Char or string to split the string into pieces. Default is a comma sign(,).
     * @param bool $removeEmpty If TRUE empty items will be removed.
     * @return array Exploded parts
     */
    public function render($subject = null, $delimiter = ',', $removeEmpty = true)
    {
        if ($subject === null) {
            $subject = $this->renderChildren();
        }

        switch ($delimiter) {
            case '\n':
                $delimiter = "\n";
                break;
            case '\r':
                $delimiter = "\r";
                break;
            case '\r\n':
                $delimiter = "\r\n";
                break;
            case '\t':
                $delimiter = "\t";
                break;
            default:
        }
        return \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode($delimiter, $subject, $removeEmpty);
    }
}
