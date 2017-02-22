<?php
namespace ArminVieweg\Dce\ViewHelpers\Be;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * This view helper adds inline javascript
 *
 * @package ArminVieweg\Dce
 */
class IncludeJsFileViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper
{
    /**
     * Plain HTML should be returned, no output escaping allowed
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Load js file and put contents to inline <script> tag
     *
     * @param string $path to js file
     * @return string
     */
    public function render($path)
    {
        $absPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($path);
        if ($absPath) {
            $contents = file_get_contents($absPath);
            return '<script type="text/javascript">' . $contents . '</script>';
        }
        return '<script type="text/javascript">console.error("File ' . $path . ' not found.")</script>';
    }
}
