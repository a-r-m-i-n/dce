<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Dynamic Content Elements (DCE)',
    'description' => 'DCE is an extension for the CMS TYPO3, which creates easily and fast dynamic content elements. It is an alternative to flexible content elements (FCE) but without need of TemplaVoila (TV). Based on Extbase and Fluid.',
    'category' => 'Backend',
    'shy' => 0,
    'version' => '1.3.3',
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
            'typo3' => '6.2.0-7.6.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
    'suggests' => array(),
);
