<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$boot = function ($extensionKey) {
    // Include cached ext_tables
    if (!\ArminVieweg\Dce\Cache::cacheExists(\ArminVieweg\Dce\Cache::CACHE_TYPE_EXTTABLES)) {
        /** @var $dceCache \ArminVieweg\Dce\Cache */
        $dceCache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('ArminVieweg\Dce\Cache');
        $dceCache->createExtTables();
    }
    require_once(PATH_site . \ArminVieweg\Dce\Cache::CACHE_PATH . \ArminVieweg\Dce\Cache::CACHE_TYPE_EXTTABLES);


    // Register backend module
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'ArminVieweg.' . $extensionKey,
        'tools',
        'dceModule',
        '',
        array(
            'DceModule' => 'index,hallOfFame',
            'Dce' => 'renderPreview'
        ),
        array(
            'access' => 'user,group',
            'icon' => 'EXT:' . $extensionKey . '/ext_icon.png',
            'labels' => 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_mod.xml',
        )
    );
};

$boot($_EXTKEY);
unset($boot);
