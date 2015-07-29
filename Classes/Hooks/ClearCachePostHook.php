<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Clear Cache Hook
 *
 * @package ArminVieweg\Dce
 */
class ClearCachePostHook
{
    /**
     * Clears the dce cache files
     *
     * @param array $params
     * @return void
     */
    public function clearDceCache(array $params)
    {
        if ($params['cacheCmd'] === 'all' || $params['cacheCmd'] === 'temp_cached') {
            if (file_exists($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceLocalconfPath'])) {
                unlink($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceLocalconfPath']);
            }
            if (file_exists($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath'])) {
                unlink($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceExtTablesPath']);
            }
        }
    }
}
