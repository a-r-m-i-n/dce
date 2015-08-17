<?php
namespace ArminVieweg\Dce\ViewHelpers\Be;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012 Benjamin Schulte <benj@minschulte.de>
 *  |     2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * This class provides the usage of backend tabs inside of backend container.
 * Full example of tab and tabContainer usage in fluid:
 *
 * <dce:be.tabContainer>
 *    <dce:be.tab title="First Tab">
 *         Content of first Tab
 *    </dce:be.tab>
 * </dce:be.tabContainer>
 *
 * @package ArminVieweg\Dce
 */
class TabViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @var RenderingContext
     */
    protected $renderingContext;

    /**
     * Sets the rendering context which needs to be passed on to child nodes.
     *
     * @param RenderingContext $renderingContext the rendering context to use
     * @return void
     */
    public function setRenderingContext(RenderingContext $renderingContext)
    {
        parent::setRenderingContext($renderingContext);
        $this->renderingContext = $renderingContext;
    }

    /**
     * Renders a tab container.
     *
     * @param string $title title for the tab
     * @return string the whole tab container construct
     */
    public function render($title)
    {
        $result = $this->renderChildren();
        $this->renderingContext->getViewHelperVariableContainer()
            ->addOrUpdate('ArminVieweg\Dce\ViewHelpers\Be\TabViewHelper', 'title', $title);
        return $result;
    }
}
