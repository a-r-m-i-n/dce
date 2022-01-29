<?php

namespace T3\Dce\ViewHelpers\Be;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Utility\BackendModuleLinkUtility;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This view helper returns a link to module in TYPO3 backend.
 */
class ModuleLinkViewHelper extends AbstractViewHelper
{
    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('module', 'string', 'Name of module');
        $this->registerArgument('parameter', 'string', 'Query string');
    }

    /**
     * Resolve user name from backend user id.
     *
     * @return string Created module link
     *
     * @throws RouteNotFoundException
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $parameters = GeneralUtility::explodeUrl2Array($arguments['parameter']);

        return BackendModuleLinkUtility::getModuleUrl($arguments['module'], $parameters);
    }
}
