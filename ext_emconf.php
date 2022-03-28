<?php

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

// phpcs:disable
$EM_CONF[$_EXTKEY] = [
    'title' => 'Dynamic Content Elements (DCE)',
    'description' => 'Best flexform based content elements since 2012. With TCA mapping feature, simple backend view and much more features which makes it super easy to create own content element types.',
    'category' => 'Backend',
    'shy' => 0,
    'version' => '2.8.2',
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
    'author' => 'Armin Vieweg',
    'author_email' => 'armin@v.ieweg.de',
    'author_company' => '',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'constraints' => [
        'depends' => [
            'php' => '7.3.0-7.4.99',
            'typo3' => '9.5.0-11.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'suggests' => [],
    'autoload' => [
        'psr-4' => ['T3\\Dce\\' => 'Classes'],
        'classmap' => ['Classes/Compatibility.php'],
    ],
];
// @codingStandardsIgnoreEnd
// phpcs:enable
