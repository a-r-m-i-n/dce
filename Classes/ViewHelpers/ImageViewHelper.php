<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Image viewhelper which works for preview texts as well
 *
 * @package ArminVieweg\Dce
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{

    /**
     * @param null|string $src
     * @param null|string $width
     * @param null|string $height
     * @param null|string $minWidth
     * @param null|string $minHeight
     * @param null|string $maxWidth
     * @param null|string $maxHeight
     * @param null|bool $treatIdAsReference
     * @return string rendered tag.
     * @deprecated Will be removed in 1.2.
     *             Use the standard f:image viewhelper instead.
     */
    public function render(
        $src,
        $width = null,
        $height = null,
        $minWidth = null,
        $minHeight = null,
        $maxWidth = null,
        $maxHeight = null,
        $treatIdAsReference = null
    ) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(
            'One of your DCEs uses the dce:image ViewHelper, which will be removed in version 1.2 of the DCE ' .
            'extension. Check frontend and backend templates and replace it with f:image.'
        );
        return parent::render($src, $width, $height, $minWidth, $minHeight, $maxWidth, $maxHeight, $treatIdAsReference);
    }
}
