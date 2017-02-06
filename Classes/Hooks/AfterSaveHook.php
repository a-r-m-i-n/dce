<?php
namespace ArminVieweg\Dce\Hooks;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\DatabaseUtility;
use ArminVieweg\Dce\Utility\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * AfterSave Hook
 *
 * @package ArminVieweg\Dce
 */
class AfterSaveHook
{
    /** @var \TYPO3\CMS\Core\DataHandling\DataHandler */
    protected $dataHandler = null;

    /** @var int uid of current record */
    protected $uid = 0;

    /** @var array all properties of current record */
    protected $fieldArray = [];

    /** @var array extension settings */
    protected $extConfiguration = [];


    /**
     * If variable in given fieldSettings is set, it will be returned.
     * Otherwise a new variableName will be returned, based on the type of the field.
     *
     * @param array $fieldSettings
     * @return string
     */
    protected function getVariableNameFromFieldSettings(array $fieldSettings)
    {
        if (!isset($fieldSettings['variable']) || empty($fieldSettings['variable'])) {
            switch ($fieldSettings['type']) {
                default:
                case 0:
                    return uniqid('field_');

                case 1:
                    return uniqid('tab_');

                case 2:
                    return uniqid('section_');
            }
        }
        return $fieldSettings['variable'];
    }

    /**
     * Hook action
     *
     * @param $status
     * @param $table
     * @param $id
     * @param array $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     *
     * @return void
     */
    public function processDatamap_afterDatabaseOperations(
        $status,
        $table,
        $id,
        array $fieldArray,
        \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
    ) {
        $this->extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);
        $this->dataHandler = $pObj;
        $this->fieldArray = [];
        foreach ($fieldArray as $key => $value) {
            if (!empty($key)) {
                $this->fieldArray[$key] = $value;
            }
        }
        $this->uid = $this->getUid($id, $table, $status, $pObj);

        // Write flexform values to TCA, when enabled
        if ($table === 'tt_content' && $this->isDceContentElement($pObj)) {
            $this->checkAndUpdateDceRelationField();
            \ArminVieweg\Dce\Components\FlexformToTcaMapper\Mapper::saveFlexformValuesToTca(
                $this->uid,
                $this->fieldArray['pi_flexform']
            );
        }

        // When a DCE is disabled, also disable/hide the based content elements
        if ($table === 'tx_dce_domain_model_dce' && $status === 'update') {
            if (!isset($GLOBALS['TYPO3_CONF_VARS']['USER']['dce']['dceImportInProgress'])) {
                if (array_key_exists('hidden', $fieldArray) && $fieldArray['hidden'] == '1') {
                    $this->hideContentElementsBasedOnDce();
                }
            }
        }

        // Show hint when dcefield has been mapped to tca column
        if ($table === 'tx_dce_domain_model_dcefield' && $status === 'update') {
            if (array_key_exists('new_tca_field_name', $fieldArray) ||
                array_key_exists('new_tca_field_type', $fieldArray)
            ) {
                \ArminVieweg\Dce\Utility\FlashMessage::add(
                    'You did some changes (in DceField with uid ' . $this->uid . ') which affects the sql schema of ' .
                    'tt_content table. Please don\'t forget to update database schema (in e.g. Install Tool)!',
                    'SQL schema changes detected!',
                    \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE
                );
            }
        }

        // Adds or removes *containerflag from simple backend view, when container is en- or disabled
        if ($table === 'tx_dce_domain_model_dce' && $status === 'update' || $status === 'new') {
            if (array_key_exists('enable_container', $fieldArray)) {
                if ($fieldArray['enable_container'] === '1') {
                    $items = GeneralUtility::trimExplode(',', $fieldArray['backend_view_bodytext'], true);
                    $items[] = '*containerflag';
                } else {
                    $items = \TYPO3\CMS\Core\Utility\ArrayUtility::removeArrayEntryByValue(
                        GeneralUtility::trimExplode(',', $fieldArray['backend_view_bodytext'], true),
                        '*containerflag'
                    );
                }
                DatabaseUtility::getDatabaseConnection()->exec_UPDATEquery(
                    'tx_dce_domain_model_dce',
                    'uid=' . $this->uid,
                    [
                        'backend_view_bodytext' => implode(',', $items)
                    ]
                );
            }
        }

