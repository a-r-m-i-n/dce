<?php
namespace T3\Dce\ViewHelpers\Be;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */

/**
 * Table list view helper
 */
class TableListViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\TableListViewHelper
{
    /**
     * @return string the rendered record list
     * @see localRecordList
     */
    public function render()
    {
        if (!\is_object($GLOBALS['SOBE'])) {
            $GLOBALS['SOBE'] = new \stdClass();
        }
        $this->getDocInstance();

        return parent::render();
    }
}
