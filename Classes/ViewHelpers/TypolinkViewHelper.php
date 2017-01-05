<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This view helper handles parameter strings using typolink function of TYPO3.
 * It creates the whole <a>-Tag.
 *
 * @package ArminVieweg\Dce
 */
class TypolinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Create a typolink.
     *
     * @param string $parameter Parameter string, which can be handled by
     *                          typolink functionality
     * @param string $subject Link text
     * @param string $class If set, overrides given class in parameter string.
     * @param string $target If set, overrides given target in parameter string.
     * @param string $title If set, overrides given title in parameter string.
     * @return string Rendered HTML <a>-tag
     */
    public function render($parameter, $subject = null, $class = null, $target = null, $title = null)
    {
        if ($subject === null) {
            $subject = $this->renderChildren();
        }

        /** @var $cObj \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer */
        $cObj = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
        if (!is_null($class) || !is_null($target) || !is_null($title)) {
            /** @var \ArminVieweg\Dce\Utility\TypoLinkCodecService $TypoLinkCodecService */
            $typoLinkCodecService = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\TypoLinkCodecService');
            $typolinkParameterParts = $typoLinkCodecService->decode($parameter);
            if (!is_null($class)) {
                $typolinkParameterParts['class'] = $class;
            }
            if (!is_null($target)) {
                $typolinkParameterParts['target'] = $target;
            }
            if (!is_null($title)) {
                $typolinkParameterParts['title'] = $title;
            }
            $parameter = $typoLinkCodecService->encode($typolinkParameterParts);
        }
        return $cObj->getTypoLink($subject, $parameter);
    }
}
