<?php

namespace T3\Dce\EventListener;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Linkvalidator\Event\BeforeRecordIsAnalyzedEvent;
use TYPO3\CMS\Linkvalidator\LinkAnalyzer;

class LinkAnalyserEventListener
{
    public function beforeAnalyzeRecord(
        array $results,
        array $record,
        string $table,
        array $fields,
        LinkAnalyzer $linkAnalyser
    ): array {
        if ('tt_content' === $table && !empty($record['pi_flexform'])) {
            $rawRecord = BackendUtility::getRecord('tt_content', $record['uid'], '*');
            if (!str_starts_with($rawRecord['CType'], 'dce_')) {
                return [$results, $record, $table, $fields, $linkAnalyser];
            }
            /** @var array|string $flexformArray */
            $flexformArray = GeneralUtility::xml2array($record['pi_flexform']);
            if (is_string($flexformArray)) {
                return [$results, $record, $table, $fields, $linkAnalyser];
            }
            $flexformData = ArrayUtility::flatten($flexformArray);

            $newFlexformContent = '';
            foreach ($flexformData as $key => $fieldValue) {
                if (!str_ends_with($key, 'vDEF')) {
                    continue;
                }

                if (!empty($fieldValue) && !is_numeric($fieldValue) && str_contains($fieldValue, '://')) {
                    // Check for typolink (string, without new lines or < > signs)
                    if (\is_string($fieldValue)
                        && !str_contains($fieldValue, "\n")
                        && !str_contains($fieldValue, ' ')
                        && !str_contains($fieldValue, '<')
                        && !str_contains($fieldValue, '>')
                    ) {
                        $fieldValue = '<a href="' . $fieldValue . '">Typolink</a>';
                    }
                    $newFlexformContent .= $fieldValue . "\n\n";
                }
            }
            $record['pi_flexform'] = $newFlexformContent;
            $GLOBALS['TCA'][$table]['columns']['pi_flexform']['config']['softref'] = 'typolink_tag,url';
        }

        return [$results, $record, $table, $fields, $linkAnalyser];
    }

    public function dispatchEvent(BeforeRecordIsAnalyzedEvent $event): void
    {
        [$results, $record] = $this->beforeAnalyzeRecord(
            $event->getResults(),
            $event->getRecord(),
            $event->getTableName(),
            $event->getFields(),
            $event->getLinkAnalyzer()
        );
        $event->setResults($results);
        $event->setRecord($record);
    }
}
