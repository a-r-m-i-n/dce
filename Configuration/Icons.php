<?php

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

$dceIcons = [
    'dce-ext' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:dce/Resources/Public/Icons/Extension.png',
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

return $dceIcons;
