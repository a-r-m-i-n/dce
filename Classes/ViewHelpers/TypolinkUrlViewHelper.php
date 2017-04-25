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
 * It returns just the URL.
 *
 * @package ArminVieweg\Dce
 * @deprecated Removed in next major version
 */
class TypolinkUrlViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Uri\TypolinkViewHelper
{
    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(
            'Do not use dce:typolinkUrl() viewhelper anymore. Use f:uri.typolink() instead.'
        );
        return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }
}
