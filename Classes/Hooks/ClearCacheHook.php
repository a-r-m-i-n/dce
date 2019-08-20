<?php
namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2019 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Components\ContentElementGenerator\CacheManager;

/**
 * Class ClearCacheHook
 */
class ClearCacheHook
{

    public function flushDceCache()
    {
        $cacheManager = CacheManager::makeInstance();
        $cacheManager->flush();
    }
}
