<?php /** @noinspection ALL */

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$boot = function ($extensionKey) {
    $extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey);

    if (!class_exists(\T3\Dce\Compatibility::class) && class_exists(\T3\Dce\Controller\DceController::class)) {
        require_once($extensionPath . 'Classes/Compatibility.php');
    }

    // Clear cache hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['dce'] =
        \T3\Dce\Hooks\ClearCacheHook::class . '->flushDceCache';

    // AfterSave hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['dce'] =
        \T3\Dce\Hooks\AfterSaveHook::class;

    // ImportExport Hooks
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/impexp/class.tx_impexp.php']['before_setRelation']['dce'] =
        \T3\Dce\Hooks\ImportExportHook::class . '->beforeSetRelation';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/impexp/class.tx_impexp.php']['before_writeRecordsRecords']['dce'] =
        \T3\Dce\Hooks\ImportExportHook::class . '->beforeWriteRecordsRecords';

    if (!\T3\Dce\Compatibility::isTypo3Version('10.0.0')
        || (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['fluidBasedPageModule']) &&
               $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['fluidBasedPageModule'] === false
           )
        || \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('gridelements')
        || \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('container')
    ) {
        // PageLayoutView DrawItem Hook for DCE content elements
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['dce'] =
            \T3\Dce\Hooks\PageLayoutViewDrawItemHook::class;
    }

    // Register ke_search hook to be able to index DCE frontend output
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyContentFromContentElement'][] =
            \T3\Dce\Hooks\KeSearchHook::class;
    }

    // List view search hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList::class]
        ['makeSearchStringConstraints']['dce'] = \T3\Dce\Hooks\ListViewSearchHook::class;

    // DocHeader buttons hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Backend\Template\Components\ButtonBar']['getButtonsHook']['Dce'] =
        \T3\Dce\Hooks\DocHeaderButtonsHook::class . '->addDcePopupButton';

    // LiveSearch XClass
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Search\LiveSearch\LiveSearch::class] = [
        'className' => \T3\Dce\XClass\LiveSearch::class,
    ];

    // Special tce validators (eval)
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']
    [\T3\Dce\UserFunction\CustomFieldValidation\LowerCamelCaseValidator::class] =
        'EXT:dce/Classes/UserFunction/CustomFieldValidation/LowerCamelCaseValidator.php';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']
    [\T3\Dce\UserFunction\CustomFieldValidation\NoLeadingNumberValidator::class] =
        'EXT:dce/Classes/UserFunction/CustomFieldValidation/NoLeadingNumberValidator.php';

    // Update Scripts (since TYPO3 v9)
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceMigrateOldNamespacesInFluidTemplateUpdate'] =
        \T3\Dce\UpdateWizards\MigrateOldNamespacesInFluidTemplateUpdateWizard::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceMigrateDceFieldDatabaseRelationUpdate'] =
        \T3\Dce\UpdateWizards\MigrateDceFieldDatabaseRelationUpdateWizard::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceMigrateFlexformSheetIdentifierUpdate'] =
        \T3\Dce\UpdateWizards\MigrateFlexformSheetIdentifierUpdateWizard::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceFixMalformedDceFieldVariableNamesUpdate'] =
        \T3\Dce\UpdateWizards\FixMalformedDceFieldVariableNamesUpdateWizard::class;

    if (!\T3\Dce\Compatibility::isTypo3Version('10.0.0')) {
        // Slot to extend SQL tables definitions
        /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
        );
        $signalSlotDispatcher->connect(
            'TYPO3\\CMS\\Install\\Service\\SqlExpectedSchemaService',
            'tablesDefinitionIsBeingBuilt',
            \T3\Dce\Slots\TablesDefinitionIsBeingBuiltSlot::class,
            'extendTtContentTable'
        );
    }

    // Link Handler Hook
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['linkHandler']['ext'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['linkHandler']['ext'] =
            \T3\Dce\Hooks\InputLinkElementExplanationHook::class;
    }

    // Register Plugin to get Dce instance
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        \T3\Dce\Compatibility::isTypo3Version('10.0.0') ? $extensionKey : 'T3.' . $extensionKey,
        'Dce',
        [
            \T3\Dce\Compatibility::isTypo3Version('10.0.0') ? \T3\Dce\Controller\DceController::class : 'Dce' => 'renderDce'
        ],
        [
            \T3\Dce\Compatibility::isTypo3Version('10.0.0') ? \T3\Dce\Controller\DceController::class : 'Dce' => ''
        ]
    );

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Dce']['modules']
        = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Dce']['plugins'];

    // Register DCEs
    $generator = new \T3\Dce\Components\ContentElementGenerator\Generator();
    $generator->makePluginConfiguration();

    // Only for v8 and v9
    if (!\T3\Dce\Compatibility::isTypo3Version('10.0.0') &&
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('linkvalidator')
    ) {
        /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
        );
        $signalSlotDispatcher->connect(
            \TYPO3\CMS\Linkvalidator\LinkAnalyzer::class,
            'beforeAnalyzeRecord',
            \T3\Dce\Slots\LinkAnalyserSlot::class,
            'beforeAnalyzeRecord'
        );
    }

    // Register PageTS defaults
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('tx_dce.defaults {
        simpleBackendView {
            titleCropLength = 10
            titleCropAppendix = ...

            imageWidth = 50c
            imageHeight = 50c

            containerGroupColors {
                10 = #0079BF
                11 = #D29034
                12 = #519839
                13 = #B04632
                14 = #838C91
                15 = #CD5A91
                16 = #4BBF6B
                17 = #89609E
                18 = #00AECC
                19 = #ED2448
                20 = #FF8700
            }
        }
    }');

    // Global namespace
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['dce'] = ['T3\\Dce\\ViewHelpers'];

    // UserFunc TypoScript Condition (for expression language)
    $providerName = 'TYPO3\CMS\Core\ExpressionLanguage\TypoScriptConditionProvider';
    $sectionName = 'additionalExpressionLanguageProvider';
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$providerName][$sectionName])) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$providerName][$sectionName] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$providerName][$sectionName][] =
        \T3\Dce\Components\UserConditions\TypoScriptConditionFunctionProvider::class;

    // Code Mirror Node for FormEngine
    if (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_BE) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1551536118] = [
            'nodeName' => 'dceCodeMirrorField',
            'priority' => '70',
            'class' => \T3\Dce\UserFunction\FormEngineNode\DceCodeMirrorFieldRenderType::class,
        ];
    }
};

$boot('dce');
unset($boot);
