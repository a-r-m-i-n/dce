<?php

use T3\Dce\Components\ContentElementGenerator\InputDatabase;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$dceIcons = [
    'dce-ext' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:dce/Resources/Public/Icons/ext_icon.png',
    ],
    'dce-module' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:dce/Resources/Public/Icons/DceModuleIcon.svg',
    ],
    'ext-dce-dcefield-type-element' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:dce/Resources/Public/Icons/tx_dce_domain_model_dcefield_element.png',
    ],
    'ext-dce-dcefield-type-tab' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:dce/Resources/Public/Icons/tx_dce_domain_model_dcefield_tab.png',
    ],
    'ext-dce-dcefield-type-section' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:dce/Resources/Public/Icons/tx_dce_domain_model_dcefield_section.png',
    ],
];

/** @var InputDatabase $dceInputDatabase */
$dceInputDatabase = GeneralUtility::makeInstance(InputDatabase::class);
foreach ($dceInputDatabase->getDces() as $dce) {
    if ($dce['hasCustomWizardIcon'] && !empty($dce['wizard_custom_icon'])) {
        $wizardCustomIcon = $dce['wizard_custom_icon'];

        $iconProvider = BitmapIconProvider::class;
        if (str_ends_with($wizardCustomIcon, '.svg')) {
            $iconProvider = SvgIconProvider::class;
        }

        $dceIcons['ext-dce-' . $dce['identifier'] . '-customwizardicon'] = [
            'provider' => $iconProvider,
            'source' => $wizardCustomIcon,
        ];
    }
}

return $dceIcons;
