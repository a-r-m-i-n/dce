<?php
namespace ArminVieweg\Dce\ViewHelpers\Be;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * Table list view helper
 *
 * @package ArminVieweg\Dce
 */
class TableListViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\TableListViewHelper
{
    /**
     * @param string $tableName
     * @param array $fieldList
     * @param null $storagePid
     * @param int $levels
     * @param string $filter
     * @param int $recordsPerPage
     * @param string $sortField
     * @param bool $sortDescending
     * @param bool $readOnly
     * @param bool $enableClickMenu
     * @param null $clickTitleMode
     * @param bool $alternateBackgroundColors
     * @return string the rendered record list
     * @see localRecordList
     */
    public function render(
        $tableName,
        array $fieldList = [],
        $storagePid = null,
        $levels = 0,
        $filter = '',
        $recordsPerPage = 0,
        $sortField = '',
        $sortDescending = false,
        $readOnly = false,
        $enableClickMenu = true,
        $clickTitleMode = null,
        $alternateBackgroundColors = false
    ) {

        if (!is_object($GLOBALS['SOBE'])) {
            $GLOBALS['SOBE'] = new \stdClass();
        }
        $this->getDocInstance();

        return parent::render(
            $tableName,
            $fieldList,
            $storagePid,
            $levels,
            $filter,
            $recordsPerPage,
            $sortField,
            $sortDescending,
            $readOnly,
            $enableClickMenu,
            $clickTitleMode,
            $alternateBackgroundColors
        );
    }
}
