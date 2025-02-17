<?php

namespace T3\Dce\EventListener;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Components\FlexformToTcaMapper\Mapper;
use T3\Dce\Utility\DatabaseUtility;

class TablesDefinitionIsBeingBuiltEventListener
{
    public function extendTtContentTable(array $sqlStrings): array
    {
        if ($this->checkRequiredFieldsExisting()) {
            $sqlStrings[] = Mapper::getSql();
        }

        return [$sqlStrings];
    }

    /**
     * Checks if required fields are already in database.
     */
    protected function checkRequiredFieldsExisting(): bool
    {
        $dbFields = DatabaseUtility::adminGetFields('tx_dce_domain_model_dcefield');

        return \array_key_exists('map_to', $dbFields)
               && \array_key_exists('new_tca_field_name', $dbFields)
               && \array_key_exists('new_tca_field_type', $dbFields);
    }

    /**
     * Used in TYPO3 10 (Event Dispatcher).
     *
     * @param \TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent $event
     */
    public function addSchema($event): void
    {
        $sqlData = $this->extendTtContentTable([]);
        foreach ($sqlData as $sql) {
            if (is_array($sql)) {
                $sql = reset($sql);
            }
            $event->addSqlData($sql);
        }
    }
}
