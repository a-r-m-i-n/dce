<?php
namespace ArminVieweg\Dce\Controller;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Utility\File;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * DCE Module Controller
 * Provides the backend DCE module, for faster and easier access to DCEs.
 *
 * @package ArminVieweg\Dce
 */
class DceModuleController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var \ArminVieweg\Dce\Domain\Repository\DceRepository
     * @inject
     */
    protected $dceRepository;

    /**
     * Index Action
     *
     * @return void
     */
    public function indexAction()
    {
        $extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dce']);
        $enableUpdateCheck = (bool)$extConfiguration['enableUpdateCheck'];

        $this->view->assign('dces', $this->dceRepository->findAllAndStatics(true));
        $this->view->assign('enableUpdateCheck', $enableUpdateCheck);
    }

    /**
     * @param \ArminVieweg\Dce\Domain\Model\Dce $dce
     * @param bool $perform
     * @return void
     */
    public function updateTcaMappingsAction(\ArminVieweg\Dce\Domain\Model\Dce $dce, $perform = false)
    {
        $contentElements = $this->dceRepository->findContentElementsBasedOnDce($dce);
        $this->view->assign('contentElements', $contentElements);
        $this->view->assign('dce', $dce);
        if ($perform) {
            foreach ($contentElements as $contentElement) {
                \ArminVieweg\Dce\Components\FlexformToTcaMapper\Mapper::saveFlexformValuesToTca(
                    $contentElement['uid'],
                    $contentElement['pi_flexform']
                );
            }
            $this->view->assign('perform', true);
        }
    }

    /**
     * Clears Caches Action
     *
     * @return void
     */
    public function clearCachesAction()
    {
        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
        $dataHandler = $this->objectManager->get(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
        $dataHandler->start([], []);
        $dataHandler->clear_cacheCmd('system');
        $dataHandler->clear_cacheCmd('pages');
        $translateKey = 'LLL:EXT:dce/Resources/Private/Language/locallang_mod.xml:';
        $this->addFlashMessage(
            LocalizationUtility::translate($translateKey . 'clearCachesFlashMessage', 'dce'),
            LocalizationUtility::translate($translateKey . 'clearCaches', 'dce')
        );
        $this->redirect('index');
        return;
    }

    /**
     * Hall of fame Action
     *
     * @return void
     */
    public function hallOfFameAction()
    {
        $donators = File::openJsonFile('EXT:dce/Resources/Private/Data/Donators.json');
        $this->view->assign('donators', $donators);
    }
}
