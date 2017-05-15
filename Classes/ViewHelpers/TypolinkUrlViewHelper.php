<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * This view helper handles parameter strings using typolink function of TYPO3.
 * It returns just the URL.
 *
 * @package ArminVieweg\Dce
 * @deprecated This viewhelper is required for TYPO3 7.6 LTS. In TYPO3 8.7 please use "f:uri.typolink" instead.
 */
class TypolinkUrlViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Create a typolink and returns just the URL
     *
     * @param string $parameter Parameter string, which can be handled by typolink functionality
     * @return string url
     * @deprecated This viewhelper is required for TYPO3 7.6 LTS. In TYPO3 8.7 please use "f:uri.typolink" instead.
     */
    public function render($parameter)
    {
        /** @var $cObj \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer */
        $cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer'
        );
        return $cObj->getTypoLink_URL($parameter);
    }
}
