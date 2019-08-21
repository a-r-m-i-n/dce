<?php
namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2019 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Components\ContentElementGenerator\CacheManager;

/**
 * Flushes DCE code cache files
 *
 * @see $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['dce']
 */
class ClearCacheHook
{
    public function flushDceCache()
    {
        CacheManager::makeInstance()->flush();
    }
}
