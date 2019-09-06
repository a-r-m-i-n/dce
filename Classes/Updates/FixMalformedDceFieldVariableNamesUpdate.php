<?php
namespace T3\Dce\Updates;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\UserFunction\CustomFieldValidation\LowerCamelCaseValidator;
use T3\Dce\UserFunction\CustomFieldValidation\NoLeadingNumberValidator;
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Fix dce fields with malformed variable names.
 *
 * In older versions of DCE the userfunction, which checks and corrects entered variable names to be lowerCamelCase,
 * did not work properly. But when editing such old DCEs the variables became corrected and does not match with
 * flexform structure used in tt_content.
 *
 * This update checks if such fields exist and correct them in tt_content's pi_flexform column and in DceFields.
 * It does not correct the fluid templates for you!
 */
class FixMalformedDceFieldVariableNamesUpdate extends AbstractUpdate
{
    /**
     * @var string
     */
    protected $title = 'EXT:dce Fix malformed DceField variable names';

    /**
     * @var string
     */
    protected $identifier = 'dceFixMalformedDceFieldVariableNamesUpdate';

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        $malformedDceFields = $this->getDceFieldsWithMalformedVariableNames();

        $description .= 'Found <b>' . \count($malformedDceFields) . ' malformed DceFields</b>! This update does not ' .
            'update malformed variable names in fluid templates! But it updates the DceField record and all ' .
            'tt_content records based on this DCE.<br>' .
            'Caution! Please make sure that you\'ve migrated the mm-relation of dce fields to 1:n ' .
            'before executing this update wizard.<br><br>';

