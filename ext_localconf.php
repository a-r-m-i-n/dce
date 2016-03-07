<?php

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$boot = function ($extensionKey) {
    $extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey);

    // AfterSave hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['dce'] =
        'ArminVieweg\\Dce\\Hooks\\AfterSaveHook';

    // ImportExport Hooks
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/impexp/class.tx_impexp.php']['before_setRelation']['dce'] =
        'EXT:' . $extensionKey . '/Classes/Hooks/ImportExportHook.php:' .
        'ArminVieweg\\Dce\\Hooks\\ImportExportHook->beforeSetRelation';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/impexp/class.tx_impexp.php']['before_writeRecordsRecords']['dce'] =
        'EXT:' . $extensionKey . '/Classes/Hooks/ImportExportHook.php:' .
        'ArminVieweg\\Dce\\Hooks\\ImportExportHook->beforeWriteRecordsRecords';

    // PageLayoutView DrawItem Hook for DCE content elements
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['dce'] =
        'EXT:' . $extensionKey . '/Classes/Hooks/PageLayoutViewDrawItemHook.php:' .
        'ArminVieweg\\Dce\\Hooks\\PageLayoutViewDrawItemHook';

    // Clear cache hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['dce'] =
        'EXT:' . $extensionKey . '/Classes/Hooks/ClearCachePostHook.php:' .
        'ArminVieweg\\Dce\\Hooks\\ClearCachePostHook->clearDceCache';

    // Make edit form access check hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/alt_doc.php']['makeEditForm_accessCheck']['dce'] =
        'EXT:' . $extensionKey . '/Classes/Hooks/MakeEditFormAccessCheckHook.php:' .
        'ArminVieweg\\Dce\\Hooks\\MakeEditFormAccessCheckHook->checkAccess';

    // Register ke_search hook to be able to index DCE frontend output
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyContentFromContentElement'][] =
            'EXT:' . $extensionKey . '/Classes/Hooks/KeSearchHook.php:ArminVieweg\\Dce\\Hooks\\KeSearchHook';
    }

    // DocHeader buttons hook
    if (\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.6')) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Backend\Template\Components\ButtonBar']['getButtonsHook']['Dce'] =
            'ArminVieweg\Dce\Hooks\DocHeaderButtonsHook->addDcePopupButton';
    } else {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['docHeaderButtonsHook'][] =
            'EXT:' . $extensionKey . '/Classes/Hooks/DocHeaderButtonsHook.php:' .
            'ArminVieweg\\Dce\\Hooks\\DocHeaderButtonsHook->addDcePopupButton62';
    }


    // DataPreprocessor XClass
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Form\\DataPreprocessor'] = array(
        'className' => 'ArminVieweg\Dce\XClass\DataPreprocessor',
    );

    // LiveSearch XClass
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\Search\\LiveSearch\\LiveSearch'] = array(
        'className' => 'ArminVieweg\Dce\XClass\LiveSearch',
    );

    // User conditions
    require_once($extensionPath . 'Classes/UserConditions/user_dceOnCurrentPage.php');


    // Special tce validators (eval)
    require_once($extensionPath . 'Classes/UserFunction/CustomFieldValidation/AbstractFieldValidator.php');

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']
    ['ArminVieweg\Dce\UserFunction\CustomFieldValidation\\LowerCamelCaseValidator'] =
        'EXT:dce/Classes/UserFunction/CustomFieldValidation/LowerCamelCaseValidator.php';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']
    ['ArminVieweg\Dce\UserFunction\CustomFieldValidation\\NoLeadingNumberValidator'] =
        'EXT:dce/Classes/UserFunction/CustomFieldValidation/NoLeadingNumberValidator.php';


    // Update Scripts
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceMigrateOldNamespacesInFluidTemplateUpdate'] =
        'ArminVieweg\Dce\Updates\MigrateOldNamespacesInFluidTemplateUpdate';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceMigrateDceFieldDatabaseRelationUpdate'] =
        'ArminVieweg\Dce\Updates\MigrateDceFieldDatabaseRelationUpdate';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceMigrateFlexformSheetIdentifierUpdate'] =
        'ArminVieweg\Dce\Updates\MigrateFlexformSheetIdentifierUpdate';


    // Slot to extend SQL tables definitions
    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        'TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher'
    );
    $signalSlotDispatcher->connect(
        'TYPO3\CMS\Install\Service\SqlExpectedSchemaService',
        'tablesDefinitionIsBeingBuilt',
        'ArminVieweg\Dce\Slots\TablesDefinitionIsBeingBuiltSlot',
        'extendTtContentTable'
    );


    // Register Plugin to get Dce instance
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'ArminVieweg.' . $extensionKey,
        'Dce',
        array(
            'Dce' => 'renderDce'
        ),
        array(
            'Dce' => ''
        )
    );

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Dce']['modules']
        = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Dce']['plugins'];

    // Include cached ext_localconf
    if (!\ArminVieweg\Dce\Cache::cacheExists(\ArminVieweg\Dce\Cache::CACHE_TYPE_EXTLOCALCONF)) {
        /** @var $dceCache \ArminVieweg\Dce\Cache */
        $dceCache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('ArminVieweg\Dce\Cache');
        $dceCache->createLocalconf();
    }
    if (\ArminVieweg\Dce\Cache::cacheExists(\ArminVieweg\Dce\Cache::CACHE_TYPE_EXTLOCALCONF)) {
        require_once(PATH_site . \ArminVieweg\Dce\Cache::CACHE_PATH . \ArminVieweg\Dce\Cache::CACHE_TYPE_EXTLOCALCONF);
    }
};

$boot($_EXTKEY);
unset($boot);
