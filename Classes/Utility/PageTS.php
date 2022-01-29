<?php

namespace T3\Dce\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PageTS utility.
 */
class PageTS
{
    /**
     * @var array
     */
    protected static $pageTsContent = [];

    /**
     * Returns value of given path in pageTS of current page.
     *
     * @param string $path    separated with dots. e.g.: "tx_dce.defaults.example"
     * @param mixed  $default Optional. Value which should be returned if path is not existing or value empty
     * @param int    $id      Optional. Set id of page from which PageTS should get loaded
     *
     * @return mixed
     */
    public static function get(string $path, $default = null, int $id = 0)
    {
        $id = $id ?: GeneralUtility::_GP('id');
        if (!isset(static::$pageTsContent[$id])) {
            /** @var TypoScriptService $typoScriptService */
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            static::$pageTsContent[$id] = $typoScriptService->convertTypoScriptArrayToPlainArray(
                BackendUtility::getPagesTSconfig($id)
            );
        }
        try {
            $value = ArrayUtility::getValueByPath(static::$pageTsContent[$id], $path, '.');
        } catch (\Exception $e) {
            return $default;
        }

        return null !== $default && empty($value) ? $default : $value;
    }
}
