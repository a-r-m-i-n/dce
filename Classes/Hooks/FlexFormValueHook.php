<?php

namespace T3\Dce\Hooks;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Domain\Repository\DceRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class FlexFormValueHook
{
    public function checkFlexFormValue_beforeMerge(
        DataHandler $dataHandler,
        array &$currentValueArray,
        array &$submittedValueArray
    ): void {
        if (!DceRepository::extractUidFromCTypeOrIdentifier(
            $dataHandler->checkValue_currentRecord['CType'] ?? null
        )) {
            return;
        }

        $this->removeEmptySectionContainers($currentValueArray);
        $this->removeEmptySectionContainers($submittedValueArray);
    }

    private function removeEmptySectionContainers(array &$flexFormData): void
    {
        if (!is_array($flexFormData['data'] ?? null)) {
            return;
        }

        foreach ($flexFormData['data'] as &$sheetValues) {
            if (!is_array($sheetValues)) {
                continue;
            }
            foreach ($sheetValues as &$languageValues) {
                if (!is_array($languageValues)) {
                    continue;
                }
                foreach ($languageValues as &$fieldValues) {
                    if (!is_array($fieldValues) || !is_array($fieldValues['el'] ?? null)) {
                        continue;
                    }
                    foreach ($fieldValues['el'] as $containerIdentifier => $containerValues) {
                        if (!is_array($containerValues)) {
                            unset($fieldValues['el'][$containerIdentifier]);
                            continue;
                        }
                        unset($containerValues['_ACTION']);
                        if ([] === $containerValues) {
                            unset($fieldValues['el'][$containerIdentifier]);
                        }
                    }
                }
                unset($fieldValues);
            }
            unset($languageValues);
        }
        unset($sheetValues);
    }
}
