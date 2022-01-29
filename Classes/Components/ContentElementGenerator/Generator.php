<?php

namespace T3\Dce\Components\ContentElementGenerator;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DCE - Content Element Generator
 * Generates content elements in TYPO3 based on given DCE configuration.
 */
class Generator
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var InputDatabase
     */
    protected $inputDatabase;

    /**
     * @var OutputPlugin
     */
    protected $outputPlugin;

    /**
     * @var OutputTcaAndFlexForm
     */
    protected $outputTcaAndFlexForm;

    /**
     * Generator constructor.
     */
    public function __construct()
    {
        $this->inputDatabase = GeneralUtility::makeInstance(InputDatabase::class);
        $this->cacheManager = CacheManager::makeInstance();

        $this->outputPlugin = GeneralUtility::makeInstance(
            OutputPlugin::class,
            $this->inputDatabase,
            $this->cacheManager
        );
        $this->outputTcaAndFlexForm = GeneralUtility::makeInstance(
            OutputTcaAndFlexForm::class,
            $this->inputDatabase,
            $this->cacheManager
        );
    }

    public function makeTca(): void
    {
        try {
            $this->outputTcaAndFlexForm->generate();
        } catch (\Exception $e) {
        }
    }

    public function makePluginConfiguration(): void
    {
        try {
            $this->outputPlugin->generate();
        } catch (\Exception $e) {
        }
    }
}
