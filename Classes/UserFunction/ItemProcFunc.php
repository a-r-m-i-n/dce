<?php
namespace ArminVieweg\Dce\UserFunction;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */

/**
 * ItemProfFunc UserFunctions
 *
 * @package ArminVieweg\Dce
 */
class ItemProcFunc
{
    /**
     * Add DceFields to referenced $parameters['items'] array
     *
     * @param array $parameters Referenced parameter array
     * @param \TYPO3\CMS\Backend\Form\DataPreprocessor $dataPreprocessor
     * @return void
     */
    public function getDceFields(array &$parameters, $dataPreprocessor)
    {
        $parameters['items'][] = array('DCE Titel', '*dcetitle');
        if ($parameters['config']['size'] === 1) {
            $parameters['items'][] = array('Empty', '*empty');
        }

        $database = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();
        $dceFields = $database->exec_SELECTgetRows(
            '*',
            'tx_dce_domain_model_dcefield',
            'parent_dce=' . $parameters['row']['uid'] . ' AND deleted=0 AND type IN (0,2)',
            '',
            'sorting asc'
        );
        foreach ($dceFields as $dceField) {
            $label = $GLOBALS['LANG']->sL($dceField['title']);
            if ($dceField['type'] === '2') {
                $label .= ' (' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('section', 'dce') . ')';
            }
            $parameters['items'][] = array($label, $dceField['variable']);
        }
    }
}
