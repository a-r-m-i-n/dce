<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * This view helper returns the url of current page
 *
 * @package ArminVieweg\Dce
 */
class ThisUrlViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Returns the current url
     *
     * @param bool $showHost If TRUE the hostname will be included
     * @param bool $showRequestedUri If TRUE the requested uri will be included
     * @param bool $urlencode If TRUE the whole result will be URI encoded
     * @return string url
     */
    public function render($showHost = true, $showRequestedUri = true, $urlencode = false)
    {
        $url = '';

        if ($showHost) {
            $url .= ($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $url .= $_SERVER['SERVER_NAME'];
        }
        if ($showRequestedUri) {
            $url .= $_SERVER['REQUEST_URI'];
        }
        if ($urlencode) {
            $url = urlencode($url);
        }

        return $url;
    }
}
