<?php

namespace T3\Dce\EventListener;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2024-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Backend\Controller\Event\ModifyNewContentElementWizardItemsEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

/**
 * This event listener only applies to TYPO3 v13 and higher,
 * because the AsEventListener attribute is not available in earlier versions.
 */
#[AsEventListener(identifier: 'ext-dce/modify-new-content-element-wizard-items-event-listener')]
class ModifyNewContentElementWizardItemsEventListener
{
    public function __invoke(ModifyNewContentElementWizardItemsEvent $event): void
    {
        $dceWizardItems = [];
        foreach ($event->getWizardItems() as $key => $wizardItem) {
            $cType = $wizardItem['defaultValues']['CType'] ?? null;
            if ($cType && str_starts_with($cType, 'dce_')) {
                $dceWizardItems[$key] = $wizardItem;
            }
        }

        $dceCTypes = [];
        $duplicates = [];
        foreach ($dceWizardItems as $key => $dceWizardItem) {
            if (in_array($dceWizardItem['defaultValues']['CType'], $dceCTypes, true)) {
                $duplicates[$key] = $dceWizardItem['defaultValues']['CType'];
            }
            $dceCTypes[$key] = $dceWizardItem['defaultValues']['CType'];
        }

        foreach ($duplicates as $duplicateCType) {
            foreach ($dceCTypes as $key => $cType) {
                if ($cType === $duplicateCType && str_starts_with($key, 'dce_')) {
                    $event->removeWizardItem($key);
                }
            }
        }
    }
}
