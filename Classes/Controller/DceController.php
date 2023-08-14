<?php

namespace T3\Dce\Controller;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2023 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use Psr\Http\Message\ResponseInterface;
use T3\Dce\Components\DceContainer\ContainerFactory;
use T3\Dce\Domain\Repository\DceRepository;
use T3\Dce\Utility\TypoScript;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class DceController extends ActionController
{
    public array $temporaryDceProperties = [];

    public function __construct(
        private readonly DceRepository $dceRepository,
        private readonly TypoScript $typoScriptUtility,
        private readonly ContainerFactory $containerFactory
    ) {
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

    public function showAction(): ResponseInterface
    {
        $contentObject = $this->configurationManager->getContentObject()->data;
        $config = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        $dceUid = $this->dceRepository::extractUidFromCTypeOrIdentifier('dce_' . $config['pluginName']);
        $fieldList = $this->dceRepository->completeFieldList($this->settings, $dceUid);

        $dce = $this->dceRepository->findAndBuildOneByUid(
            $dceUid,
            $fieldList,
            $contentObject
        );

        if ($dce->getEnableContainer()) {
            if (ContainerFactory::checkContentElementForBeingRendered($dce->getContentObject())) {
                ContainerFactory::clearContentElementsToSkip($dce->getContentObject());

                $response = $this->responseFactory->createResponse();
                $response->getBody()->write(' ');

                return $response;
            }
            $container = $this->containerFactory->makeContainer($dce);

            $response = $this->responseFactory->createResponse();
            $response->getBody()->write($container->render());

            return $response;
        }

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($dce->render());

        return $response;
    }
}
