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
     * @var array Defines after which cache clearing the DCE cache
     *            should get cleared as well
     */
    protected $clearedCacheTypes = array(
        'all', 'temp_cached', 'system'
    );


    /**
     * Clears the dce cache files
     *
     * @param array $params
     * @return void
     */
    public function clearDceCache(array $params)
    {
        if (in_array($params['cacheCmd'], $this->clearedCacheTypes)) {
            \ArminVieweg\Dce\Cache::clear();
        }
    }
}