        return \count($malformedDceFields) > 0;
    }

    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries Queries done in this update
     * @param string|array &$customMessages Custom messages
     * @return bool Whether everything went smoothly or not
     * @TODO Refactor me
     */
    public function performUpdate(array &$dbQueries, &$customMessages)
    {
        $malformedDceFields = $this->getDceFieldsWithMalformedVariableNames();
        foreach ($malformedDceFields as $malformedDceField) {
            $malformedVariableName = $malformedDceField['variable'];
            // Update DceField
            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dcefield');
            $connection->update(
                'tx_dce_domain_model_dcefield',
                [
                    'variable' => $this->fixVariableName($malformedVariableName)
                ],
                [
                    'uid' => (int) $malformedDceField['uid']
                ]
            );

            // Update tt_content records based on the DCE regarding current field
            if ($malformedDceField['parent_dce'] == 0) {
                // get section field and then DCE (thanks god, that section fields are limited to be not nestable!^^)
                $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
                    'tx_dce_domain_model_dcefield'
                );
                $sectionParent = $queryBuilder
                    ->select('*')
                    ->from('tx_dce_domain_model_dcefield')
                    ->where(
                        $queryBuilder->expr()->eq(
                            'uid',
                            $queryBuilder->createNamedParameter($malformedDceField['parent_field'], \PDO::PARAM_INT)
                        )
                    )
                    ->execute()
                    ->fetch();
                $dceUid = $sectionParent['parent_dce'];
            } else {
                $dceUid = $malformedDceField['parent_dce'];
            }

            $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
            $contentElements = $queryBuilder
                ->select('*')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->eq(
                        'CType',
                        $queryBuilder->createNamedParameter($this->getDceIdentifier($dceUid))
                    )
                )
                ->execute()
                ->fetchAll();

            foreach ($contentElements as $contentElement) {
                $updatedFlexform = str_replace(
                    [
                        '"settings.' . $malformedVariableName . '"', // Fix variable names
                        '<field index="' . $malformedVariableName . '">' // Fix section field names
                    ],
                    [
                        '"settings.' . $this->fixVariableName($malformedVariableName) . '"',
                        '<field index="' . $this->fixVariableName($malformedVariableName) . '">'
                    ],
                    $contentElement['pi_flexform']
                );

                $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tt_content');
                $connection->update(
                    'tt_content',
                    [
                        'pi_flexform' => $updatedFlexform
                    ],
                    [
                        'uid' => (int) $contentElement['uid']
                    ]
                );
            }
        }
        return true;
    }

    /**
     * Returns DceField rows of fields with malformed variable name.
     * A malformed variable:
     * - starts with integer and/or
     * - is not lowerCamelCase
     *
     * @return array DceField rows
     * @see \T3\Dce\UserFunction\CustomFieldValidation\NoLeadingNumberValidator
     * @see \T3\Dce\UserFunction\CustomFieldValidation\LowerCamelCaseValidator
     */
    protected function getDceFieldsWithMalformedVariableNames() : array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dcefield');
        $dceFieldRows = $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where('variable != ""')
            ->execute()
            ->fetchAll();

        $lowerCamelCaseValidator = $this->getLowerCamelCaseValidator();
        $noLeadingNumberValidator = $this->getNoLeadingNumberValidator();

        $malformedDceFields = [];
        foreach ($dceFieldRows as $dceFieldRow) {
            $evalLowerCamelCase = $lowerCamelCaseValidator->evaluateFieldValue($dceFieldRow['variable'], true);
            $evalNoLeadingNumber = $noLeadingNumberValidator->evaluateFieldValue($dceFieldRow['variable'], true);
            if ($evalLowerCamelCase !== $dceFieldRow['variable'] || $evalNoLeadingNumber !== $dceFieldRow['variable']) {
                $malformedDceFields[] = $dceFieldRow;
            }
        }
        return $malformedDceFields;
    }

    /**
     * Returns instance of LowerCamelCaseValidator
     *
     * @return LowerCamelCaseValidator
     */
    protected function getLowerCamelCaseValidator() : LowerCamelCaseValidator
    {
        /** @var LowerCamelCaseValidator $lowerCamelCaseValidator */
        $lowerCamelCaseValidator = GeneralUtility::makeInstance(
            LowerCamelCaseValidator::class
        );
        return $lowerCamelCaseValidator;
    }

    /**
     * Returns instance of NoLeadingNumberValidator
     *
     * @return NoLeadingNumberValidator
     */
    protected function getNoLeadingNumberValidator() : NoLeadingNumberValidator
    {
        /** @var NoLeadingNumberValidator $noLeadingNumberValidator */
        $noLeadingNumberValidator = GeneralUtility::makeInstance(
            NoLeadingNumberValidator::class
        );
        return $noLeadingNumberValidator;
    }

    /**
     * Fix given variable name
     *
     * @param string $variableName e.g. "4ExampleValue"
     * @return string "exampleValue"
     */
    protected function fixVariableName(string $variableName) : string
    {
        $lowerCamelCaseValidator = $this->getLowerCamelCaseValidator();
        $noLeadingNumberValidator = $this->getNoLeadingNumberValidator();

        $updatedVariableName = $lowerCamelCaseValidator->evaluateFieldValue($variableName, true);
        $updatedVariableName = $noLeadingNumberValidator->evaluateFieldValue($updatedVariableName, true);
        return $updatedVariableName;
    }

    /**
     * Flattens an array, but makes the delimiter configurable
     *
     * @param array $array
     * @param string $prefix
     * @param string $delimiter
     * @return array
     * @see \TYPO3\CMS\Core\Utility\ArrayUtility::flatten
     */
    protected function flattenArray(array $array, $prefix = '', $delimiter = '.') : array
    {
        $flatArray = [];
        foreach ($array as $key => $value) {
            // Ensure there is no trailling dot:
            $key = rtrim($key, '.');
            if (!\is_array($value)) {
                $flatArray[$prefix . $key] = $value;
            } else {
                $flatArray = array_merge(
                    $flatArray,
                    $this->flattenArray($value, $prefix . $key . $delimiter, $delimiter)
                );
            }
        }
        return $flatArray;
    }
}
