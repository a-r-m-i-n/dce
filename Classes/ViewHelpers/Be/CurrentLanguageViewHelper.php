<?php

namespace T3\Dce\ViewHelpers\Be;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Compatibility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Gets the current language key as string.
 */
class CurrentLanguageViewHelper extends AbstractViewHelper
{
    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        if (Compatibility::isFrontendMode()) {
            if (isset($GLOBALS['TSFE']->config['config']['language'])) {
                return $GLOBALS['TSFE']->config['config']['language'];
            }
        } elseif (\strlen($GLOBALS['BE_USER']->uc['lang']) > 0) {
            return $GLOBALS['BE_USER']->uc['lang'];
        }

        return 'default';
    }
}
