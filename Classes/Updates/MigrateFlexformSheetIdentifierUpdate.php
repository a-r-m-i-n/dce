<?php
namespace ArminVieweg\Dce\Updates;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Migrate Flexform sheet identifier
 *
 * In the past DCE named tabs in flexform configuration like this:
 * <sheet0></sheet0>
 *
 * But this has the effect that all your data is broken, when you change
 * the order of tabs in a DCE. Now the sheets have a named identifier. You
 * can set the identifier in the variable field which is also visible for
 * tab fields, now.
 *
 * The flexform configuration looks like this now:
 * <sheet.tabGeneral></sheet.tabGeneral>
 *
 * The very first sheet has the identifier/variable "tabGeneral" by default.
 *
 * Please migrate the field database relations first, before executing this update!
 *
 * @package ArminVieweg\Dce
 */
class MigrateFlexformSheetIdentifierUpdate extends AbstractUpdate
{
    /**
     * @var string
     */
    protected $title = 'EXT:dce Migrate flexform sheet identifiers';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        $tabsWithoutIdentifier = $this->getUpdatableDceFields();
        $contentElementsWithWrongXml = $this->getUpdatableContentElements();

        $description .= 'There are <b>' . count($tabsWithoutIdentifier) . ' tab fields</b> without identifier and ' .
                        '<b>' . count($contentElementsWithWrongXml) . ' content elements</b> with old xml structure ' .
                        'existing.<br>' .
                        'Caution! Please make sure that you\'ve migrated the mm-relation of dce fields to 1:n ' .
                        'before executing this update wizard.<br><br>';

        return count($tabsWithoutIdentifier) > 0 || count($contentElementsWithWrongXml) > 0;
    }

    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries Queries done in this update
     * @param mixed &$customMessages Custom messages
     * @return bool Whether everything went smoothly or not
     * @TODO Refactor me
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        $this->getDatabaseConnection()->store_lastBuiltQuery = true;

        $tabsWithoutIdentifier = $this->getUpdatableDceFields();
        $tabsGroupedByDce = [];
        foreach ($tabsWithoutIdentifier as $tabRow) {
            $tabRow['variable'] = 'tab' . $this->convertFieldTitleToVariable($tabRow['title']);
            $this->getDatabaseConnection()->exec_UPDATEquery(
                'tx_dce_domain_model_dcefield',
                'uid=' . $tabRow['uid'],
                [
                    'variable' => $tabRow['variable']
                ]
            );
            $this->storeLastQuery($dbQueries);

            if (!isset($tabsGroupedByDce[$tabRow['parent_dce']])) {
                $tabsGroupedByDce[$tabRow['parent_dce']] = [];
                if ($tabRow['sorting'] !== '1') {
                    $tabsGroupedByDce[$tabRow['parent_dce']][] = ['variable' => 'tabGeneral'];
                }
            }
            $tabsGroupedByDce[$tabRow['parent_dce']][] = $tabRow;
        }

        /** @var \TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools $flexFormTools */
        $flexFormTools = GeneralUtility::makeInstance('TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools');
        foreach ($tabsGroupedByDce as $dceUid => $tabs) {
            $contentElements = $this->getDatabaseConnection()->exec_SELECTgetRows(
                '*',
                'tt_content',
                'CType="dce_dceuid' . $dceUid . '" AND deleted=0'
            );
            $this->storeLastQuery($dbQueries);

            foreach ($contentElements as $contentElement) {
                $flexformData = GeneralUtility::xml2array($contentElement['pi_flexform']);
                $i = 0;
                $newFlexformData = ['data' => []];
                if (!empty($flexformData['data'])) {
                    foreach ($flexformData['data'] as $sheetIdentifier => $sheetData) {
                        if ($i === 0) {
                            // First sheet
                            $newFlexformData['data']['sheet.tabGeneral'] = $sheetData;
                            $i++;
                            continue;
                        }
                        if (strpos($sheetIdentifier, '.') === false) {
                            $newFlexformData['data']['sheet.' . $tabs[$i]['variable']] = $sheetData;
                        } else {
                            $newFlexformData['data'][$sheetIdentifier] = $sheetData;
                        }
                        $i++;
                    }
                }

                $this->getDatabaseConnection()->exec_UPDATEquery(
                    'tt_content',
                    'uid=' . $contentElement['uid'],
                    [
                        'pi_flexform' => $flexFormTools->flexArray2Xml($newFlexformData, true)
                    ]
                );
                $this->storeLastQuery($dbQueries);
            }
        }

        $updatableContentElements = $this->getUpdatableContentElements();
        foreach ($updatableContentElements as $contentElement) {
            $flexformData = GeneralUtility::xml2array($contentElement['pi_flexform']);
            $newFlexformData = ['data' => []];
            if (!empty($flexformData['data'])) {
                foreach ($flexformData['data'] as $sheetIdentifier => $sheetData) {
                    if ($sheetIdentifier === 'sheet0') {
                        $newFlexformData['data']['sheet.tabGeneral'] = $sheetData;
                    } else {
                        $newFlexformData['data'][$sheetIdentifier] = $sheetData;
                    }
                }
            }
            $this->getDatabaseConnection()->exec_UPDATEquery(
                'tt_content',
                'uid=' . $contentElement['uid'],
                [
                    'pi_flexform' => $flexFormTools->flexArray2Xml($newFlexformData, true)
                ]
            );
            $this->storeLastQuery($dbQueries);
        }
        return true;
    }

    /**
     * Returns tabs (DceFields with type=1) without set variable field
     *
     * @return array DceField rows
     */
    protected function getUpdatableDceFields()
    {
        return $this->getDatabaseConnection()->exec_SELECTgetRows(
            '*',
            'tx_dce_domain_model_dcefield',
            'type=1 AND variable="" AND deleted=0',
            '',
            'sorting asc'
        );
    }

    /**
     * Returns content elements, based on DCE, with old sheet identifier
     *
     * @return array tt_content rows
     */
    protected function getUpdatableContentElements()
    {
        return $this->getDatabaseConnection()->exec_SELECTgetRows(
            '*',
            'tt_content',
            'CType LIKE "dce_dceuid%" AND deleted=0 AND pi_flexform LIKE "%<sheet index=\"sheet0\">%"'
        );
    }

    /**
     * Converts given fieldTitle to variable name (in UpperCamelCase).
     *
     * @param string $fieldTitle The title you want to convert
     * @return string The converted variable name in UpperCamelCase
     */
    protected function convertFieldTitleToVariable($fieldTitle)
    {
        /** @var \TYPO3\CMS\Core\Charset\CharsetConverter $charsetConverter */
        $charsetConverter = GeneralUtility::makeInstance('TYPO3\CMS\Core\Charset\CharsetConverter');
        $variable = $charsetConverter->specCharsToASCII('utf-8', $fieldTitle);
        $variable = preg_replace('/[^A-Z0-9]/i', '_', $variable);
        return GeneralUtility::underscoredToUpperCamelCase($variable);
    }
}
