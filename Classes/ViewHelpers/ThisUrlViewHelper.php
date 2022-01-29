<?php

namespace T3\Dce\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the url of current page.
 */
class ThisUrlViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('showHost', 'boolean', 'If TRUE the hostname will be included');
        $this->registerArgument(
            'showRequestedUri',
            'boolean',
            'If TRUE the requested uri will be included',
            false,
            true
        );
        $this->registerArgument('urlencode', 'boolean', 'If TRUE the whole result will be URI encoded');
    }

    /**
     * @return string
     */
    public function render()
    {
        $url = '';
        if ($this->arguments['showHost']) {
            $url .= ($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $url .= $_SERVER['SERVER_NAME'];
        }
        if ($this->arguments['showRequestedUri']) {
            $url .= $_SERVER['REQUEST_URI'];
        }
        if ($this->arguments['urlencode']) {
            $url = urlencode($url);
        }

        return $url;
    }
}
