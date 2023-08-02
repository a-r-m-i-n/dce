<?php

//\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
//    \T3\Dce\Compatibility::isTypo3Version('10.0.0') ? $extensionKey : 'T3.' . $extensionKey,
//    'tools',
//    'dceModule',
//    '',
//    [
//        \T3\Dce\Compatibility::isTypo3Version('10.0.0') ? \T3\Dce\Controller\DceModuleController::class : 'DceModule' =>
//            'index,clearCaches,hallOfFame,updateTcaMappings',
//    ],
//    [
//        'access' => 'user,group',
//        'icon' => $extensionIconPath,
//        'labels' => 'LLL:EXT:dce/Resources/Private/Language/locallang_mod.xlf',
//    ]
//);

use T3\Dce\Controller\DceModuleController;

return [

    'tools_dce' => [
        'parent' => 'tools',
        'access' => 'admin',
        'path' => '/module/tools/dce',
        'iconIdentifier' => 'dce-module',
        'labels' => [
            'title' => 'LLL:EXT:dce/Resources/Private/Language/locallang_mod.xlf:mlang_tabs_tab',
            'shortDescription' => 'LLL:EXT:dce/Resources/Private/Language/locallang_mod.xlf:mlang_labels_tablabel',
            'description' => 'LLL:EXT:dce/Resources/Private/Language/locallang_mod.xlf:mlang_labels_tabdescr',
        ],
//        'navigationComponent' => '@typo3/backend/page-tree/page-tree-element',
        'extensionName' => 'Dce',
        'controllerActions' => [
            DceModuleController::class => [
                'index', 'clearCaches', 'hallOfFame', 'updateTcaMappings',
            ],
        ],
    ],
];
