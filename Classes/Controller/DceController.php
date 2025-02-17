<?php

namespace T3\Dce\Controller;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use Psr\Http\Message\ResponseInterface;
use T3\Dce\Components\DceContainer\ContainerFactory;
use T3\Dce\Domain\Repository\DceRepository;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class DceController extends ActionController
{
    public function __construct(
        private readonly DceRepository $dceRepository,
        private readonly ContainerFactory $containerFactory
    ) {
    }

    public function showAction(): ResponseInterface
    {
        $contentObject = $this->request->getAttribute('currentContentObject');
        if ($contentObject instanceof ContentObjectRenderer) {
            $contentObject = $contentObject->data;
        }

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
