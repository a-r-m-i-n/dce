<?php
namespace ArminVieweg\Dce\ViewHelpers\Uri;

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
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Uri\ImageViewHelper
{

    /**
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @param null|string $src
     * @param null|string $width
     * @param null|string $height
     * @param null|string $minWidth
     * @param null|string $minHeight
     * @param null|string $maxWidth
     * @param null|string $maxHeight
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
        $image = null
    ) {
        return parent::render($src, $image, $width, $height, $minWidth, $minHeight, $maxWidth, $maxHeight);
    }
}
