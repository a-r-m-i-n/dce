<?php
namespace ArminVieweg\Dce\ViewHelpers\Be;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * This view helper adds inline css
 *
 * @package ArminVieweg\Dce
 */
class IncludeCssFileViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper
{
    /**
     * Plain HTML should be returned, no output escaping allowed
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Load css file and put contents to inline <style> tag
     *
     * @param string $path to css file
     * @return string <style> HTML tag
     */
    public function render($path)
    {
        $absPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($path);
        if ($absPath) {
            $contents = file_get_contents($absPath);
            return '<style>' . $contents . '</style>';
        }
        return '<script type="text/javascript">console.error("File ' . $path . ' not found.")</script>';
    }
}
