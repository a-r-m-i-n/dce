<?php

declare(strict_types = 1);

namespace T3\Dce\UpdateWizards;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2022 Armin Vieweg <armin@v.ieweg.de>
 */
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Domain\Repository\DceRepository;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\FlexformService;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Resource\Index\Indexer;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migrates old "type: group, internal_type: file" DCEs and content elements based on it.
 * Moving files to fileadmin/ and index by FAL
 */
class FileToFalUpdateWizard implements UpgradeWizardInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private CONST FILEADMIN_STORAGE_UID = 1; // TODO: Hardcoded uid

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var DceRepository
     */
    private $dceRepository;

    /**
     * @var StorageRepository
     */
    private $fileStorageRepository;

    public function __construct()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->dceRepository = $objectManager->get(DceRepository::class);
        $this->fileStorageRepository = $objectManager->get(StorageRepository::class);
    }

    public function getIdentifier(): string
    {
        return 'dceFileToFalUpdate';
    }

    public function getTitle(): string
    {
        return 'EXT:dce Migrate old files (type: group) to FAL (type: inline)';
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function executeUpdate(): bool
    {
        return (bool)$this->update();
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    public function updateNecessary(): bool
    {
        // Get DCEs (and fields) with old type: group, internal_type: file configuration
        $affectedFieldRows = $this->getAffectedDceFieldRows();
        $affectedDceRows = $this->getAffectedDceRows($affectedFieldRows);

        // Get content elements based on those DCEs and check for existing/missing images
        $imagesFound = [];
        $imagesMissing = [];

        $allElementRows = [];
        $affectedDceNames = '';
        $imagesMissingText = '';
        foreach ($affectedDceRows as $affectedDceRow) {
            if (!$affectedDceRow['uid']) {
                continue;
            }
            $affectedDceNames .= '- ' . $affectedDceRow['title'] . ' (uid=' . $affectedDceRow['uid'] . ')' . PHP_EOL;

            /** @var Dce $dce */
            $dce = $this->dceRepository->findByUidIncludingHidden($affectedDceRow['uid']);
            $allElementRows = array_merge($allElementRows, $elementRows = $this->dceRepository->findContentElementsBasedOnDce($dce, false));
            foreach ($elementRows as $elementRow) {
                $flexformData = FlexformService::get()->convertFlexFormContentToArray($elementRow['pi_flexform']);
                $flexformData = $flexformData['settings'];

                foreach ($affectedDceRow['_affectedFields'] ?? [] as $affectedFieldRow) {
                    $conf = new \DOMDocument();
                    $conf->loadXML('<root>' . $affectedFieldRow['configuration'] . '</root>');
                    $flexformConfig = FlexformService::xmlToArray($conf);
                    $flexformConfig = $flexformConfig['root']['config'] ?? [];

                    $uploadFolder = $flexformConfig['uploadfolder'] ?? null;

                    if ($affectedFieldRow['parent_dce'] === 0) {
                        // Resolve section field contents
                        $images = '';
                        foreach ($flexformData[$affectedFieldRow['_parent_section_field']['variable']] as $key => $child) {
                            $child = reset($child);
                            $images .= $child[$affectedFieldRow['variable']];
                            $images .= ',';
                        }
                    } else {
                        $images = $flexformData[$affectedFieldRow['variable']] ?? '';
                    }
                    $images = GeneralUtility::trimExplode(',', $images, true);

                    foreach ($images as $imageFileName) {
                        $path = Environment::getPublicPath() . DIRECTORY_SEPARATOR . $uploadFolder . DIRECTORY_SEPARATOR . $imageFileName;
                        if (file_exists($path)) {
                            $imagesFound[] = $path;
                        } else {
                            $imagesMissing[] = $path;
                            $imagesMissingText .= '- ' . substr($path, strlen(Environment::getPublicPath())) . PHP_EOL;
                        }
                    }
                }
            }
        }

        $this->description = sprintf(
            'Found %d fields with old file configuration, in %d different DCEs. The following DCEs are affected: %s',
            count($affectedFieldRows),
            count($affectedDceRows),
            PHP_EOL . $affectedDceNames
        );

        $this->description .= PHP_EOL . sprintf(
            '%d content elements found with %d media files (referenced %d times) to migrate.',
            count($allElementRows),
            count(array_unique($imagesFound)),
            count($imagesFound),
        );

        if (!empty($imagesMissing)) {
            $this->description .= PHP_EOL . sprintf(
                '%d files missing: %s',
                count($imagesMissing),
                PHP_EOL . $imagesMissingText
            );

        }

        return count($allElementRows) > 0;
    }

    public function update(): ?bool
    {
        $this->logger->debug('Executing update of ' . __CLASS__);

        $affectedFieldRows = $this->getAffectedDceFieldRows();
        $affectedDceRows = $this->getAffectedDceRows($affectedFieldRows);

        $this->createNewFoldersInFileadmin($affectedFieldRows);
        $this->moveAndIndexMediaFiles($affectedDceRows);

        $this->convertGroupToInlineFields($affectedFieldRows);
        $this->convertGroupSectionFields($affectedFieldRows);
        $this->createFileReferencesAndUpdateDceContentElementFlexform($affectedDceRows);
        $this->updateDceContentElementFlexformForSectionFields($affectedDceRows);

        $this->logger->info('Update of ' . __CLASS__ . ' has been successfully executed.');

        // Add notice for required template adjustments, in logs
        $dceList = '';
        foreach ($affectedDceRows as $affectedDceRow) {
            $dceList .= '- ' . $affectedDceRow['title'] . ' (uid=' . $affectedDceRow['uid'] . ')' . PHP_EOL;
        }
        $this->logger->info(
            'Now, you need to update the image handling in Fluid templates of the following DCEs:' . PHP_EOL . $dceList . PHP_EOL .
            'Read more about the migration of templates in DCE\'s documentation: ' .
            'https://docs.typo3.org/p/t3/dce/main/en-us/AdministratorManual/UpgradeWizards.html#filetofalupdate'
        );

        return true;
    }

    private function getAffectedDceFieldRows(): ?array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dcefield');
        return $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->like(
                    'configuration',
                    $queryBuilder->createNamedParameter(
                        '%' . $queryBuilder->escapeLikeWildcards('<type>group</type>') . '%'
                    )
                )
            )
            ->andWhere(
                $queryBuilder->expr()->like(
                    'configuration',
                    $queryBuilder->createNamedParameter(
                        '%' . $queryBuilder->escapeLikeWildcards('<internal_type>file</internal_type>') . '%'
                    )
                )
            )
            ->execute()
            ->fetchAll();
    }

    private function getAffectedDceRows(?array $affectedFieldRows): array
    {
        $affectedDceRows = [];
        foreach ($affectedFieldRows as $affectedFieldRow) {
            if (!array_key_exists($affectedFieldRow['parent_dce'], $affectedDceRows)) {
                $dceUid = (int)$affectedFieldRow['parent_dce'];

                // Handle section fields
                $sectionFieldRow = null;
                if ($dceUid === 0) {
                    $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
                        'tx_dce_domain_model_dcefield'
                    );
                    $sectionFieldRow = $queryBuilder
                        ->select('*')
                        ->from('tx_dce_domain_model_dcefield')
                        ->where(
                            $queryBuilder->expr()->eq(
                                'uid',
                                $queryBuilder->createNamedParameter($affectedFieldRow['parent_field'])
                            )
                        )
                        ->execute()
                        ->fetch();

                    $affectedFieldRow['_parent_section_field'] = $sectionFieldRow;
                    $dceUid = (int)$sectionFieldRow['parent_dce'];
                }


                $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable(
                    'tx_dce_domain_model_dcefield'
                );
                $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());
                $dceRow = $queryBuilder
                    ->select('*')
                    ->from('tx_dce_domain_model_dce')
                    ->where(
                        $queryBuilder->expr()->eq(
                            'uid',
                            $queryBuilder->createNamedParameter($dceUid)
                        )
                    )
                    ->execute()
                    ->fetch();

                $affectedDceRows[$dceUid] = $dceRow;
                $affectedDceRows[$dceUid]['_affectedFields'] = [$affectedFieldRow];
            } else {
                $affectedDceRows[$dceUid]['_affectedFields'][] = $affectedFieldRow;
            }
        }
        return $affectedDceRows;
    }

    private function createNewFoldersInFileadmin(?array $affectedFieldRows): void
    {
        $this->logger->debug('Checking for new folders to create in fileadmin');
        $fileadminBasePath = $this->getFileadminBasePath();

        $createdFolderPaths = [];
        $error = false;
        foreach ($affectedFieldRows as $affectedFieldRow) {
            $conf = new \DOMDocument();
            $conf->loadXML('<root>' . $affectedFieldRow['configuration'] . '</root>');
            $flexformConfig = FlexformService::xmlToArray($conf);
            $flexformConfig = $flexformConfig['root']['config'] ?? [];

            $uploadFolder = $flexformConfig['uploadfolder'] ?? null;
            $newFolderPath = $fileadminBasePath . DIRECTORY_SEPARATOR . $uploadFolder;

            if (!in_array($newFolderPath, $createdFolderPaths, true)) {
                if (!file_exists($newFolderPath)) {
                    $this->logger->info('Creating new folder "' . $newFolderPath . '"');
                    GeneralUtility::mkdir_deep($newFolderPath);
                    if (!file_exists($newFolderPath)) {
                        $error = true;
                        $this->logger->error('Unable to create new folder "' . $newFolderPath . '"!');
                    }
                }
                $createdFolderPaths[] = $newFolderPath;
            }
        }
        if ($error) {
            throw new \RuntimeException('Unable to create new folders during update. Please check log files for more details.');
        }
        $this->logger->debug(count($createdFolderPaths) . ' new folders created.');
    }

    private function moveAndIndexMediaFiles(array $affectedDceRows)
    {
        $fileadminBasePath = $this->getFileadminBasePath();
        $movedFiles = [];
        $this->logger->debug('Moving old media files');

        // Move found media files
        foreach ($affectedDceRows as $affectedDceRow) {
            $this->logger->debug('Migrating DCE ' . $affectedDceRow['title'] . ' (uid=' . $affectedDceRow['uid'] . ')');
            /** @var Dce $dce */
            $dce = $this->dceRepository->findByUidIncludingHidden($affectedDceRow['uid']);
            $elementRows = $this->dceRepository->findContentElementsBasedOnDce($dce, false);
            foreach ($elementRows as $elementRow) {
                $this->logger->debug('Processing images of tt_content element with uid ' . $elementRow['uid'] . ' (CType: ' . $elementRow['CType'] . ')');

                $flexformData = FlexformService::get()->convertFlexFormContentToArray($elementRow['pi_flexform']);
                $flexformData = $flexformData['settings'];

                foreach ($affectedDceRow['_affectedFields'] ?? [] as $affectedFieldRow) {
                    $conf = new \DOMDocument();
                    $conf->loadXML('<root>' . $affectedFieldRow['configuration'] . '</root>');
                    $flexformConfig = FlexformService::xmlToArray($conf);
                    $flexformConfig = $flexformConfig['root']['config'] ?? [];

                    $uploadFolder = $flexformConfig['uploadfolder'] ?? null;

                    if ($affectedFieldRow['parent_dce'] === 0) {
                        // Resolve section field contents
                        $media = '';
                        foreach ($flexformData[$affectedFieldRow['_parent_section_field']['variable']] as $key => $child) {
                            $child = reset($child);
                            $media .= $child[$affectedFieldRow['variable']];
                            $media .= ',';
                        }
                    } else {
                        $media = $flexformData[$affectedFieldRow['variable']] ?? '';
                    }
                    $media = GeneralUtility::trimExplode(',', $media, true);

                    foreach ($media as $mediaFileName) {
                        $oldPath = Environment::getPublicPath() . DIRECTORY_SEPARATOR . $uploadFolder . DIRECTORY_SEPARATOR . $mediaFileName;
                        if (in_array($oldPath, $movedFiles, true)) {
                            $this->logger->debug('Image "' . $oldPath . '" already moved. Skipping.');
                            continue;
                        }
                        if (file_exists($oldPath)) {
                            $newPath = $fileadminBasePath . DIRECTORY_SEPARATOR . $uploadFolder . DIRECTORY_SEPARATOR . $mediaFileName;
                            $status = rename($oldPath, $newPath);
                            if (!$status) {
                                throw new \RuntimeException(sprintf('Unable to move media file from "%s" to "%s".', $oldPath, $newPath));
                            }
                            $this->logger->info('Moved media file successfully', ['from' => $oldPath, 'to' => $newPath]);
                            $movedFiles[$newPath] = $oldPath;
                        } else {
                            $this->logger->error('Old image path "' . $oldPath . '" not found!', ['tt_content_uid' => $elementRow['uid'], 'dce' => $dce->getTitle(), 'field' => $affectedFieldRow['variable']]);
                        }
                    }
                }
            }
        }

        // Index all new media files in FAL
        $this->logger->debug('All found media files moved. Start indexing new files in FAL');
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $storage = $resourceFactory->getStorageObject(self::FILEADMIN_STORAGE_UID);
        $currentEvaluatePermissionsValue = $storage->getEvaluatePermissions();
        $storage->setEvaluatePermissions(false);
        $indexer = GeneralUtility::makeInstance(Indexer::class, $storage);
        foreach (array_keys($movedFiles) as $newPath) {
            $movedFileIdentifier = substr($newPath, strlen($fileadminBasePath) + 1);
            $indexer->createIndexEntry($movedFileIdentifier);
        }
        $storage->setEvaluatePermissions($currentEvaluatePermissionsValue);
        $this->logger->debug('Indexing of new files in FAL successfully completed');
    }

    private function convertGroupToInlineFields(?array $affectedFieldRows)
    {
        $defaultFalFieldConfiguration = file_get_contents(GeneralUtility::getFileAbsFileName('EXT:dce/Resources/Public/CodeSnippets/ConfigurationTemplates/12 FAL/File Abstraction Layer.xml'));

        foreach ($affectedFieldRows as $affectedFieldRow) {
            if ($affectedFieldRow['parent_dce'] === 0) {
                continue; // Skipping section fields
            }

            $conf = new \DOMDocument();
            $conf->loadXML('<root>' . $affectedFieldRow['configuration'] . '</root>');
            $flexformConfig = FlexformService::xmlToArray($conf);
            $flexformConfig = $flexformConfig['root']['config'] ?? [];

            $allowed = $flexformConfig['allowed'] ?? null;
            $min = $flexformConfig['minitems'] ?? null;
            $max = $flexformConfig['maxitems'] ?? null;

            $newFieldConfig = $defaultFalFieldConfiguration;
            if ($allowed) {
                $newFieldConfig = str_replace(
                    '<elementBrowserAllowed>gif,jpg,jpeg,tif,tiff,bmp,pcx,tga,png,pdf,ai,svg</elementBrowserAllowed>',
                    '<elementBrowserAllowed>' . trim($allowed) . '</elementBrowserAllowed>',
                    $newFieldConfig
                );
            }
            if ($min) {
                $newFieldConfig = str_replace('<minitems>0</minitems>', '<minitems>' . $min . '</minitems>', $newFieldConfig);
            }
            if ($max) {
                $newFieldConfig = str_replace('<maxitems>0</maxitems>', '<maxitems>' . $max . '</maxitems>', $newFieldConfig);
            }

            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dcefield');
            $connection->update(
                'tx_dce_domain_model_dcefield',
                ['configuration' => $newFieldConfig],
                ['uid' => $affectedFieldRow['uid']]
            );

            $this->logger->info('Updated DCE field (uid=' . $affectedFieldRow['uid'] . ') configuration', [
                'old' => $affectedFieldRow['configuration'],
                'new' => $newFieldConfig,
            ]);
        }
    }

    private function convertGroupSectionFields(?array $affectedFieldRows)
    {
        $defaultSysFileFieldConfiguration = <<<XML
<config>
	<type>group</type>
	<internal_type>db</internal_type>
	<appearance>
		<elementBrowserType>file</elementBrowserType>
		<elementBrowserAllowed>gif,jpg,jpeg,tif,tiff,bmp,pcx,tga,png,pdf,ai,svg</elementBrowserAllowed>
	</appearance>
	<allowed>sys_file</allowed>
	<size>5</size>
	<minitems>0</minitems>
	<maxitems>0</maxitems>
	<show_thumbs>1</show_thumbs>

	<dce_load_schema>1</dce_load_schema>
	<dce_get_fal_objects>1</dce_get_fal_objects>
</config>
XML;
        foreach ($affectedFieldRows as $affectedFieldRow) {
            if ($affectedFieldRow['parent_dce'] > 0) {
                continue; // Skipping non-section fields
            }

            $conf = new \DOMDocument();
            $conf->loadXML('<root>' . $affectedFieldRow['configuration'] . '</root>');
            $flexformConfig = FlexformService::xmlToArray($conf);
            $flexformConfig = $flexformConfig['root']['config'] ?? [];

            $allowed = $flexformConfig['allowed'] ?? null;
            $min = $flexformConfig['minitems'] ?? null;
            $max = $flexformConfig['maxitems'] ?? null;

            $newFieldConfig = $defaultSysFileFieldConfiguration;
            if ($allowed) {
                $newFieldConfig = str_replace(
                    '<elementBrowserAllowed>gif,jpg,jpeg,tif,tiff,bmp,pcx,tga,png,pdf,ai,svg</elementBrowserAllowed>',
                    '<elementBrowserAllowed>' . trim($allowed) . '</elementBrowserAllowed>',
                    $newFieldConfig
                );
            }
            if ($min) {
                $newFieldConfig = str_replace('<minitems>0</minitems>', '<minitems>' . $min . '</minitems>', $newFieldConfig);
            }
            if ($max) {
                $newFieldConfig = str_replace('<maxitems>0</maxitems>', '<maxitems>' . $max . '</maxitems>', $newFieldConfig);
            }

            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dcefield');
            $connection->update(
                'tx_dce_domain_model_dcefield',
                ['configuration' => $newFieldConfig],
                ['uid' => $affectedFieldRow['uid']]
            );

            $this->logger->info('Updated DCE section sub-field (uid=' . $affectedFieldRow['uid'] . ') configuration', [
                'old' => $affectedFieldRow['configuration'],
                'new' => $newFieldConfig,
            ]);
        }
    }

    private function createFileReferencesAndUpdateDceContentElementFlexform(array $affectedDceRows)
    {
        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $sysFileReferenceConnection = DatabaseUtility::getConnectionPool()->getConnectionForTable('sys_file_reference');
        $ttContentConnection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tt_content');

        foreach ($affectedDceRows as $affectedDceRow) {
            /** @var Dce $dce */
            $dce = $this->dceRepository->findByUid($affectedDceRow['uid']);
            $elementRows = $this->dceRepository->findContentElementsBasedOnDce($dce, false);
            foreach ($elementRows as $elementRow) {
                $flexformData = FlexformService::get()->convertFlexFormContentToArray($elementRow['pi_flexform']);
                $flexformSettings = $flexformData['settings'];

                foreach ($affectedDceRow['_affectedFields'] ?? [] as $affectedFieldRow) {
                    if ($affectedFieldRow['parent_dce'] === 0) {
                        continue; // Skipping section fields
                    }

                    $conf = new \DOMDocument();
                    $conf->loadXML('<root>' . $affectedFieldRow['configuration'] . '</root>');
                    $flexformConfig = FlexformService::xmlToArray($conf);
                    $flexformConfig = $flexformConfig['root']['config'] ?? [];

                    $uploadFolder = $flexformConfig['uploadfolder'] ?? null;
                    $media = $flexformSettings[$affectedFieldRow['variable']] ?? '';
                    $media = GeneralUtility::trimExplode(',', $media, true);

                    // Create new sys_file_reference for every media file found
                    foreach ($media as $i => $mediaFileName) {
                        $newPath = $uploadFolder . DIRECTORY_SEPARATOR . $mediaFileName;
                        try {
                            $file = $resourceFactory->getFileObjectByStorageAndIdentifier(self::FILEADMIN_STORAGE_UID, $newPath);
                        } catch (\InvalidArgumentException $e) {
                        }
                        if (!$file) {
                            throw new \RuntimeException('Unable to get file from fileadmin storage with identifier "' . $newPath . '"');
                        }

                        $newSysFileReference = [
                            'table_local' => 'sys_file',
                            'uid_local' => $file->getUid(),
                            'tablenames' => 'tt_content',
                            'fieldname' => $affectedFieldRow['variable'],
                            'uid_foreign' => $elementRow['uid'],
                            'sys_language_uid' => $elementRow['sys_language_uid'],
                            'sorting_foreign' => $i,
                            'pid' => $elementRow['pid'],
                        ];

                        $sysFileReferenceConnection->insert('sys_file_reference', $newSysFileReference);
                        $newSysFileReferenceUid = $sysFileReferenceConnection->lastInsertId('sys_file_reference');
                        $this->logger->info('Added new sys_file_reference with uid ' . $newSysFileReferenceUid, ['values' => $newSysFileReference]);
                    }

                    // Replace image name(s) with amount of relations, in pi_flexform of DCE content element
                    $conf = new \DOMDocument();
                    $conf->loadXML($elementRow['pi_flexform']);

                    $xpath = new \DOMXPath($conf);
                    $node = $xpath->query("//field[@index='settings." . $affectedFieldRow['variable'] . "']/value");
                    $node->item(0)->nodeValue = count($media);
                    $updatedFlexform = $conf->saveXML();

                    $ttContentConnection->update('tt_content', [
                        'pi_flexform' => $updatedFlexform,
                    ], ['uid' => $elementRow['uid']]);
                }
            }
        }
    }

    private function updateDceContentElementFlexformForSectionFields(array $affectedDceRows)
    {
        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $ttContentConnection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tt_content');

        foreach ($affectedDceRows as $affectedDceRow) {
            /** @var Dce $dce */
            $dce = $this->dceRepository->findByUid($affectedDceRow['uid']);
            $elementRows = $this->dceRepository->findContentElementsBasedOnDce($dce, false);
            foreach ($elementRows as $elementRow) {
                $flexformData = FlexformService::get()->convertFlexFormContentToArray($elementRow['pi_flexform']);
                $flexformSettings = $flexformData['settings'];

                $elementFlexformContents = new \DOMDocument();
                $elementFlexformContents->loadXML($elementRow['pi_flexform']);
                $xpath = new \DOMXPath($elementFlexformContents);

                foreach ($affectedDceRow['_affectedFields'] ?? [] as $affectedFieldRow) {
                    if ($affectedFieldRow['parent_dce'] > 0) {
                        continue; // Skipping non-section fields
                    }

                    $conf = new \DOMDocument();
                    $conf->loadXML('<root>' . $affectedFieldRow['configuration'] . '</root>');
                    $flexformConfig = FlexformService::xmlToArray($conf);
                    $flexformConfig = $flexformConfig['root']['config'] ?? [];

                    $uploadFolder = $flexformConfig['uploadfolder'] ?? null;

                    // Resolve section field contents
                    foreach ($flexformSettings[$affectedFieldRow['_parent_section_field']['variable']] as $sectionIndexKey => $child) {
                        $child = reset($child);
                        $media = $child[$affectedFieldRow['variable']];
                        $media = GeneralUtility::trimExplode(',', $media, true);

                        // Get sys_file uid and replace filename with uid in section field flexform
                        $fileUids = [];
                        foreach ($media as $i => $mediaFileName) {
                            $newPath = $uploadFolder . DIRECTORY_SEPARATOR . $mediaFileName;
                            try {
                                $file = $resourceFactory->getFileObjectByStorageAndIdentifier(self::FILEADMIN_STORAGE_UID, $newPath);
                            } catch (\InvalidArgumentException $e) {
                            }
                            if (!$file) {
                                throw new \RuntimeException('Unable to get file from fileadmin storage with identifier "' . $newPath . '"');
                            }
                            $fileUids[] = $file->getUid();
                            unset($mediaFileName, $newPath);
                        }

                        $node = $xpath->query("//field[@index='settings." . $affectedFieldRow['_parent_section_field']['variable'] . "']//field[@index='" . $sectionIndexKey . "']//field[@index='" . $affectedFieldRow['variable'] . "']/value");
                        $node->item(0)->nodeValue = implode(',', $fileUids);
                    }

                    $updatedFlexform = $elementFlexformContents->saveXML();

                    $ttContentConnection->update('tt_content', [
                        'pi_flexform' => $updatedFlexform,
                    ], ['uid' => $elementRow['uid']]);

                    $this->logger->info('Updated content element (uid=' . $elementRow['uid'] . ' flexform values.');
                }
            }
        }
    }

    private function getFileadminBasePath(): string
    {
        /** @var ResourceStorage $fileStorage */
        $fileStorage = $this->fileStorageRepository->findByUid(self::FILEADMIN_STORAGE_UID);
        $fileStorageConfiguration = $fileStorage->getStorageRecord()['configuration'];
        $flexformConfig = FlexformService::get()->convertFlexFormContentToArray($fileStorageConfiguration);

        return Environment::getPublicPath() . DIRECTORY_SEPARATOR . $flexformConfig['basePath'];
    }
}
