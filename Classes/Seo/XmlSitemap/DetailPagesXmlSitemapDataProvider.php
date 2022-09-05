<?php

declare(strict_types=1);

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2022 Armin Vieweg <armin@v.ieweg.de>
 */
namespace T3\Dce\Seo\XmlSitemap;

use Psr\Http\Message\ServerRequestInterface;
use T3\Dce\Compatibility;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Domain\Repository\DceRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Seo\XmlSitemap\AbstractXmlSitemapDataProvider;

/**
 * Class to generate a XML sitemap for DCE content elements, with detail pages
 * This class has been copied from TYPO3 CMS Core: \TYPO3\CMS\Seo\XmlSitemap\PagesXmlSitemapDataProvider
 */
class DetailPagesXmlSitemapDataProvider extends AbstractXmlSitemapDataProvider
{
    /**
     * @var DceRepository|null
     */
    protected $dceRepository;


    public function __construct(ServerRequestInterface $request, string $key, array $config = [], ContentObjectRenderer $cObj = null)
    {
        parent::__construct($request, $key, $config, $cObj);

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->dceRepository = $objectManager->get(DceRepository::class);

        $this->generateItems();
    }

    protected function generateItems(): void
    {
        $pageRepository = GeneralUtility::makeInstance(Compatibility::getPageRepositoryClassName());
        $pages = $pageRepository->getPagesOverlay($this->getPages());
        $languageAspect = $this->getCurrentLanguageAspect();
        foreach ($pages as $page) {
            if (!$pageRepository->isPageSuitableForLanguage($page, $languageAspect)) {
                continue;
            }

            $this->items[] = [
                'uid' => $page['uid'],
                'lastMod' => (int)($page['SYS_LASTCHANGED'] ?: $page['tstamp']),
                'changefreq' => $page['sitemap_changefreq'],
                'priority' => (float)$page['sitemap_priority'],
            ];

            // Check for DCE content elements, with detail pages BEGIN
            $dceElements = $this->getDceContentElementsWithDetailPage($page['uid']);
            foreach ($dceElements as $dceElement) {
                if ($dceElement['sys_language_uid'] !== $languageAspect->getId()) {
                    continue;
                }

                /** @var Dce $dce */
                $dce = $dceElement['_dce'];
                $this->items[] = [
                    'uid' => $dceElement['uid'],
                    'lastMod' => $dceElement['tstamp'],
                    'changefreq' => $page['sitemap_changefreq'],
                    'priority' => (float)$page['sitemap_priority'],

                    'pid' => $dceElement['pid'],
                    'detailpageIdentifier' => $dce->getDetailpageIdentifier(),
                ];
            }
            // Check for DCE content elements, with detail pages END
        }
    }

    /**
     * @return array
     */
    protected function getPages(): array
    {
        if (!empty($this->config['rootPage'])) {
            $rootPageId = (int)$this->config['rootPage'];
        } else {
            $site = $this->request->getAttribute('site');
            $rootPageId = $site->getRootPageId();
        }

        $excludePagesRecursive = GeneralUtility::intExplode(',', $this->config['excludePagesRecursive'] ?? '', true);
        $excludePagesRecursiveWhereClause = '';
        if ($excludePagesRecursive !== []) {
            $excludePagesRecursiveWhereClause = sprintf('uid NOT IN (%s)', implode(',', $excludePagesRecursive));
        }

        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $treeList = $cObj->getTreeList(-$rootPageId, 99, 0, false, '', $excludePagesRecursiveWhereClause);
        $treeListArray = GeneralUtility::intExplode(',', $treeList);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');

        $constraints = [
            $queryBuilder->expr()->in('uid', $treeListArray),
        ];

        if (!empty($this->config['additionalWhere'])) {
            $constraints[] = QueryHelper::quoteDatabaseIdentifiers($queryBuilder->getConnection(), QueryHelper::stripLogicalOperatorPrefix($this->config['additionalWhere']));
        }

        if (!empty($this->config['excludedDoktypes'])) {
            $excludedDoktypes = GeneralUtility::intExplode(',', $this->config['excludedDoktypes']);
            if (!empty($excludedDoktypes)) {
                $constraints[] = $queryBuilder->expr()->notIn('doktype', implode(',', $excludedDoktypes));
            }
        }
        $pages = $queryBuilder->select('*')
            ->from('pages')
            ->where(...$constraints)
            ->orderBy('uid', 'ASC')
            ->execute()
            ->fetchAll();

        return $pages;
    }

    /**
     * @return array
     */
    protected function getDceContentElementsWithDetailPage(int $pageUid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        $dceRows = $queryBuilder->select('*')
            ->from('tt_content')
            ->where($queryBuilder->expr()->eq('pid', $pageUid))
            ->andWhere($queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter($queryBuilder->escapeLikeWildcards('dce_') . '%')))
            ->orderBy('colPos', 'ASC')
            ->addOrderBy('sorting', 'ASC')
            ->execute()
            ->fetchAll();


        $dceRowsWithDetailPage = [];

        if (!empty($dceRows)) {
            foreach ($dceRows as $index => $dceRow) {
                $dce = $this->dceRepository->getDceInstance($dceRow['uid']);
                if ($dce->getEnableDetailpage()) {
                    $dceRow['_dce'] = $dce;
                    $dceRowsWithDetailPage[] = $dceRow;
                }
            }
        }

        return $dceRowsWithDetailPage;
    }

    /**
     * @return LanguageAspect
     */
    protected function getCurrentLanguageAspect(): LanguageAspect
    {
        return GeneralUtility::makeInstance(Context::class)->getAspect('language');
    }

    /**
     * @param array $data
     * @return array
     */
    protected function defineUrl(array $data): array
    {
        $typoLinkConfig = [
            'parameter' => $data['uid'],
            'forceAbsoluteUrl' => 1,
        ];

        // Check for DCE content elements, with detail pages BEGIN
        if (array_key_exists('pid', $data) && array_key_exists('detailpageIdentifier', $data)) {
            $typoLinkConfig['parameter'] = $data['pid'];
            $typoLinkConfig['additionalParams'] = '&' . $data['detailpageIdentifier'] . '=' . $data['uid'];
        }
        // Check for DCE content elements, with detail pages END

        $data['loc'] = $this->cObj->typoLink_URL($typoLinkConfig);

        return $data;
    }
}
