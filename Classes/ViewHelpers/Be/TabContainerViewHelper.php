<?php
namespace DceTeam\Dce\ViewHelpers\Be;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2015 Armin Rüdiger Vieweg <armin@v.ieweg.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This class provides a container for backend tabs. To create a container just use the following in a fluid template:
 * <dce:be.tabContainer></dce:be.tabContainer>
 *
 * The containers should only contain 'dce:be.tab's (see Be/TabViewHelper for usage).
 *
 * @author     Armin Rüdiger Vieweg <armin@v.ieweg.de>
 * @author     Benjamin Schulte <benj@minschulte.de>
 * @copyright  2011 Copyright belongs to the respective authors
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TabContainerViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper implements  \TYPO3\CMS\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface {
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
	 *
	 * @return void
	 */
	public function setChildNodes(array $childNodes) {
		$this->childNodes = $childNodes;
	}

	/**
	 * Gets title and contents of tabs and returns as array
	 *
	 * @return array array of tab contents and titles
	 */
	protected function getTabsDataArray() {
		$tabs = array();
		foreach ($this->childNodes as $childNode) {
			if ($childNode instanceof \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
				&& $childNode->getViewHelperClassName() === 'DceTeam\Dce\ViewHelpers\Be\TabViewHelper'
			) {
				$tab = array();
				$tab['content'] = $childNode->evaluate($this->getRenderingContext());
				$tab['label'] = $this->getRenderingContext()
					->getViewHelperVariableContainer()
					->get('DceTeam\Dce\ViewHelpers\Be\TabViewHelper', 'title');
				$tabs[] = $tab;
			}
		}
		return $tabs;
	}

	/**
	 * Renders a tab container with typo3 tce forms function getDynTabMenu
	 * @return string the whole tab container construct
	 */
	public function render() {
		$tabs = $this->getTabsDataArray();
		return \TYPO3\CMS\Backend\Form\FormEngine::getDynTabMenu($tabs, uniqid());
	}
}