<?php /** @noinspection ALL */

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2023 Armin Vieweg <armin@v.ieweg.de>
 */

$boot = function ($extensionKey) {
    $extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey);

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

    // Register ke_search hook to be able to index DCE frontend output
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyContentFromContentElement'][] =
            \T3\Dce\Hooks\KeSearchHook::class;
    }

    // List view search hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList::class]
        ['makeSearchStringConstraints']['dce'] = \T3\Dce\Hooks\ListViewSearchHook::class;

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
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceFileToFalUpdate'] =
        \T3\Dce\UpdateWizards\FileToFalUpdateWizard::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['dceInlineFalToFileUpdateWizard'] =
        \T3\Dce\UpdateWizards\InlineFalToFileUpdateWizard::class;

    // Logger for update scripts
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['T3']['Dce']['UpdateWizards']['writerConfiguration'] = [
        \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
            \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                'logFile' => \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/dce_update_wizards.log'
            ],
        ],
    ];

    // Link Handler Hook
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['linkHandler']['ext'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['linkHandler']['ext'] =
            \T3\Dce\Hooks\InputLinkElementExplanationHook::class;
    }

    // Register Plugin to get Dce instance
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        $extensionKey,
        'Dce',
        [
            \T3\Dce\Controller\DceController::class => 'renderDce'
        ],
        [
            \T3\Dce\Controller\DceController::class => ''
        ]
    );

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Dce']['modules']
        = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Dce']['plugins'];

    // Register DCEs
    $generator = new \T3\Dce\Components\ContentElementGenerator\Generator();
    $generator->makePluginConfiguration();

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

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('linkvalidator')) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            'mod.linkvalidator.searchFields.tt_content := addToList(pi_flexform)'
        );
    }

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
//    if (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_BE) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1551536118] = [
            'nodeName' => 'dceCodeMirrorField',
            'priority' => '70',
            'class' => \T3\Dce\UserFunction\FormEngineNode\DceCodeMirrorFieldRenderType::class,
        ];
//    }
};

$boot('dce');
unset($boot);
