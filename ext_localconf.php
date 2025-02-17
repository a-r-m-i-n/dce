<?php

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */

$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('dce');

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

// Special tce validators (eval)
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']
[\T3\Dce\UserFunction\CustomFieldValidation\LowerCamelCaseValidator::class] =
    'EXT:dce/Classes/UserFunction/CustomFieldValidation/LowerCamelCaseValidator.php';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']
[\T3\Dce\UserFunction\CustomFieldValidation\NoLeadingNumberValidator::class] =
    'EXT:dce/Classes/UserFunction/CustomFieldValidation/NoLeadingNumberValidator.php';

// Logger for update scripts
$GLOBALS['TYPO3_CONF_VARS']['LOG']['T3']['Dce']['UpdateWizards']['writerConfiguration'] = [
    \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
        \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
            'logFile' => \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/dce_update_wizards.log'
        ],
    ],
];

// Register Plugin to get Dce instance
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'dce',
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

// Register global TypoScript
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript('dce', 'setup', '
plugin.tx_dce.persistence.storagePid = 0

# Disable ce wrapping (for dce)
tt_content.stdWrap.innerWrap.cObject.default.stdWrap.if {
    value := addToList(dce_dceuid0)
    isInList.field = CType
    negate = 1
}

lib.contentElement.templateRootPaths.1 = EXT:dce/Resources/Private/Templates

config.pageTitleProviders.dce {
    provider = T3\Dce\Components\DetailPage\PageTitleProvider
    before = record
    after = altPageTitle

    prependWrap = || - |
    appendWrap = | - ||
}
');

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('linkvalidator')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod.linkvalidator.searchFields.tt_content := addToList(pi_flexform)'
    );
}

// Global namespace for Fluid templates
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
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1551536118] = [
    'nodeName' => 'dceCodeMirrorField',
    'priority' => '70',
    'class' => \T3\Dce\UserFunction\FormEngineNode\DceCodeMirrorFieldRenderType::class,
];
