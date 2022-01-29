<?php

namespace T3\Dce\UserFunction\CustomLabels;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Domain\Model\DceField;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\LanguageService;

/**
 * Extends TCA label of fields with variable key.
 */
class DceFieldLabel
{
    /**
     * User function to get custom labels for DCE fields
     * to show available variable name after title.
     *
     * It also respects section fields and child fields inside of sections
     * and marks them with a blue "n", which indicates that the section
     * variable contains an array with n records.
     */
    public function getLabel(array &$parameter): void
    {
        if (!isset($parameter['row']['variable']) || empty($parameter['row']['variable'])) {
            $parameter['title'] = LanguageService::sL($parameter['row']['title']);

            return;
        }
        if (!$this->isSectionChildField($parameter)) {
            if (!$this->isSectionField($parameter)) {
                if ($this->isTab($parameter)) {
                    // Tab
                    $parameter['title'] = LanguageService::sL($parameter['row']['title']);
                } else {
                    // Standard field
                    $parameter['title'] = LanguageService::sL($parameter['row']['title']) .
                        ' - {field.' . $parameter['row']['variable'] . '}';
                }
            } else {
                $parameter['title'] = LanguageService::sL($parameter['row']['title']) .
                    ' - {field.' . $parameter['row']['variable'] . '.n}';
            }
        } else {
            // Section child field
            if (is_numeric($parameter['row']['parent_field'])) {
                $parentFieldRow = $this->getDceFieldRecordByUid($parameter['row']['parent_field']);
            } else {
                $parentFieldRow = ['variable' => $parameter['parent']['uid']];
            }
            $parameter['title'] = LanguageService::sL($parameter['row']['title']) .
                ' - {field.' . $parentFieldRow['variable'] . '.n.' . $parameter['row']['variable'] . '}';
        }
    }

    /**
     * Translates title of DCEs itself.
     */
    public function getLabelDce(array &$parameter): void
    {
        $parameter['title'] = LanguageService::sL($parameter['row']['title']);
    }

    /**
     * Checks if given parameters, belonging to a DCE field, is a
     * child field of section.
     *
     * @return bool TRUE if given field parameters are child field of section
     */
    protected function isSectionChildField(array $parameter): bool
    {
        return !empty($parameter['row']['parent_field']);
    }

    /**
     * Checks if given parameters, belonging to a DCE field, is a
     * section field.
     */
    protected function isSectionField(array $parameter): bool
    {
        return DceField::TYPE_SECTION === (int)$parameter['row']['type'][0];
    }

    /**
     * Checks if given parameters, belonging to a DCE field, is a tab.
     */
    protected function isTab(array $parameter): bool
    {
        return DceField::TYPE_TAB === (int)$parameter['row']['type'][0];
    }

    /**
     * Get row of dce field of given uid (even for deleted fields).
     *
     * @return array|null dce field row
     */
    protected function getDceFieldRecordByUid(int $uid): ?array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dcefield');

        return $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch() ?: null;
    }
}
