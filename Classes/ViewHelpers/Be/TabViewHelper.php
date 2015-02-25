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
 * This class provides the usage of backend tabs inside of backend container.
 * Full example of tab and tabContainer usage in fluid:
 *
 * <dce:be.tabContainer>
 *    <dce:be.tab title="First Tab">
 *         Content of first Tab
 *    </dce:be.tab>
 * </dce:be.tabContainer>
 *
 * @author     Armin Rüdiger Vieweg <armin@v.ieweg.de>
 * @author     Benjamin Schulte <benj@minschulte.de>
 * @copyright  2011 Copyright belongs to the respective authors
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TabViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper  {
	/**
	 * @var \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext
	 */
	protected $renderingContext;

	/**
	 * Sets the rendering context which needs to be passed on to child nodes.
	 *
	 * @param \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext $renderingContext the rendering context to use
	 * @return void
	 */
	public function setRenderingContext(\TYPO3\CMS\Fluid\Core\Rendering\RenderingContext $renderingContext) {
		parent::setRenderingContext($renderingContext);
		$this->renderingContext = $renderingContext;
	}

	/**
	 * Renders a tab container.
	 *
	 * @param string $title title for the tab
	 *
	 * @return the whole tab container construct
	 */
	public function render($title) {
		$result = $this->renderChildren();
		$this->renderingContext->getViewHelperVariableContainer()
			->addOrUpdate('DceTeam\Dce\ViewHelpers\Be\TabViewHelper', 'title', $title);
		return $result;
	}
}