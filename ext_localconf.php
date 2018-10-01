<?php

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2018 Armin Vieweg <armin@v.ieweg.de>
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$boot = function ($extensionKey) {
    $extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey);

    // AfterSave hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['dce'] =
        \ArminVieweg\Dce\Hooks\AfterSaveHook::class;

    // ImportExport Hooks
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/impexp/class.tx_impexp.php']['before_setRelation']['dce'] =
        \ArminVieweg\Dce\Hooks\ImportExportHook::class . '->beforeSetRelation';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/impexp/class.tx_impexp.php']['before_writeRecordsRecords']['dce'] =
        \ArminVieweg\Dce\Hooks\ImportExportHook::class . '->beforeWriteRecordsRecords';

    // PageLayoutView DrawItem Hook for DCE content elements
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['dce'] =
        \ArminVieweg\Dce\Hooks\PageLayoutViewDrawItemHook::class;

    // Register ke_search hook to be able to index DCE frontend output
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyContentFromContentElement'][] =
            \ArminVieweg\Dce\Hooks\KeSearchHook::class;
    }

    // DocHeader buttons hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Backend\Template\Components\ButtonBar']['getButtonsHook']['Dce'] =
        \ArminVieweg\Dce\Hooks\DocHeaderButtonsHook::class . '->addDcePopupButton';

    // LiveSearch XClass
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Search\LiveSearch\LiveSearch::class] = [
        'className' => \ArminVieweg\Dce\XClass\LiveSearch::class,
    ];

    // User conditions
    require_once($extensionPath . 'Classes/UserConditions/user_dceOnCurrentPage.php');

    // Special tce validators (eval)
    require_once($extensionPath . 'Classes/UserFunction/CustomFieldValidation/AbstractFieldValidator.php');

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']
    [\ArminVieweg\Dce\UserFunction\CustomFieldValidation\LowerCamelCaseValidator::class] =
        'EXT:dce/Classes/UserFunction/CustomFieldValidation/LowerCamelCaseValidator.php';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']
    [\ArminVieweg\Dce\UserFunction\CustomFieldValidation\NoLeadingNumberValidator::class] =
        'EXT:dce/Classes/UserFunction/CustomFieldValidation/NoLeadingNumberValidator.php';

    // Update Scripts
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceMigrateOldNamespacesInFluidTemplateUpdate'] =
        \ArminVieweg\Dce\Updates\MigrateOldNamespacesInFluidTemplateUpdate::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceMigrateDceFieldDatabaseRelationUpdate'] =
        \ArminVieweg\Dce\Updates\MigrateDceFieldDatabaseRelationUpdate::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceMigrateFlexformSheetIdentifierUpdate'] =
        \ArminVieweg\Dce\Updates\MigrateFlexformSheetIdentifierUpdate::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceFixMalformedDceFieldVariableNamesUpdate'] =
        \ArminVieweg\Dce\Updates\FixMalformedDceFieldVariableNamesUpdate::class;

    // Slot to extend SQL tables definitions
    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
    );
    $signalSlotDispatcher->connect(
        \TYPO3\CMS\Install\Service\SqlExpectedSchemaService::class,
        'tablesDefinitionIsBeingBuilt',
        \ArminVieweg\Dce\Slots\TablesDefinitionIsBeingBuiltSlot::class,
        'extendTtContentTable'
    );

    // Register Plugin to get Dce instance
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'ArminVieweg.' . $extensionKey,
        'Dce',
        [
            'Dce' => 'renderDce'
        ],
        [
            'Dce' => ''
        ]
    );

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Dce']['modules']
        = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Dce']['plugins'];

    // Register DCEs
    $cache = new \ArminVieweg\Dce\Injector();
    $cache->injectPluginConfiguration();

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('linkvalidator')) {
        /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
        );
        $signalSlotDispatcher->connect(
            \TYPO3\CMS\Linkvalidator\LinkAnalyzer::class,
            'beforeAnalyzeRecord',
            \ArminVieweg\Dce\Slots\LinkAnalyserSlot::class,
            'beforeAnalyzeRecord'
        );
    }
};

$boot($_EXTKEY);
unset($boot);
