<?php

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * A view helper for creating URIs to resources.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <script type="text/javascript">{dce:include.resource(path:'js/javascript.js')}</script>
 * </code>
 * <output>
 * <script type="text/javascript">alert('8)');</script>
 * </output>
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_Dce_ViewHelpers_Include_ResourceViewHelper extends Tx_Fluid_ViewHelpers_Uri_ResourceViewHelper {

	/**
	 * Render the content to the resource. The filename is used from child content.
	 *
	 * @param string $path The path and filename of the resource (relative to Public resource directory of the extension).
	 * @param string $extensionName Target extension name. If not set, the current extension name will be used
	 *
	 * @return string The content of the resouce
	 * @see Tx_Fluid_ViewHelpers_Uri_ResourceViewHelper
	 */
	public function render($path, $extensionName = NULL) {
		$fileUri = PATH_typo3conf . parent::render($path, $extensionName, FALSE);
		if ($fileUri && file_exists($fileUri)) {
			return file_get_contents($fileUri);
		}
	}
}
?>
