<?php

namespace T3\Dce\Controller;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use Psr\Http\Message\ResponseInterface;
use T3\Dce\Components\FlexformToTcaMapper\Mapper;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Domain\Repository\DceRepository;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class DceModuleController extends ActionController
{
    private ModuleTemplate $moduleTemplate;

    public function __construct(
        private readonly DceRepository $dceRepository,
        private readonly DataHandler $dataHandler,
        private readonly ModuleTemplateFactory $moduleTemplateFactory
    ) {
    }

    protected function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->moduleTemplate->setTitle('DCE Backend Module');
        $this->moduleTemplate->getDocHeaderComponent()->disable();
    }

    public function indexAction()
    {
        $this->moduleTemplate->assign('dces', $this->dceRepository->findAllAndStatics(true));

        return $this->moduleTemplate->renderResponse('DceModule/Index');
    }

    public function updateTcaMappingsAction(Dce $dce, bool $perform = false): ResponseInterface
    {
        $contentElements = $this->dceRepository->findContentElementsBasedOnDce($dce);
        $this->moduleTemplate->assign('contentElements', $contentElements);
        $this->moduleTemplate->assign('dce', $dce);
        if ($perform) {
            foreach ($contentElements as $contentElement) {
                Mapper::saveFlexformValuesToTca(
                    $contentElement,
                    $contentElement['pi_flexform']
                );
            }
            $this->moduleTemplate->assign('perform', true);
        }

        return $this->moduleTemplate->renderResponse('DceModule/UpdateTcaMappings');
    }

    /**
     * Clears Caches Action.
     */
    public function clearCachesAction(): ResponseInterface
    {
        $this->dataHandler->start([], []);
        $this->dataHandler->clear_cacheCmd('all');
        $translateKey = 'LLL:EXT:dce/Resources/Private/Language/locallang_mod.xlf:';
        $this->addFlashMessage(
            LocalizationUtility::translate($translateKey . 'clearCachesFlashMessage', 'dce'),
            LocalizationUtility::translate($translateKey . 'clearCaches', 'dce')
        );

        return $this->redirect('index');
    }

    public function hallOfFameAction(): ResponseInterface
    {
        $content = file_get_contents(GeneralUtility::getFileAbsFileName('EXT:dce/Resources/Private/Data/Donators.json'));
        $donators = json_decode($content, true, 512, JSON_THROW_ON_ERROR) ?? [];
        shuffle($donators);
        $this->view->assign('donators', $donators);

        $this->moduleTemplate->assign('donators', $donators);

        return $this->moduleTemplate->renderResponse('DceModule/HallOfFame');
    }
}
