<?php

declare(strict_types = 1);

namespace T3\Dce\UpdateWizards;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2023-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\FlexformService;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('dceInlineFalToFileUpdateWizard')]
class InlineFalToFileUpdateWizard implements UpgradeWizardInterface
{
    /** @var string */
    protected $description = '';

    public function getIdentifier(): string
    {
        return 'dceInlineFalToFileUpdateWizard';
    }

    public function getTitle(): string
    {
        return 'EXT:dce Migrate FAL-TCA in DCE field configurations from "inline" to "file"';
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function executeUpdate(): bool
    {
        return (bool)$this->update();
    }

    public function updateNecessary(): bool
    {
        $affectedFieldRows = $this->getAffectedDceFieldRows();
        $this->description = 'Found ' . \count($affectedFieldRows) . ' DCE field configurations with old "inline" config!';

        return \count($affectedFieldRows) > 0;
    }

    public function update(): ?bool
    {
        $affectedFieldRows = $this->getAffectedDceFieldRows();

        foreach ($affectedFieldRows as $affectedFieldRow) {
            $conf = new \DOMDocument();
            $conf->loadXML('<root>' . $affectedFieldRow['configuration'] . '</root>');
            $flexformConfig = FlexformService::xmlToArray($conf);

            $newConfig = PHP_EOL . '<config>' . PHP_EOL . '    <type>file</type>' . PHP_EOL;

            if (isset($flexformConfig['root']['config']['minitems'])) {
                $newConfig .= '    <minitems>' . $flexformConfig['root']['config']['minitems'] . '</minitems>' . PHP_EOL;
            }
            if (isset($flexformConfig['root']['config']['maxitems'])) {
                $newConfig .= '    <maxitems>' . $flexformConfig['root']['config']['maxitems'] . '</maxitems>' . PHP_EOL;
            }
            if (isset($flexformConfig['root']['config']['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed'])) {
                $newConfig .= '    <allowed>' . $flexformConfig['root']['config']['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed'] . '</allowed>' . PHP_EOL;
            }
            $newConfig .= PHP_EOL;
            if (isset($flexformConfig['root']['config']['dce_load_schema'])) {
                $newConfig .= '    <dce_load_schema>' . $flexformConfig['root']['config']['dce_load_schema'] . '</dce_load_schema>' . PHP_EOL;
            }
            if (isset($flexformConfig['root']['config']['dce_get_fal_objects'])) {
                $newConfig .= '    <dce_get_fal_objects>' . $flexformConfig['root']['config']['dce_get_fal_objects'] . '</dce_get_fal_objects>' . PHP_EOL;
            }
            $newConfig .= '</config>' . PHP_EOL;

            $newConfig = trim($newConfig);
            $newConfig = preg_replace('/(.*?)(<config>.*<\/config>)(.*?)/is', '$1' . $newConfig . '$3', $affectedFieldRow['configuration']);

            $connection = DatabaseUtility::getConnectionPool()->getConnectionForTable('tx_dce_domain_model_dcefield');
            $connection->update(
                'tx_dce_domain_model_dcefield',
                ['configuration' => $newConfig],
                ['uid' => $affectedFieldRow['uid']]
            );
        }

        return true;
    }

    private function getAffectedDceFieldRows(): array
    {
        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('tx_dce_domain_model_dcefield');

        return $queryBuilder
            ->select('*')
            ->from('tx_dce_domain_model_dcefield')
            ->where(
                $queryBuilder->expr()->like(
                    'configuration',
                    $queryBuilder->createNamedParameter(
                        '%' . $queryBuilder->escapeLikeWildcards('<type>inline</type>') . '%'
                    )
                )
            )
            ->andWhere(
                $queryBuilder->expr()->like(
                    'configuration',
                    $queryBuilder->createNamedParameter(
                        '%' . $queryBuilder->escapeLikeWildcards('<foreign_table>sys_file_reference</foreign_table>') . '%'
                    )
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }
}
