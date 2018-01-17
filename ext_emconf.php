<?php

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2018 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

// @codingStandardsIgnoreStart
$EM_CONF[$_EXTKEY] = [
    'title' => 'Dynamic Content Elements (DCE)',
    'description' => 'Best flexform based content elements since 2012. With TCA mapping feature, simple backend view and much more features which makes it super easy to create own content element types.',
    'category' => 'Backend',
    'shy' => 0,
    'version' => '1.4.11',
    'dependencies' => 'extbase,fluid',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'stable',
    'uploadfolder' => 1,
    'createDirs' => '',
    'modify_tables' => 'tt_content',
    'clearcacheonload' => 1,
    'lockType' => '',
    'author' => 'Armin Ruediger Vieweg',
    'author_email' => 'armin@v.ieweg.de',
    'author_company' => '',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'suggests' => [],
    'autoload' => [
        'psr-4' => ['ArminVieweg\\Dce\\' => 'Classes']
    ],
];
// @codingStandardsIgnoreEnd
