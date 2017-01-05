<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PageTS utility
 *
 * @package ArminVieweg\Dce
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
     * @param string $path separated with dots. e.g.: "tx_dce.defaults.example"
     * @param mixed $default Optional. Value which should be returned if path is not existing or value empty
     * @param int $id Optional. Set id of page from which PageTS should get loaded
     * @return mixed
     */
    public static function get($path, $default = null, $id = 0)
    {
        $id = $id ?: GeneralUtility::_GP('id');
        if (!isset(static::$pageTsContent[$id])) {
            /** @var \TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService */
            $typoScriptService = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Service\TypoScriptService');
            static::$pageTsContent[$id] = $typoScriptService->convertTypoScriptArrayToPlainArray(
                BackendUtility::getPagesTSconfig($id)
            );
        }
        try {
            $value = \TYPO3\CMS\Core\Utility\ArrayUtility::getValueByPath(static::$pageTsContent[$id], $path, '.');
        } catch (\Exception $e) {
            return $default;
        }
        return !is_null($default) && empty($value) ? $default : $value;
    }
}
