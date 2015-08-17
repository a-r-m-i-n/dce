<?php
namespace ArminVieweg\Dce\XClass;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * XClass LiveSearch
 *
 * @package ArminVieweg\Dce
 */
class LiveSearch extends \TYPO3\CMS\Backend\Search\LiveSearch\LiveSearch
{
    /**
     * Includes DCE content elements to CTypes which should get search by field "bodytext"
     *
     * @param string $tableName
     * @param array $fieldsToSearchWithin
     * @return mixed|string
     */
    protected function makeQuerySearchByTable($tableName, array $fieldsToSearchWithin)
    {
        $data = parent::makeQuerySearchByTable($tableName, $fieldsToSearchWithin);
        $searchString = 'CType=\'text\' OR CType=\'textpic\'';
        $dceAppendix = ' OR CType LIKE \'dce_%\'';
        if (strpos($data, $searchString) !== false) {
            $data = str_replace($searchString, $searchString . $dceAppendix, $data);
        }
        return $data;
    }
}
