<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Returns the given index of an array.
 *
 * @package ArminVieweg\Dce
 */
class ArrayGetIndexViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Returns the value of the given index in the given array. To make sure the indexes are numeric the array will be
     * converted. Named array keys will be overwritten by ascending index numbers (starting with 0).
     *
     * @param array $subject The array to get the value of
     * @param int|string $index Index of array. May be int or string. Default is zero (0).
     * @return mixed The value of the given array index
     */
    public function render(array $subject = null, $index = 0)
    {
        if ($subject === null) {
            $subject = $this->renderChildren();
        }
        $subject = array_values($subject);
        return $subject[$index];
    }
}
