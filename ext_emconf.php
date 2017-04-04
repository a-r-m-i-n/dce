<?php

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Dynamic Content Elements (DCE)',
    'description' => 'Best flexform based content elements since 2012. With TCA mapping feature, simple backend view ' .
        'and many more features which makes it easy to create own content element types.',
    'category' => 'Backend',
    'shy' => 0,
    'version' => '1.4.2',
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
    'constraints' => array(
        'depends' => array(
            'typo3' => '7.6.0-8.9.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
    'suggests' => array(),
);
