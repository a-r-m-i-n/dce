<?php

namespace T3\Dce\Controller;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */

use Psr\Http\Message\ResponseInterface;
use T3\Dce\Compatibility;
use T3\Dce\Components\DceContainer\ContainerFactory;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Domain\Repository\DceRepository;
use T3\Dce\Utility\TypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * DCE Controller
 * Handles the output of content element based on DCEs in front- and backend.
 */
class DceController extends ActionController
{
    /**
     * DCE Repository.
     *
     * @var DceRepository
     */
    protected $dceRepository;

    /**
     * TypoScript Utility.
     *
     * @var TypoScript
     */
    protected $typoScriptUtility;

    /**
     * @var array
     */
    public $temporaryDceProperties = [];

    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->dceRepository = $objectManager->get(DceRepository::class);
        $this->typoScriptUtility = $objectManager->get(TypoScript::class);
    }

    /**
     * Initialize Action.
     */
    public function initializeAction(): void
    {
        if (null === $this->settings) {
            $this->settings = [];
        }
        $this->settings = $this->typoScriptUtility->renderConfigurationArray($this->settings);
    }

    /**
     * Show Action which get called if a DCE get rendered in frontend.
     *
     * @return string|ResponseInterface output of dce in frontend
     */
    public function showAction()
    {
        $contentObject = $this->configurationManager->getContentObject()->data;
        $config = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        /** @var Dce $dce */
        $dce = $this->dceRepository->findAndBuildOneByUid(
            $this->dceRepository::extractUidFromCTypeOrIdentifier('dce_' . $config['pluginName']),
            $this->settings,
            $contentObject
        );

        if ($dce->getEnableContainer()) {
            if (ContainerFactory::checkContentElementForBeingRendered($dce->getContentObject())) {
                ContainerFactory::clearContentElementsToSkip($dce->getContentObject());

                return ' ';
            }
            $container = ContainerFactory::makeContainer($dce);

            if (!isset($this->responseFactory) || !Compatibility::isTypo3Version()) {
                return $container->render();
            }
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write($container->render());

            return $response;
        }

        if (!isset($this->responseFactory) || !Compatibility::isTypo3Version()) {
            return $dce->render();
        }
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($dce->render());

        return $response;
    }
}
