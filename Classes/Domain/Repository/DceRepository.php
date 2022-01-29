<?php

namespace T3\Dce\Domain\Repository;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */

use T3\Dce\Compatibility;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Domain\Model\DceField;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\FlexformService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Resource\Collection\AbstractFileCollection;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileCollectionRepository;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * DCE repository.
 */
class DceRepository extends Repository
{
    /**
     * @var Dce[]
     */
    protected static $dceInstanceCache = [];

    /**
     * @var Typo3QuerySettings|null
     */
    private static $defaultQuerySettingsInstance;

    /**
     * DceRepository constructor.
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);
    }

    /**
     * Returns database DCEs and static DCEs as merged array.
     */
    public function findAllAndStatics(bool $includeHidden = false): array
    {
        if ($includeHidden) {
            /** @var Typo3QuerySettings $querySettings */
            $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
            $querySettings->setIgnoreEnableFields(true);
            $this->setDefaultQuerySettings($querySettings);
        }
        $this->setDefaultOrderings(['sorting' => QueryInterface::ORDER_ASCENDING]);

        return $this->findAll()->toArray();
    }

    /**
     * Returns a DCE from the instance cache.
     *
     * @param int $uid Content object uid
     */
    public function findInCacheByContentObjectUid(int $uid): ?Dce
    {
        if (array_key_exists($uid, static::$dceInstanceCache)) {
            return static::$dceInstanceCache[$uid];
        }

        return null;
    }

    /**
     * Finds and build a DCE. The given uid loads the DCE structure and the
     * fieldList triggers the fillFields which gives the dce its contents
     * and values.
     *
     * @param bool $doNotCache If true forces to not use the internal cache
     */
    public function findAndBuildOneByUid(
        int $uid,
        array $fieldList,
        array $contentObject,
        bool $doNotCache = false
    ): Dce {
        if (!$doNotCache && array_key_exists($contentObject['uid'], static::$dceInstanceCache)) {
            return static::$dceInstanceCache[$contentObject['uid']];
        }
        $this->disableRespectOfEnableFields();

        /** @var Dce $dce */
        $dce = $this->findByUid($uid);

        if (!$dce instanceof Dce) {
            throw new \UnexpectedValueException('No DCE found with uid "' . $uid . '".', 1328613288);
        }
        $dce = clone $dce;
        $this->cloneFields($dce);
        $this->processFillingFields($dce, $contentObject, $fieldList);
        $dce->setContentObject($this->resolveContentObjectRelations($contentObject));
        static::$dceInstanceCache[$contentObject['uid']] = $dce;

        return $dce;
    }

    /**
     * Returns content element rows based on given DCE object.
     */
    public function findContentElementsBasedOnDce(Dce $dce): ?array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');

