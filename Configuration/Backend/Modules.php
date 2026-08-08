<?php

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
