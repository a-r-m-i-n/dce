<?php
namespace ArminVieweg\Dce\Controller;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DCE Controller
 * Handles the output of content element based on DCEs in front- and backend.
 *
 * @package ArminVieweg\Dce
 */
class DceController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * DCE Repository
     *
     * @var \ArminVieweg\Dce\Domain\Repository\DceRepository
     * @inject
     */
    protected $dceRepository;

    /**
     * TypoScript Utility
     *
     * @var \ArminVieweg\Dce\Utility\TypoScript
     * @inject
     */
    protected $typoScriptUtility;


    /**
     * Initialize Action
     *
     * @return void
     */
    public function initializeAction()
    {
        if ($this->settings === null) {
            $this->settings = array();
        }
        $this->settings = $this->typoScriptUtility->renderConfigurationArray($this->settings);
    }

    /**
     * Show Action which get called if a DCE get rendered in frontend
     *
     * @return string output of dce in frontend
     */
    public function showAction()
    {
        $contentObject = $this->configurationManager->getContentObject()->data;
        $config = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        /** @var $dce \ArminVieweg\Dce\Domain\Model\Dce */
        $dce = $this->dceRepository->findAndBuildOneByUid(
            $this->dceRepository->extractUidFromCtype($config['pluginName']),
            $this->settings,
            $contentObject
        );

        if ($dce->getEnableDetailpage()
            && intval($contentObject['uid']) === intval(GeneralUtility::_GP($dce->getDetailpageIdentifier()))
        ) {
            return $dce->renderDetailpage();
        }
        return $dce->render();
    }

    /**
     * Render preview action
     *
     * @return string
     */
    public function renderPreviewAction()
    {
        $uid = intval($this->settings['dceUid']);
        $contentObject = $this->getContentObject($this->settings['contentElementUid']);
        $previewType = $this->settings['previewType'];

        $this->settings = $this->simulateContentElementSettings($this->settings['contentElementUid']);

        /** @var $dce \ArminVieweg\Dce\Domain\Model\Dce */
        $dce = clone $this->dceRepository->findAndBuildOneByUid(
            $uid,
            $this->settings,
            $contentObject
        );

        if ($previewType === 'header') {
            return $dce->renderHeaderPreview();
        }
        return $dce->renderBodytextPreview();
    }

    /**
     * Simulates content element settings, which is necessary in backend context
     *
     * @param int $contentElementUid
     * @return array
     */
    protected function simulateContentElementSettings($contentElementUid)
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pi_flexform', 'tt_content', 'uid = ' . $contentElementUid);
        $flexform = GeneralUtility::xml2array(current($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)));

        $this->temporaryDceProperties = array();
        if (is_array($flexform)) {
            $this->dceRepository->getVdefValues($flexform, $this);
        }
        return $this->temporaryDceProperties;
    }

    /**
     * Returns an array with properties of content element with given uid
     *
     * @param int $uid of content element to get
     * @return array with all properties of given content element uid
     */
    protected function getContentObject($uid)
    {
        return $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', 'uid=' . $uid);
    }
}
