<?php
namespace ArminVieweg\Dce\ViewHelpers\Format;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Tiny Viewhelper
 *
 * @package ArminVieweg\Dce
 */
class TinyViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * We accept value and children interchangeably, thus we must disable children escaping.
     *
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * If we decode, we must not encode again after that.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Make the given source tiny. Removes all whitespaces but spaces.
     *
     * @param string $subject Code to make tiny
     * @return string Tiny code
     */
    public function render($subject = null)
    {
        if ($subject === null) {
            $subject = $this->renderChildren();
        }
        return str_replace(["\r", "\n", "\t"], '', $subject);
    }
}