        // Clear cache if dce or dcefield has been created or updated
        if (in_array($table, ['tx_dce_domain_model_dce', 'tx_dce_domain_model_dcefield']) &&
            in_array($status, ['update', 'new'])
        ) {
            if ($this->extConfiguration['disableAutoClearCache'] == 0) {
                \ArminVieweg\Dce\Cache::clear();
            }
            // TODO: Deprecated remove in next major version (also in ext_conf_template.txt)
            if ($this->extConfiguration['disableAutoClearFrontendCache'] == 0) {
                $pObj->clear_cacheCmd('pages');
            }
        }
    }

    /**
     * Disables content elements based on this deactivated DCE. Also display flash message
     * about the amount of content elements affected and a notice, that these content elements
     * will not get re-enabled when enabling the DCE again.
     *
     * @return void
     */
    protected function hideContentElementsBasedOnDce()
    {
        $whereStatement = 'CType="dce_dceuid' . $this->uid . '" AND deleted=0 AND hidden=0';
        $res = DatabaseUtility::getDatabaseConnection()->exec_SELECTquery('uid', 'tt_content', $whereStatement);
        $updatedContentElementsCount = 0;
        while (($row = DatabaseUtility::getDatabaseConnection()->sql_fetch_assoc($res))) {
            $this->dataHandler->updateDB('tt_content', $row['uid'], ['hidden' => 1]);
            $updatedContentElementsCount++;
        }

        if ($updatedContentElementsCount === 0) {
            return;
        }

        $pathToLocallang = 'LLL:EXT:dce/Resources/Private/Language/locallang_mod.xml:';
        $message = LocalizationUtility::translate(
            $pathToLocallang . 'hideContentElementsBasedOnDce',
            'Dce',
            ['count' => $updatedContentElementsCount]
        );
        FlashMessage::add(
            $message,
            LocalizationUtility::translate($pathToLocallang . 'caution', 'Dce'),
            \TYPO3\CMS\Core\Messaging\FlashMessage::INFO
        );
    }

    /**
     * Checks the CType of current content element and return TRUE if it is a dce. Otherwise return FALSE.
     *
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     * @return bool
     */
    protected function isDceContentElement(\TYPO3\CMS\Core\DataHandling\DataHandler $pObj)
    {
        $datamap = $pObj->datamap;
        $datamap = reset($datamap);
        $datamap = reset($datamap);
        return (strpos($datamap['CType'], 'dce_dceuid') !== false);
    }

    /**
     * Investigates the uid of entry
     *
     * @param $id
     * @param $table
     * @param $status
     * @param $pObj
     * @return int
     */
    protected function getUid($id, $table, $status, $pObj)
    {
        $uid = $id;
        if ($status === 'new') {
            if (!$pObj->substNEWwithIDs[$id]) {
                //postProcessFieldArray
                $uid = 0;
            } else {
                //afterDatabaseOperations
                $uid = $pObj->substNEWwithIDs[$id];
                if (isset($pObj->autoVersionIdMap[$table][$uid])) {
                    $uid = $pObj->autoVersionIdMap[$table][$uid];
                }
            }
        }
        return intval($uid);
    }

    /**
     * Checks if dce relation (field tx_dce_dce) is empty. If it is empty, it will be filled by CType.
     * @return void
     */
    protected function checkAndUpdateDceRelationField()
    {
        $row = $this->dataHandler->recordInfo('tt_content', $this->uid, 'CType,tx_dce_dce');
        if (empty($row['tx_dce_dce'])) {
            $this->dataHandler->updateDB('tt_content', $this->uid, [
                'tx_dce_dce' => \ArminVieweg\Dce\Domain\Repository\DceRepository::extractUidFromCtype($row['CType'])
            ]);
        }
    }
}
