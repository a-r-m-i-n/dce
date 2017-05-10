<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * This view helper handles parameter strings using typolink function of TYPO3.
 * It creates the whole <a>-Tag.
 *
 * @package ArminVieweg\Dce
 * @deprecated Removed in next major version
 */
class TypolinkViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Link\TypolinkViewHelper
{
    /**
     * Render
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed|string
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(
            'Do not use dce:typolink() viewhelper anymore. Use f:link.typolink() instead.'
        );
        return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }
}
