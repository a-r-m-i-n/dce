<?php

declare(strict_types = 1);

namespace T3\Dce\UpdateWizards;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\UserFunction\CustomFieldValidation\LowerCamelCaseValidator;
use T3\Dce\UserFunction\CustomFieldValidation\NoLeadingNumberValidator;
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

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
class FixMalformedDceFieldVariableNamesUpdateWizard implements UpgradeWizardInterface
{
    /** @var string */
    protected $description = '';

    public function getIdentifier(): string
    {
        return 'dceFixMalformedDceFieldVariableNamesUpdate';
    }

    public function getTitle(): string
    {
        return 'EXT:dce Fix malformed DceField variable names';
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function executeUpdate(): bool
    {
        return (bool)$this->update();
    }

    public function updateNecessary(): bool
    {
        $malformedDceFields = $this->getDceFieldsWithMalformedVariableNames();
        $this->description = 'Found ' . \count($malformedDceFields) . ' malformed DceFields!' . PHP_EOL .
            'This update does not update malformed variable names in fluid templates! ' .
            'But it updates the DceField record and all tt_content records based on this DCE.' . PHP_EOL .
            'Caution! Please make sure that you\'ve migrated the mm-relation of dce fields to 1:n ' .
            'before executing this update wizard.';

        return \count($malformedDceFields) > 0;
    }

    public function update(): ?bool
    {
        $malformedDceFields = $this->getDceFieldsWithMalformedVariableNames();
        foreach ($malformedDceFields as $malformedDceField) {
            $malformedVariableName = $malformedDceField['variable'];
            // Update DceField
            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dcefield');
            $connection->update(
                'tx_dce_domain_model_dcefield',
                [
                    'variable' => $this->fixVariableName($malformedVariableName),
                ],
                [
                    'uid' => (int)$malformedDceField['uid'],
                ]
            );

            // Update tt_content records based on the DCE regarding current field
            if (0 == $malformedDceField['parent_dce']) {
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
                        '<field index="' . $malformedVariableName . '">', // Fix section field names
                    ],
                    [
                        '"settings.' . $this->fixVariableName($malformedVariableName) . '"',
                        '<field index="' . $this->fixVariableName($malformedVariableName) . '">',
                    ],
                    $contentElement['pi_flexform']
                );

                $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tt_content');
                $connection->update(
                    'tt_content',
                    [
                        'pi_flexform' => $updatedFlexform,
                    ],
                    [
                        'uid' => (int)$contentElement['uid'],
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
     * - is not lowerCamelCase.
     *
     * @return array DceField rows
     *
     * @see \T3\Dce\UserFunction\CustomFieldValidation\NoLeadingNumberValidator
     * @see \T3\Dce\UserFunction\CustomFieldValidation\LowerCamelCaseValidator
     */
    protected function getDceFieldsWithMalformedVariableNames(): array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dcefield');
        $dceFieldRows = $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->eq(
                    'variable',
                    $queryBuilder->createNamedParameter('')
                )
            )
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
     * Returns instance of LowerCamelCaseValidator.
     */
    protected function getLowerCamelCaseValidator(): LowerCamelCaseValidator
    {
        /** @var LowerCamelCaseValidator $lowerCamelCaseValidator */
        $lowerCamelCaseValidator = GeneralUtility::makeInstance(
            LowerCamelCaseValidator::class
        );

        return $lowerCamelCaseValidator;
    }

    /**
     * Returns instance of NoLeadingNumberValidator.
     */
    protected function getNoLeadingNumberValidator(): NoLeadingNumberValidator
    {
        /** @var NoLeadingNumberValidator $noLeadingNumberValidator */
        $noLeadingNumberValidator = GeneralUtility::makeInstance(
            NoLeadingNumberValidator::class
        );

        return $noLeadingNumberValidator;
    }

    /**
     * Fix given variable name.
     *
     * @param string $variableName e.g. "4ExampleValue"
     *
     * @return string "exampleValue"
     */
    protected function fixVariableName(string $variableName): string
    {
        $lowerCamelCaseValidator = $this->getLowerCamelCaseValidator();
        $noLeadingNumberValidator = $this->getNoLeadingNumberValidator();

        $updatedVariableName = $lowerCamelCaseValidator->evaluateFieldValue($variableName, true);
        $updatedVariableName = $noLeadingNumberValidator->evaluateFieldValue($updatedVariableName, true);

        return $updatedVariableName;
    }

    /**
     * Returns the identifier of dce with given uid.
     */
    public static function getDceIdentifier(int $dceUid): string
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dce');
        $dce = $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dce')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($dceUid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        return is_array($dce) && !empty($dce['identifier']) ? 'dce_' . $dce['identifier'] : 'dce_dceuid' . $dceUid;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
