<?php
namespace ArminVieweg\Dce;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generates "ext_localconf.php" and "ext_tables.php" located
 * in "typo3temp/Cache/Code/cache_dce/" which contains
 * the whole DCE configurations used by TYPO3.
 *
 * @package ArminVieweg\Dce
 */
class Cache
{
    /**
     * Path of DCE cache files
     */
    const CACHE_PATH = 'typo3temp/Cache/Code/cache_dce/';

    /**
     * Filename for cache type ext_localconf
     */
    const CACHE_TYPE_EXTLOCALCONF = 'ext_localconf.php';

    /**
     * Filename for cache type ext_tables
     */
    const CACHE_TYPE_EXTTABLES = 'ext_tables.php';


    /**
     * @var \ArminVieweg\Dce\Utility\FluidTemplate
     */
    protected $fluidTemplateUtility;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fluidTemplateUtility = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\FluidTemplate');
    }

    /**
     * Create localconf
     *
     * @return void
     */
    public function createLocalconf()
    {
        \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();
        $this->fluidTemplateUtility->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Private/Templates/DceSource/localconf.html'
        );

        /** @var \ArminVieweg\Dce\Utility\StaticDce $staticDceUtility */
        $staticDceUtility = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\StaticDce');

        $dces = array_merge($this->getDatabaseDce(), $staticDceUtility->getAll());
        $this->fluidTemplateUtility->assign('dceArray', $dces);
        $this->saveCacheData(self::CACHE_TYPE_EXTLOCALCONF, $this->fluidTemplateUtility->render());
    }

    /**
     * Create ext_tables
     *
     * @return void
     */
    public function createExtTables()
    {
        \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();
        $this->fluidTemplateUtility->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('dce') . 'Resources/Private/Templates/DceSource/ext_tables.html'
        );

        /** @var \ArminVieweg\Dce\Utility\StaticDce $staticDceUtility */
        $staticDceUtility = GeneralUtility::makeInstance('ArminVieweg\Dce\Utility\StaticDce');

        $dces = array_merge($this->getDatabaseDce(), $staticDceUtility->getAll(true));

        if (ExtensionManagementUtility::isLoaded('gridelements')) {
            $dces = $this->ensureGridelementsFieldCompatibility($dces);
        }
        $this->fluidTemplateUtility->assign('dceArray', $dces);
        $this->saveCacheData(self::CACHE_TYPE_EXTTABLES, $this->fluidTemplateUtility->render());
    }

    /**
     * Stores given data under given path, create folder if not existing and fixes file permissions.
     *
     * @param string $cacheType Filename of cache file
     * @param string $data Content to write to cache
     * @return void
     * @throws \Exception
     */
    protected function saveCacheData($cacheType, $data)
    {
        self::validateCacheType($cacheType);
        self::checkCacheBasePath();

        $cachePath = PATH_site . self::CACHE_PATH . $cacheType;
        if (is_writable(dirname($cachePath))) {
            file_put_contents($cachePath, $data);
            GeneralUtility::fixPermissions($cachePath);
        } else {
            throw new \Exception('Not able to write to cache file "' . $cachePath . '"!', 1438174706);
        }
    }

    /**
     * Clears the DCE cache
     *
     * @return void
     */
    public static function clear()
    {
        $cachePath = PATH_site . self::CACHE_PATH;
        if (file_exists($cachePath . self::CACHE_TYPE_EXTLOCALCONF)) {
            unlink($cachePath . self::CACHE_TYPE_EXTLOCALCONF);
        }
        if (file_exists($cachePath . self::CACHE_TYPE_EXTTABLES)) {
            unlink($cachePath . self::CACHE_TYPE_EXTTABLES);
        }
    }

    /**
     * Checks if given cache type is valid. If not an exception will be thrown.
     *
     * @param string $cacheType
     * @return void
     * @throws \UnexpectedValueException
     */
    protected static function validateCacheType($cacheType)
    {
        if ($cacheType !== self::CACHE_TYPE_EXTLOCALCONF && $cacheType !== self::CACHE_TYPE_EXTTABLES) {
            throw new \UnexpectedValueException(
                'Given cache type not allowed. Use \ArminVieweg\Dce\Cache::CACHE_TYPE_* constants only.',
                1438174705
            );
        }
    }

    /**
     * Checks if given cacheType exists
     *
     * @param string $cacheType
     * @return bool true if cache already exists, false if not
     */
    public static function cacheExists($cacheType)
    {
        self::validateCacheType($cacheType);
        return file_exists(PATH_site . self::CACHE_PATH . $cacheType);
    }

    /**
     * Checks if expected folder for DCE cache is exsting. If not
     * it creates the folder.
     */
    protected function checkCacheBasePath()
    {
        $cachePath = PATH_site . self::CACHE_PATH;
        if (!file_exists($cachePath) || !is_dir($cachePath)) {
            GeneralUtility::mkdir_deep($cachePath);
        }
    }

    /**
     * Returns all available DCE as array with this format
     * (just most important fields listed):
     *
     * DCE
     *    |_ uid
     *    |_ title
     *    |_ tabs <array>
     *    |    |_ title
     *    |    |_ fields <array>
     *    |        |_ uid
     *    |        |_ title
     *    |        |_ variable
     *    |        |_ configuration
     *    |_ ...
     *
     * @return array with DCE -> containing tabs -> containing fields
     */
    protected function getDatabaseDce()
    {
        /** @var $databaseConnection \TYPO3\CMS\Dbal\Database\DatabaseConnection */
        $databaseConnection = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();

        $res = $databaseConnection->exec_SELECTquery(
            '*',
            'tx_dce_domain_model_dce',
            'deleted=0 AND pid=0',
            '',
            'sorting asc'
        );

        $dce = array();
        while (($row = $databaseConnection->sql_fetch_assoc($res))) {
            $res2 = $databaseConnection->exec_SELECT_mm_query(
                '*',
                'tx_dce_domain_model_dce',
                'tx_dce_dce_dcefield_mm',
                'tx_dce_domain_model_dcefield',
                ' AND tx_dce_domain_model_dce.uid = ' . $row['uid'] .
                ' AND tx_dce_domain_model_dcefield.deleted = 0 AND tx_dce_domain_model_dcefield.hidden = 0',
                '',
                'tx_dce_dce_dcefield_mm.sorting asc'
            );

            if (TYPO3_MODE === 'FE') {
                $generalTabLabel = LocalizationUtility::translate('generaltab', 'dce');
            } else {
                $generalTabLabel = LocalizationUtility::translate(
                    'LLL:EXT:dce/Resources/Private/Language/locallang.xml:generaltab',
                    'dce'
                );
            }
            $tabs = array(0 => array('title' => $generalTabLabel, 'fields' => array()));
            $index = 0;
            while ($row2 = $databaseConnection->sql_fetch_assoc($res2)) {
                if ($row2['type'] === '1') {
                    // Create new Tab
                    $index++;
                    $tabs[$index] = array();
                    $tabs[$index]['title'] = $row2['title'];
                    $tabs[$index]['fields'] = array();
                    continue;
                } elseif ($row2['type'] === '2') {
                    $res3 = $databaseConnection->exec_SELECTquery(
                        '*',
                        'tx_dce_domain_model_dcefield as a,' .
                        'tx_dce_dcefield_sectionfields_mm,' .
                        'tx_dce_domain_model_dcefield as b',
                        'a.uid=tx_dce_dcefield_sectionfields_mm.uid_local ' .
                        'AND b.uid=tx_dce_dcefield_sectionfields_mm.uid_foreign AND a.uid = ' . $row2['uid'] .
                        ' AND b.deleted = 0 AND b.hidden = 0',
                        '',
                        'tx_dce_dcefield_sectionfields_mm.sorting asc'
                    );
                    $sectionFields = array();
                    while (($row3 = $databaseConnection->sql_fetch_assoc($res3))) {
                        if ($row3['type'] === '0') {
                            // add fields of section to fields
                            $sectionFields[] = $row3;
                        }
                    }
                    $row2['section_fields'] = $sectionFields;
                    $tabs[$index]['fields'][] = $row2;
                } else {
                    // usual element
                    $tabs[$index]['fields'][] = $row2;
                }
            }
            if (count($tabs[0]['fields']) === 0) {
                unset($tabs[0]);
            }

            $row['tabs'] = $tabs;
            $row['hasCustomWizardIcon'] = ($row['wizard_icon'] === 'custom') ? true : false;
            $dce[] = $row;
        }
        return $dce;
    }

    /**
     * Iterates through given DCE rows and add field "" to DCE palettes
     * if not already set.
     *
     * @param array $dces
     * @return array
     */
    protected function ensureGridelementsFieldCompatibility($dces)
    {
        foreach ($dces as $key => $dceRow) {
            $paletteFields = GeneralUtility::trimExplode(',', $dceRow['palette_fields'], true);
            if (!in_array('colPos', $paletteFields)) {
                $paletteFields[] = 'colPos';
            }
            if (!in_array('tx_gridelements_container', $paletteFields)) {
                $paletteFields[] = 'tx_gridelements_container ';
            }
            $dces[$key]['palette_fields'] = implode(', ', $paletteFields);
        }
        return $dces;
    }
}
