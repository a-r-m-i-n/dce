<?php declare(strict_types=1);
namespace T3\Dce\UpdateWizards;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\UpdateWizards\Traits\FixMalformedDceFieldVariableNamesTrait;
use T3\Dce\UpdateWizards\Traits\GetDceIdentifierTrait;
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
    use GetDceIdentifierTrait;
    use FixMalformedDceFieldVariableNamesTrait;

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
        return (bool) $this->update();
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

    public function getPrerequisites(): array
    {
        return [];
    }
}
