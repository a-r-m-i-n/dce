<?php
namespace ArminVieweg\Dce\ViewHelpers\Be;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012 Benjamin Schulte <benj@minschulte.de>
 *  |     2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface;

/**
 * This class provides a container for backend tabs. To create a container just
 * use the following in a fluid template:
 * <dce:be.tabContainer></dce:be.tabContainer>
 *
 * The containers should only contain 'dce:be.tab's
 * (see Be/TabViewHelper for usage).
 *
 * @package ArminVieweg\Dce
 */
class TabContainerViewHelper extends AbstractViewHelper implements ChildNodeAccessInterface
{
    /**
     * All child nodes within this viewHelper
     *
     * @var array<\TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\AbstractNode>
     */
    protected $childNodes = array();

    /**
     * Setter for ChildNodes - as defined in ChildNodeAccessInterface
     *
     * @param array $childNodes Child nodes of this syntax tree node
     * @return void
     */
    public function setChildNodes(array $childNodes)
    {
        $this->childNodes = $childNodes;
    }

    /**
     * Gets title and contents of tabs and returns as array
     *
     * @return array array of tab contents and titles
     */
    protected function getTabsDataArray()
    {
        $tabs = array();
        foreach ($this->childNodes as $childNode) {
            if ($childNode instanceof \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
                && $childNode->getViewHelperClassName() === 'ArminVieweg\Dce\ViewHelpers\Be\TabViewHelper'
            ) {
                $tab = array();
                $tab['content'] = $childNode->evaluate($this->getRenderingContext());
                $tab['label'] = $this->getRenderingContext()
                    ->getViewHelperVariableContainer()
                    ->get('ArminVieweg\Dce\ViewHelpers\Be\TabViewHelper', 'title');
                $tabs[] = $tab;
            }
        }
        return $tabs;
    }

    /**
     * Renders a tab container with typo3 tce forms function getDynTabMenu
     *
     * @return string the whole tab container construct
     */
    public function render()
    {
        $tabs = $this->getTabsDataArray();
        return \TYPO3\CMS\Backend\Form\FormEngine::getDynTabMenu($tabs, uniqid());
    }
}
