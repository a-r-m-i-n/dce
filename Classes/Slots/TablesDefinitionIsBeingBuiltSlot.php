<?php
namespace ArminVieweg\Dce\Slots;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\DatabaseUtility;

/**
 * Class TablesDefinitionIsBeingBuiltSlot
 * Signal defined in \TYPO3\CMS\Install\Service\SqlExpectedSchemaService
 *
 * @package ArminVieweg\Dce
 */
class TablesDefinitionIsBeingBuiltSlot
{
    /**
     *
     * @param array $sqlStrings
     * @return array
     */
    public function extendTtContentTable(array $sqlStrings)
    {
        if ($this->checkRequiredFieldsExisting()) {
            $sqlStrings[] = \ArminVieweg\Dce\Utility\FlexformToTcaMapper::getSql();
        }
        return array($sqlStrings);
    }

    /**
     * Checks if required fields are already in database.
     *
     * @return bool
     */
    protected function checkRequiredFieldsExisting()
    {
        $dbFields = DatabaseUtility::getDatabaseConnection()->admin_get_fields('tx_dce_domain_model_dcefield');
        $dbFieldNames = array_keys($dbFields);

        return in_array('map_to', $dbFieldNames) &&
               in_array('new_tca_field_name', $dbFieldNames) &&
               in_array('new_tca_field_type', $dbFieldNames);
    }
}
