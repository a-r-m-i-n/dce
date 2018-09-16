<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2018 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Returns the url of current page
 */
class ThisUrlViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('showHost', 'boolean', 'If TRUE the hostname will be included');
        $this->registerArgument('showRequestedUri', 'boolean', 'If TRUE the requested uri will be included');
        $this->registerArgument('urlencode', 'boolean', 'If TRUE the whole result will be URI encoded');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $url = '';
        if ($arguments['showHost']) {
            $url .= ($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $url .= $_SERVER['SERVER_NAME'];
        }
        if ($arguments['showRequestedUri']) {
            $url .= $_SERVER['REQUEST_URI'];
        }
        if ($arguments['urlencode']) {
            $url = urlencode($url);
        }
        return $url;
    }
}
