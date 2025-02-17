<?php

namespace T3\Dce\UserFunction;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Type;
use Psr\Container\ContainerInterface;
use T3\Dce\Components\FlexformToTcaMapper\Mapper;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\LanguageService;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ItemsProcFunc
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function getDceFields(array &$parameters): void
    {
        if (!isset($parameters['row']['uid']) || !is_numeric($parameters['row']['uid'])) {
            return;
        }

        $ll = 'LLL:EXT:dce/Resources/Private/Language/locallang.xlf:';

        $parameters['items'][] = [
            'label' => $ll . 'dceTitle',
            'value' => '*dcetitle',
        ];
        if (1 === $parameters['config']['size']) {
            $parameters['items'][] = [
                'label' => $ll . 'empty',
                'value' => '*empty',
            ];
        }
        if (isset($parameters['row']['enable_container'])
            && $parameters['row']['enable_container']
            && 'backend_view_header' !== $parameters['field']
        ) {
            $parameters['items'][] = [
                'label' => $ll . 'containerflag',
                'value' => '*containerflag',
            ];
        }

        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dcefield');
        $dceFields = $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->eq(
                    'parent_dce',
                    $queryBuilder->createNamedParameter($parameters['row']['uid'], Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->in(
                    'type',
                    $queryBuilder->createNamedParameter([0, 2], ArrayParameterType::INTEGER)
                )
            )
            ->orderBy('sorting', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        if (!empty($dceFields)) {
            $parameters['items'][] = [
                'label' => $ll . 'dceFields',
                'value' => '--div--',
            ];

            foreach ($dceFields as $dceField) {
                $label = LanguageService::sL($dceField['title']);
                if ('2' === $dceField['type']) {
                    if ('backend_view_header' === $parameters['field']) {
                        continue;
                    }

                    $label .= ' (' . LocalizationUtility::translate('section', 'dce') . ')';
                }
                $parameters['items'][] = [
                    'label' => $label,
                    'value' => $dceField['variable'],
                ];
            }
        }
    }

    public function getAvailableTtContentColumnsForTcaMapping(array &$parameters): void
    {
        $excludedColumns = [
            'uid',
            'pid',
            'CType',
            'editlock',
            'sys_language_uid',
            'l18n_parent',
            'colPos',
            'pi_flexform',
            'tx_impexp_origuid',
            'l18n_diffsource',
            't3ver_label',
            'tx_dce_dce',
            'tx_dce_index',
            'tx_dce_new_container',
            'tx_dce_slug',
        ];
        // Do not show column which has been provided by itself
        if ('tx_dce_domain_model_dcefield' === $parameters['table']
            && '*newcol' === $parameters['row']['map_to']
            && !empty($parameters['row']['new_tca_field_name'])
        ) {
            $excludedColumns[] = $parameters['row']['new_tca_field_name'];
        }
        $tcaColumns = $GLOBALS['TCA']['tt_content']['columns'];
        $dbColumns = DatabaseUtility::adminGetFields('tt_content');

        $ll = 'LLL:EXT:dce/Resources/Private/Language/locallang.xlf:';

        $parameters['items'][] = [
            'label' => $ll . 'chooseOption',
            'value' => '--div--',
        ];
        $parameters['items'][] = [
            'label' => $ll . 'noMapping',
            'value' => '',
        ];
        $parameters['items'][] = [
            'label' => $ll . 'mapToIndexColumn',
            'value' => 'tx_dce_index',
        ];
        $parameters['items'][] = [
            'label' => $ll . 'newcol',
            'value' => '*newcol',
        ];

        $parameters['items'][] = [
            'label' => $ll . 'chooseExistingField',
            'value' => '--div--',
        ];
        foreach (array_keys($tcaColumns) as $fieldName) {
            if (!empty($dbColumns[$fieldName]['Type']) && !in_array($fieldName, $excludedColumns, true)) {
                $columnInfo = '"' . trim(Type::lookupName($dbColumns[$fieldName]['Type']), ' \\') . '"';
                $parameters['items'][] = [
                    'label' => $fieldName . ' - ' . $columnInfo,
                    'value' => $fieldName,
                ];
            }
        }
    }

    public function getAvailableTtContentColumnsForPaletteFields(array &$parameters): void
    {
        $excludedColumns = [
            'uid',
            'pid',
            'CType',
            'editlock',
            'pi_flexform',
            'tx_impexp_origuid',
            't3ver_label',
            'tx_dce_dce',
            'tx_dce_index',
            'categories',
            'assets',
            'media',
            'tx_dce_new_container',
            'tx_dce_slug',
        ];
        // Do not offer fields used for TCA mapping. They are by default configured as passthrough.
        $mappedColumns = array_keys(Mapper::getDceFieldMappings());
        if (!empty($mappedColumns)) {
            $excludedColumns = array_merge($excludedColumns, $mappedColumns);
        }

        $tcaColumns = $GLOBALS['TCA']['tt_content']['columns'];
        $dbColumns = DatabaseUtility::adminGetFields('tt_content');

        $parameters['items'][] = ['--linebreak--', '--linebreak--'];
        $parameters['items'][] = ['--linebreak--', '--linebreak1--'];
        $parameters['items'][] = ['--linebreak--', '--linebreak2--'];
        $parameters['items'][] = ['--linebreak--', '--linebreak3--'];
        foreach (array_keys($tcaColumns) as $fieldName) {
            if (!empty($dbColumns[$fieldName]['Type']) && !in_array($fieldName, $excludedColumns, true)) {
                $label = '';
                if (isset($tcaColumns[$fieldName]['label'])) {
                    $label = trim($GLOBALS['LANG']->sL($tcaColumns[$fieldName]['label']), ': ');
                }
                if (empty($label)) {
                    $label = $fieldName;
                } else {
                    $label .= ' (' . $fieldName . ')';
                }
                $parameters['items'][] = [
                    'label' => $label,
                    'value' => $fieldName,
                ];
            }
        }
    }

    public function getAvailableWizardIcons(array &$parameters): void
    {
        // Default Icons
        $identifiers = [
            'content-header',
            'content-textpic',
            'content-bullets',
            'content-table',
            'content-special-uploads',
            'content-special-menu',
            'content-special-html',
            'content-special-div',
            'content-special-shortcut',
            'content-elements-login',
            'content-elements-mailform',
            'content-plugin',
        ];

        foreach ($identifiers as $identifier) {
            $parameters['items'][] = [
                'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:wizardIcon.' . $identifier,
                $identifier,
                $identifier,
            ];
        }

        // Custom Icon
        $ll = 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:';
        $parameters['items'][] = [$ll . 'wizardIcon.custom', '--div--'];
        $parameters['items'][] = [$ll . 'wizardIcon.customIcon', 'custom'];

        // TYPO3 Core Icons
        $parameters['items'][] = [$ll . 'wizardIcon.core', '--div--'];

        $absoluteIconDeclarationPath = GeneralUtility::getFileAbsFileName('EXT:core/Resources/Public/Icons/T3Icons/icons.json');
        $json = json_decode(file_get_contents($absoluteIconDeclarationPath) ?: '', true, 512, JSON_THROW_ON_ERROR);
        foreach ($json['icons'] ?? [] as $declaration) {
            $parameters['items'][] = [$declaration['identifier'], $declaration['identifier'], $declaration['identifier']];
        }

        // TYPO3 Extension Icons
        $parameters['items'][] = [$ll . 'wizardIcon.extensions', '--div--'];

        /** @var \ArrayObject $extensionIcons */
        $extensionIcons = $this->container->get('icons');
        foreach ($extensionIcons as $identifier => $config) {
            $parameters['items'][] = [$identifier, $identifier, $identifier];
        }
    }
}
