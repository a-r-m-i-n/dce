<?php
namespace ArminVieweg\Dce\ViewHelpers\Uri;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is free software and is                          *
 *  | licensed under GNU General Public License.                                                                ♥php  *
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>                                                          */

/**
 * Image viewhelper which works for preview texts as well
 *
 * @package ArminVieweg\Dce
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Uri\ImageViewHelper {

	/**
	 * Resizes a given image (if required) and renders the respective img tag
	 *
	 * @param string $src
	 * @param string $width width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param string $height height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param int $minWidth minimum width of the image
	 * @param int $minHeight minimum height of the image
	 * @param int $maxWidth maximum width of the image
	 * @param int $maxHeight maximum height of the image
	 * @param \TYPO3\CMS\Core\Resource\FileInterface|\TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder $image
	 * @return string rendered tag.
	 */
	public function render($src, $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL, $image = NULL) {
		$imageUri = parent::render($src, $image, $width, $height, $minWidth, $minHeight, $maxWidth, $maxHeight);
		if (TYPO3_MODE === 'BE' && strpos($imageUri, '../') === 0) {
				// Make image uri absolute
				$imageUri = substr($imageUri, 2);
		}
		return $imageUri;
	}
}