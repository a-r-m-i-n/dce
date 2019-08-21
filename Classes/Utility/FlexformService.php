<?php
namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Returns correct FlexFormService (TYPO3 8/9 compatibility)
 */
class FlexformService
{
    /**
     * @return object|\TYPO3\CMS\Core\Service\FlexFormService|\TYPO3\CMS\Extbase\Service\FlexFormService
     */
    public static function get()
    {
        if (!class_exists(\TYPO3\CMS\Core\Service\FlexFormService::class)) {
            return GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Service\FlexFormService::class);
        }
        return GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\FlexFormService::class);
    }
}
