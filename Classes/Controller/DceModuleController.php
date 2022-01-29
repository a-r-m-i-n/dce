<?php

namespace T3\Dce\Controller;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use Psr\Http\Message\ResponseInterface;
use T3\Dce\Compatibility;
use T3\Dce\Components\FlexformToTcaMapper\Mapper;
use T3\Dce\Domain\Repository\DceRepository;
use T3\Dce\Utility\File;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * DCE Module Controller
 * Provides the backend DCE module, for faster and easier access to DCEs.
 */
class DceModuleController extends ActionController
{
    /**
     * @var DceRepository
     */
    protected $dceRepository;

    /**
     * Initialize Action.
     */
    public function initializeAction(): void
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->dceRepository = $objectManager->get(DceRepository::class);
    }

    /**
     * Index Action.
     *
     * @return void|ResponseInterface
     */
    public function indexAction()
    {
        $this->view->assign('dces', $this->dceRepository->findAllAndStatics(true));

        if (isset($this->responseFactory) && Compatibility::isTypo3Version()) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write($this->view->render());

            return $response;
        }
    }

    /**
     * @param \T3\Dce\Domain\Model\Dce $dce
     * @param bool                     $perform
     *
     * @return void|ResponseInterface
     */
    public function updateTcaMappingsAction($dce, $perform = false)
    {
        $contentElements = $this->dceRepository->findContentElementsBasedOnDce($dce);
        $this->view->assign('contentElements', $contentElements);
        $this->view->assign('dce', $dce);
        if ($perform) {
            foreach ($contentElements as $contentElement) {
                Mapper::saveFlexformValuesToTca(
                    $contentElement,
                    $contentElement['pi_flexform']
                );
            }
            $this->view->assign('perform', true);
        }
        if (isset($this->responseFactory) && Compatibility::isTypo3Version()) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write($this->view->render());

            return $response;
        }
    }

    /**
     * Clears Caches Action.
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function clearCachesAction(): void
    {
        /** @var DataHandler $dataHandler */
        $dataHandler = $this->objectManager->get(DataHandler::class);
        $dataHandler->start([], []);
        $dataHandler->clear_cacheCmd('all');
        $translateKey = 'LLL:EXT:dce/Resources/Private/Language/locallang_mod.xlf:';
        $this->addFlashMessage(
            LocalizationUtility::translate($translateKey . 'clearCachesFlashMessage', 'dce'),
            LocalizationUtility::translate($translateKey . 'clearCaches', 'dce')
        );
        $this->redirect('index');
    }

    /**
     * Hall of fame Action.
     *
     * @return void|ResponseInterface
     */
    public function hallOfFameAction()
    {
        $donators = File::openJsonFile('EXT:dce/Resources/Private/Data/Donators.json');
        shuffle($donators);
        $this->view->assign('donators', $donators);

        if (isset($this->responseFactory) && Compatibility::isTypo3Version()) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write($this->view->render());

            return $response;
        }
    }
}
