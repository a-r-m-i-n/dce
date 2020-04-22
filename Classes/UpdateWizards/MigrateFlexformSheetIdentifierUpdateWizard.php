<?php declare(strict_types=1);
namespace T3\Dce\UpdateWizards;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\UpdateWizards\Traits\MigrateFlexformSheetIdentifierTrait;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

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
 */
class MigrateFlexformSheetIdentifierUpdateWizard implements UpgradeWizardInterface
{
    use MigrateFlexformSheetIdentifierTrait;

    protected $description = '';

    public function getIdentifier(): string
    {
        return 'dceMigrateFlexformSheetIdentifierUpdate';
    }

    public function getTitle(): string
    {
        return 'EXT:dce Migrate flexform sheet identifiers';
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
        $tabsWithoutIdentifier = $this->getUpdatableDceFields();
        $contentElementsWithWrongXml = $this->getUpdatableContentElements();

        $this->description = 'There are ' . \count($tabsWithoutIdentifier) . ' tab fields without identifier and ' .
            \count($contentElementsWithWrongXml) . ' content elements with old xml structure ' .
            'existing.' . PHP_EOL .
            'Caution! Please make sure that you\'ve migrated the mm-relation of dce fields to 1:n ' .
            'before executing this update wizard.';

        return \count($tabsWithoutIdentifier) > 0 || \count($contentElementsWithWrongXml) > 0;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
