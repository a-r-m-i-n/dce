<?php
namespace ArminVieweg\Dce\UserFunction\CustomLabels;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Domain\Model\DceField;
use ArminVieweg\Dce\Utility\LanguageService;

/**
 * Extends TCA label of fields with variable key
 *
 * @package ArminVieweg\Dce
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
     *
     * @param array $parameter
     * @return void
     */
    public function getLabel(&$parameter)
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
            $parameter['title'] = $parameter['row']['title'] .
                ' - {field.' . $parentFieldRow['variable'] . '.n.' . $parameter['row']['variable'] . '}';
        }
    }

    /**
     * Translates title of DCEs itself
     *
     * @param array $parameter
     * @return void
     */
    public function getLabelDce(&$parameter)
    {
        $parameter['title'] = LanguageService::sL($parameter['row']['title']);
    }

    /**
     * Checks if given parameters, belonging to a DCE field, is a
     * child field of section
     *
     * @param array $parameter
     * @return bool TRUE if given field parameters are child field of section
     */
    protected function isSectionChildField($parameter)
    {
        return  !empty($parameter['row']['parent_field']);
    }

    /**
     * Checks if given parameters, belonging to a DCE field, is a
     * section field.
     *
     * @param array $parameter
     * @return bool
     */
    protected function isSectionField($parameter)
    {
        return intval($parameter['row']['type'][0]) === DceField::TYPE_SECTION;
    }

    /**
     * Checks if given parameters, belonging to a DCE field, is a tab
     *
     * @param array $parameter
     * @return bool
     */
    protected function isTab($parameter)
    {
        return intval($parameter['row']['type'][0]) === DceField::TYPE_TAB;
    }

    /**
     * Get row of dce field of given uid
     *
     * @param int $uid
     * @return array dce field row
     */
    protected function getDceFieldRecordByUid($uid)
    {
        return \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection()->exec_SELECTgetSingleRow(
            '*',
            'tx_dce_domain_model_dcefield',
            'uid=' . intval($uid)
        );
    }
}
