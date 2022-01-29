<?php

namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Service\FlexFormService as CoreFlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Returns correct FlexFormService (TYPO3 8/9 compatibility).
 */
class FlexformService
{
    public static function get(): CoreFlexFormService
    {
        /** @var CoreFlexFormService $flexFormService */
        $flexFormService = GeneralUtility::makeInstance(CoreFlexFormService::class);

        return $flexFormService;
    }
}