        return $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter($dce->getIdentifier(), \PDO::PARAM_STR)
                )
            )
            ->execute()
            ->fetchAll() ?? null;
    }

    /**
     * Clones the fields of a dce separately, because cloning the dce just
     * refers the fields.
     */
    protected function cloneFields(Dce $dce): void
    {
        /** @var ObjectStorage $clonedFields */
        $clonedFields = GeneralUtility::makeInstance(ObjectStorage::class);
        /** @var DceField $field */
        foreach ($dce->getFields() ?? [] as $field) {
            $field = clone $field;
            if (DceField::TYPE_ELEMENT === $field->getType() || DceField::TYPE_SECTION === $field->getType()) {
                if ($field->getSectionFields()) {
                    /** @var ObjectStorage $clonedSectionFields */
                    $clonedSectionFields = GeneralUtility::makeInstance(
                        ObjectStorage::class
                    );
                    foreach ($field->getSectionFields() as $sectionField) {
                        /** @var DceField $clonedSectionField */
                        $clonedSectionField = clone $sectionField;
                        $clonedSectionField->setValue(null);
                        $clonedSectionFields->attach($clonedSectionField);
                        $field->setSectionFields($clonedSectionFields);
                    }
                }
                $clonedFields->attach($field);
                $dce->setFields($clonedFields);
            }
        }
    }

    /**
     * Disable the respect of enable fields in defaultQuerySettings.
     */
    protected function disableRespectOfEnableFields(): void
    {
        if (!self::$defaultQuerySettingsInstance) {
            // @var $querySettings Typo3QuerySettings
            self::$defaultQuerySettingsInstance = GeneralUtility::makeInstance(Typo3QuerySettings::class);
            self::$defaultQuerySettingsInstance->setIgnoreEnableFields(true)->setIncludeDeleted(true);
        }
        $this->setDefaultQuerySettings(self::$defaultQuerySettingsInstance);
    }

    /**
     * Walk through the fields and section fields to fill them.
     *
     * @param array $fieldList Field list. Key must contain field variable, value its value.
     */
    protected function processFillingFields(
        Dce $dce,
        array $contentObject,
        array $fieldList = null
    ): void {
        $fieldList = $fieldList ?: [];
        foreach ($fieldList as $fieldVariable => $fieldValue) {
            $dceField = $dce->getFieldByVariable($fieldVariable);
            if ($dceField) {
                if (\is_array($fieldValue)) {
                    $i = 0;
                    foreach ($fieldValue as $sectionFieldValues) {
                        $sectionFieldValues = current($sectionFieldValues);

                        // Check if $sectionFieldValues is empty - if so, go to next
                        if (!empty($sectionFieldValues)) {
                            foreach ($sectionFieldValues as $sectionFieldVariable => $sectionFieldValue) {
                                $sectionField = $dceField->getSectionFieldByVariable($sectionFieldVariable);
                                if ($sectionField instanceof DceField) {
                                    $xmlIdent = $dce->getUid() . '-' . $dceField->getVariable() . '-' .
                                        $sectionField->getVariable();
                                    $this->fillFields(
                                        $sectionField,
                                        $sectionFieldValue,
                                        $xmlIdent,
                                        true,
                                        $contentObject,
                                        $i
                                    );
                                }
                            }
                            ++$i;
                        }
                    }
                } else {
                    $xmlIdent = $dce->getUid() . '-' . $dceField->getVariable();
                    $this->fillFields($dceField, $fieldValue, $xmlIdent, false, $contentObject);
                }
            }
        }
    }

    /**
     * Fills the value of given field. If field has special properties some
     * objects or database operations will be do,if not just the given
     * $fieldValue will be add to $dceField->_value. Value of sectionFields
     * will be filled differently.
     */
    protected function fillFields(
        DceField $dceField,
        string $fieldValue,
        string $xmlIdentifier,
        bool $isSectionField = false,
        array $contentObject = [],
        int $sectionFieldIndex = 0
    ): void {
        $xmlWrapping = 'xml-' . $xmlIdentifier;
        $dceFieldConfiguration = GeneralUtility::xml2array(
            '<' . $xmlWrapping . '>' . $dceField->getConfiguration() . '</' . $xmlWrapping . '>'
        );

        if (\is_array($dceFieldConfiguration)) {
            $dceFieldConfiguration = $dceFieldConfiguration['config'];
            if (isset($dceFieldConfiguration['dce_load_schema']) && !empty($dceFieldConfiguration['dce_load_schema']) && $this->hasRelatedObjects($dceFieldConfiguration)) {
                $objects = $this->createObjectsByFieldConfiguration(
                    $fieldValue,
                    $dceFieldConfiguration,
                    $contentObject
                );
            }
            if (isset($objects, $dceFieldConfiguration['dce_get_first'])) {
                $objects = current($objects);
            }
        }
        if (false === $isSectionField) {
            if (isset($objects)) {
                $dceField->setValue($objects);
            } else {
                $dceField->setValue($fieldValue);
            }
        } else {
            $sectionFieldValues = $dceField->getValue();
            if (!\is_array($sectionFieldValues)) {
                $sectionFieldValues = [];
            }

            $sectionFieldValues[$sectionFieldIndex] = $objects ?? $fieldValue;
            $dceField->setValue($sectionFieldValues);
        }
    }

    /**
     * Detects fields.
     *
     * @return array The record with DCE attributes
     */
    protected function getDceFieldsByRecord(array $record): array
    {
        $flexformData = FlexformService::get()->convertFlexFormContentToArray($record['pi_flexform'], 'lDEF', 'vDEF');

        return isset($flexformData['settings']) && \is_array($flexformData['settings'])
            ? $flexformData['settings']
            : [];
    }

    /**
     * Extracts and returns the uid from given DCE CType or identifier.
     * Returns FALSE if CType is not a DCE one.
     *
     * @param string|array $cType or DCE identifier
     *
     * @return int|null uid or null
     * @static
     */
    public static function extractUidFromCTypeOrIdentifier($cType): ?int
    {
        if (\is_array($cType)) {
            // For any reason the CType can be an array with one entry
            $cType = reset($cType);
        }
        if (!$cType) {
            return null;
        }
        if (StringUtility::beginsWith($cType, 'dceuid')) {
            return (int)substr($cType, 6);
        }
        if (StringUtility::beginsWith($cType, 'dce_dceuid')) {
            return (int)substr($cType, 10);
        }
        if (StringUtility::beginsWith($cType, 'dce_')) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dce');
            $row = $queryBuilder
                ->select('uid')
                ->from('tx_dce_domain_model_dce')
                ->where(
                    $queryBuilder->expr()->eq(
                        'identifier',
                        $queryBuilder->createNamedParameter(addslashes(substr($cType, 4)), \PDO::PARAM_STR)
                    )
                )
                ->execute()
                ->fetch();

            if (isset($row['uid'])) {
                return (int)$row['uid'];
            }
        }

        return null;
    }

    /**
     * Converts a given dce uid to a dce CType.
     */
    public static function convertUidToCtype(int $uid): ?string
    {
        if (0 === $uid) {
            return null;
        }

        return 'dce_dceuid' . $uid;
    }

    /**
     * Checks if given field configuration allows to load sub items
     * (assoc array or objects).
     */
    protected function hasRelatedObjects(array $fieldConfiguration): bool
    {
        return \in_array($fieldConfiguration['type'], ['group', 'inline', 'select'])
                && (('select' === $fieldConfiguration['type'] && !empty($fieldConfiguration['foreign_table']))
                    || ('inline' === $fieldConfiguration['type'] && !empty($fieldConfiguration['foreign_table']))
                    || ('group' === $fieldConfiguration['type'] && !empty($fieldConfiguration['allowed'])));
    }

    /**
     * Creates array of assoc array or objects, depending
     * on given field configuration.
     *
     * @param string $fieldValue    Comma separated list of uids
     * @param array  $contentObject Content object (required by FAL viewhelper)
     */
    protected function createObjectsByFieldConfiguration(
        string $fieldValue,
        array $dceFieldConfiguration,
        array $contentObject
    ): array {
        $objects = [];

        if ('group' === $dceFieldConfiguration['type']) {
            $className = $dceFieldConfiguration['allowed'];
            $tableNames = GeneralUtility::trimExplode(',', $dceFieldConfiguration['allowed'], true);
        } else {
            $className = $dceFieldConfiguration['foreign_table'];
            $tableNames = GeneralUtility::trimExplode(',', $dceFieldConfiguration['foreign_table'], true);
        }

        $specialClass = null;
        if (isset($dceFieldConfiguration['dce_load_entity_class']) && $dceFieldConfiguration['dce_load_entity_class']) {
            $className = $dceFieldConfiguration['dce_load_entity_class'];
        } else {
            while (false !== strpos($className, '_')) {
                $position = strpos($className, '_') + 1;
                $className = substr($className, 0, $position - 1) . '-' . strtoupper(substr($className, $position, 1)) .
                    substr($className, $position + 1);
            }

            $className = ucfirst(str_replace('-', '_', $className));
        }
        if (isset($dceFieldConfiguration['dce_get_fal_objects']) && $dceFieldConfiguration['dce_get_fal_objects'] && 'sys_file' === strtolower($className)) {
            $className = File::class;
        }

        if (isset($dceFieldConfiguration['dce_get_fal_objects']) && $dceFieldConfiguration['dce_get_fal_objects'] && 'sys_file_reference' === strtolower($className)) {
            $contentObjectUid = (int)($contentObject['_LOCALIZED_UID'] ?? $contentObject['uid']);
            $fileReferences = [];
            if (Compatibility::isFrontendMode()) {
                $fileRepository = GeneralUtility::makeInstance(
                    FileRepository::class
                );
                $fileReferences = $fileRepository->findByRelation(
                    'tt_content',
                    $dceFieldConfiguration['foreign_match_fields']['fieldname'],
                    $contentObjectUid
                );
            } else {
                /** @var RelationHandler $relationHandler */
                $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
                $relationHandler->start(
                    '',
                    'sys_file_reference',
                    '',
                    $contentObjectUid,
                    'tt_content',
                    $dceFieldConfiguration
                );
                if (isset($relationHandler->tableArray['sys_file_reference']) && !empty($relationHandler->tableArray['sys_file_reference'])) {
                    $referenceUids = $relationHandler->tableArray['sys_file_reference'];
                }
                if (isset($referenceUids) && !empty($referenceUids)) {
                    /** @var ResourceFactory $fileFactory */
                    $fileFactory = GeneralUtility::makeInstance(ResourceFactory::class);
                    foreach ($referenceUids as $referenceUid) {
                        $fileReferenceData = BackendUtility::getRecordWSOL(
                            'sys_file_reference',
                            $referenceUid
                        );
                        if ($fileReferenceData) {
                            if (
                                (!isset($fileReferenceData['t3ver_label']) || 'DELETED!' !== $fileReferenceData['t3ver_label']) &&
                                '1' !== $fileReferenceData['hidden'] &&
                                '1' !== $fileReferenceData['deleted']
                            ) {
                                $fileReferences[] = $fileFactory->createFileReferenceObject($fileReferenceData);
                            }
                        }
                    }
                }
            }

            return $fileReferences;
        }

        if (false === strpos($className, '\\')) {
            $repositoryName = str_replace('_Model_', '_Repository_', $className) . 'Repository';
        } else {
            $repositoryName = str_replace('\\Model\\', '\\Repository\\', $className) . 'Repository';
        }
        if ('sys_file_collection' === strtolower($className)) {
            $specialClass = 'FileCollection';
            $className = AbstractFileCollection::class;
            $repositoryName = FileCollectionRepository::class;
        }

        if (class_exists($className) && class_exists($repositoryName)) {
            // Extbase object found
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            /** @var Repository $repository */
            $repository = $objectManager->get($repositoryName);

            foreach (GeneralUtility::trimExplode(',', $fieldValue, true) as $uid) {
                $uid = (int)$uid;
                /** @var \TYPO3\CMS\Core\Resource\Collection\StaticFileCollection|object $object */
                $object = $repository->findByUid($uid);
                if ('FileCollection' === $specialClass) {
                    $object->loadContents();
                }
                $objects[] = $object;
            }

            return $objects;
        }
        // No class found... load DB record and return assoc
        foreach (GeneralUtility::trimExplode(',', $fieldValue, true) as $uid) {
            $enableFields = '';

            if (1 === \count($tableNames)) {
                $uid = (int)$uid;
                $tableName = $tableNames[0];
            } else {
                $position = strrpos($uid, '_');
                $tableName = substr($uid, 0, $position);
                $uid = (int)substr($uid, $position + 1);
            }

            if (empty($tableName)) {
                continue;
            }

            $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable($tableName);
            if ($dceFieldConfiguration['dce_ignore_enablefields']) {
                $queryBuilder->getRestrictions()->removeAll();
            }

            $recordRows = $queryBuilder
                ->select('*')
                ->from($tableName)
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                    )
                )
                ->execute()
                ->fetchAll();

            $pageRepository = $GLOBALS['TSFE']->sys_page;
            if ($dceFieldConfiguration['dce_enable_autotranslation']) {
                /** @var class-string $pageRepoClassName */
                $pageRepoClassName = Compatibility::getPageRepositoryClassName();
                if (!$pageRepository instanceof $pageRepoClassName) {
                    /** @var PageRepository $pageRepository */
                    $pageRepository = GeneralUtility::makeInstance($pageRepoClassName);
                }
            }
            foreach ($recordRows as $row) {
                if ($dceFieldConfiguration['dce_enable_autotranslation']) {
                    if ('pages' === $tableName) {
                        $row = $pageRepository->getPageOverlay($row);
                    } else {
                        $row = $pageRepository->getRecordOverlay(
                            $tableName,
                            $row,
                            $this->getSysLanguageUid(),
                            $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_overlay']
                        );
                    }
                }

                // Add field with tableName
                $row['_table'] = $tableName;

                if ('tt_content' === $tableName) {
                    $dceUid = static::extractUidFromCTypeOrIdentifier($row['CType']);
                    if ($dceUid) {
                        $objects[] = $this->findAndBuildOneByUid(
                            $dceUid,
                            $this->getDceFieldsByRecord($row),
                            $row
                        );
                        continue;
                    }
                }
                $objects[] = $row;
            }
        }

        return $objects;
    }

    private function getSysLanguageUid(): ?int
    {
        $context = GeneralUtility::makeInstance(Context::class);
        try {
            return (int)$context->getPropertyFromAspect('language', 'id', 0);
        } catch (AspectNotFoundException $e) {
        }

        return null;
    }

    /**
     * Resolves relations of tt_content row record stored in {contentObject}
     * It resolves: media, assets and categories.
     *
     * @param array $contentObjectArray which contains
     *
     * @return array Processed content object array
     */
    protected function resolveContentObjectRelations(array $contentObjectArray): array
    {
        if (empty($contentObjectArray)) {
            return $contentObjectArray;
        }

        /** @var FileRepository $fileRepository */
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $processedContentObject = $contentObjectArray;

        // Resolve media field
        $processedContentObject['media'] = $fileRepository->findByRelation(
            'tt_content',
            'media',
            $contentObjectArray['uid']
        );

        // Resolve assets field (if fluid_styled_content is installed)
        if (ExtensionManagementUtility::isLoaded('fluid_styled_content')) {
            $processedContentObject['assets'] = $fileRepository->findByRelation(
                'tt_content',
                'assets',
                $contentObjectArray['uid']
            );
        }

        // Resolve categories
        if (array_key_exists('categories', $processedContentObject)) {
            /** @var ObjectManager $objectManager */
            $objectManager = $this->objectManager
                ?? GeneralUtility::makeInstance(ObjectManager::class);
            /** @var CategoryRepository $categoryRepository */
            $categoryRepository = $objectManager->get(CategoryRepository::class);

            $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('sys_category');
            $statement = $queryBuilder
                ->select('sc.uid')
                ->from('sys_category', 'sc')
                ->join(
                    'sc',
                    'sys_category_record_mm',
                    'sc_mm',
                    $queryBuilder->expr()->eq(
                        'sc_mm.uid_local',
                        $queryBuilder->quoteIdentifier('sc.uid')
                    )
                )
                ->join(
                    'sc_mm',
                    'tt_content',
                    'tc',
                    $queryBuilder->expr()->eq(
                        'sc_mm.uid_foreign',
                        $queryBuilder->quoteIdentifier('tc.uid')
                    )
                )
                ->where(
                    $queryBuilder->expr()->eq(
                        'tc.uid',
                        $queryBuilder->createNamedParameter($contentObjectArray['uid'], \PDO::PARAM_INT)
                    )
                )
                ->andWhere(
                    $queryBuilder->expr()->eq(
                        'sc_mm.tablenames',
                        $queryBuilder->createNamedParameter('tt_content', \PDO::PARAM_STR)
                    )
                )
                ->andWhere(
                    $queryBuilder->expr()->eq(
                        'sc_mm.fieldname',
                        $queryBuilder->createNamedParameter('categories', \PDO::PARAM_STR)
                    )
                )
                ->execute();
            $processedContentObject['categories'] = [];
            while ($categoryRow = $statement->fetch()) {
                $category = $categoryRepository->findByUid($categoryRow['uid']);
                if ($category instanceof Category) {
                    $processedContentObject['categories'][] = $categoryRepository->findByUid($categoryRow['uid']);
                }
            }
        }

        return $processedContentObject;
    }

    /**
     * Get DCE Instance.
     *
     * @param int $contentElementUid Uid of content element (tt_content)
     * @param array|null $contentObject
     * @return Dce
     */
    public function getDceInstance(int $contentElementUid, ?array $contentObject = null): Dce
    {
        $contentObject = $contentObject ?? $this->getContentObject($contentElementUid);
        $uid = $this->extractUidFromCTypeOrIdentifier($contentObject['CType']);
        $settings = $this->simulateContentElementSettings($contentObject['_LOCALIZED_UID'] ?? $contentObject['uid']);

        return $this->findAndBuildOneByUid(
            $uid,
            $settings,
            $contentObject
        );
    }

    /**
     * Simulates content element settings, which is necessary in backend context.
     */
    public function simulateContentElementSettings(int $contentElementUid): array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $queryBuilder->getRestrictions()->removeByType(StartTimeRestriction::class);
        $queryBuilder->getRestrictions()->removeByType(EndTimeRestriction::class);
        $row = $queryBuilder
            ->select('pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($contentElementUid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        $flexData = FlexformService::get()->convertFlexFormContentToArray($row['pi_flexform'], 'lDEF', 'vDEF');

        return $flexData['settings'] ?? [];
    }

    /**
     * Returns an array with properties of content element with given uid.
     *
     * @param int $uid of content element to get
     *
     * @return array|null with all properties of given content element uid
     */
    public function getContentObject(int $uid): ?array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $queryBuilder->getRestrictions()->removeByType(StartTimeRestriction::class);
        $queryBuilder->getRestrictions()->removeByType(EndTimeRestriction::class);

        return $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch() ?: null;
    }
}
