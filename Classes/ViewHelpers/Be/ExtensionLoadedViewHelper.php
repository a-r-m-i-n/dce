<?php
namespace ArminVieweg\Dce\ViewHelpers\Be;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Checks if extension with given key is loaded
 *
 * @package ArminVieweg\Dce
 */
class ExtensionLoadedViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper
{
    /**
     * Checks if extension with given key is loaded
     *
     * @param string $key Extension key
     * @return bool TRUE if extension is loaded. Otherwise false.
     */
    public function render($key)
    {
        return (int) \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($key);
    }
}
