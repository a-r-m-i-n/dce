<?php
namespace T3\Dce\Components\BackendView;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 *  |     2019 Stefan Froemken <froemken@gmail.com>
 */
use T3\Dce\Components\DceContainer\ContainerFactory;
use T3\Dce\Domain\Model\Dce;
use T3\Dce\Domain\Model\DceField;
use T3\Dce\Utility\DatabaseUtility;
use T3\Dce\Utility\LanguageService;
use T3\Dce\Utility\PageTS as PageTsUtility;
use T3\Dce\Utility\Strings as StringUtility;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Simple Backend View
 */
class SimpleBackendView
{
    /**
     * @var string
     */
    protected static $lastContainerColor = '';

    /**
     * Returns configured rendered field value
     *
     * @param Dce $dce
     * @param bool $textOnly When true the return value is not wrapped by <strong>-tags
     * @return string
     */
    public function getHeaderContent(Dce $dce, bool $textOnly = false) : string
    {
        if ($dce->getBackendViewHeader() === '*empty') {
            return '';
        }
        if ($dce->getBackendViewHeader() === '*dcetitle') {
            if ($textOnly) {
                return LanguageService::sL($dce->getTitle());
            }
            return '<strong class="dceHeader">' . LanguageService::sL($dce->getTitle()) . '</strong>';
        }

        $field = $dce->getFieldByVariable($dce->getBackendViewHeader());
        if (!$field) {
            return '';
        }
        if ($textOnly) {
            return $field->getValue();
        }
        return '<strong class="dceHeader">' . $field->getValue() . '</strong>';
    }

    /**
     * Returns table of configured rendered field values
     *
     * @param Dce $dce
     * @param array $row Content element row
     * @return string
     * @throws ResourceDoesNotExistException
     */
    public function getBodytextContent(Dce $dce, array $row) : string
    {
        $fields = [];
        foreach ($dce->getBackendViewBodytextArray() as $fieldIdentifier) {
            if (strpos($fieldIdentifier, '*') === 0) {
                $fields[] = $fieldIdentifier;
            } else {
                $dceField = $dce->getFieldByVariable($fieldIdentifier);
                if ($dceField !== null) {
                    $fields[] = $dceField;
                }
            }
        }

        $content = '';
        /** @var DceField|string $field */
        foreach ($fields as $field) {
            if ($field === '*empty') {
                $content .= '<tr class="dceRow"><td class="dceFull" colspan="2"></td></tr>';
            } elseif ($field === '*dcetitle') {
                $content .= '<tr class="dceRow"><td class="dceFull" colspan="2">' .
                            LanguageService::sL($dce->getTitle()) . '</td></tr>';
            } elseif ($field === '*containerflag') {
                $containerFlag = $this->getContainerFlag($dce);
                if ($containerFlag) {
                    $content = '<tr><td class="dce-container-flag" colspan="2" style="background-color: ' .
                                $containerFlag . '"></td></tr>' . $content;
                }
            } else {
                $content .= '<tr class="dceRow"><td class="dceFieldTitle">' . $this->getFieldLabel($field) . '</td>' .
                    '<td class="dceFieldValue">' . $this->renderDceFieldValue($field, $row) . '</td></tr>';
            }
        }
        return '<table class="dceSimpleBackendView"><tbody>' . $content . '</tbody></table>';
    }

    /**
     * Returns label of given field and crops it
     *
     * @param DceField $field
     * @return string Cropped field label
     */
    protected function getFieldLabel(DceField $field) : string
    {
        return StringUtility::crop(
            'utf-8',
            LanguageService::sL($field->getTitle()),
            PageTsUtility::get('tx_dce.defaults.simpleBackendView.titleCropLength', 10),
            PageTsUtility::get('tx_dce.defaults.simpleBackendView.titleCropAppendix', '...')
        );
    }

    /**
     * Renders given dce field for simple backend view (bodytext)
     *
     * @param DceField $field
     * @param array $row Content element row
     * @return string Rendered DceField value for simple backend view
     * @throws ResourceDoesNotExistException
     */
    protected function renderDceFieldValue(DceField $field, array $row) : string
    {
        if ($field->isSection()) {
            $sectionRowAmount = 0;
            foreach ($field->getSectionFields() as $sectionField) {
                $sectionFieldValue = $sectionField->getValue();
                if (\is_array($sectionFieldValue)) {
                    $sectionRowAmount = \count($sectionFieldValue);
                }
            }
            $label = $sectionRowAmount === 1
                ? LocalizationUtility::translate('entry', 'dce')
                : LocalizationUtility::translate('entries', 'dce');
            return $sectionRowAmount . ' ' . $label;
        }

        if ($field->isFal()) {
            return $this->getFalMediaPreview($field, $row);
        }

        if (\is_array($field->getValue()) || $field->getValue() instanceof \Countable) {
            if (\count($field->getValue()) === 1) {
                $label = LocalizationUtility::translate('entry', 'dce');
            } else {
                $label = LocalizationUtility::translate('entries', 'dce');
            }
            return \count($field->getValue()) . ' ' . $label;
        }

        if ($field->getConfigurationAsArray()['type'] === 'check') {
            return $field->getValue() ? '<i class="fa fa-check"></i>' : '<i class="fa fa-not"></i>';
        }

        return $field->getValue();
    }

    /**
     * Get FAL media preview
     *
     * @param DceField $field
     * @param array $row
     * @return string
     * @throws ResourceDoesNotExistException
     */
    protected function getFalMediaPreview(DceField $field, array $row) : string
    {
        $fieldConfiguration = $field->getConfigurationAsArray();
        $fieldConfiguration = $fieldConfiguration['config'];

        $queryBuilder = DatabaseUtility::getConnectionPool()->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder
            ->select('*')
            ->from('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq(
                    'tablenames',
                    $queryBuilder->createNamedParameter('tt_content', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'fieldname',
                    $queryBuilder->createNamedParameter(
                        stripslashes($fieldConfiguration['foreign_match_fields']['fieldname']),
                        \PDO::PARAM_STR
                    )
                ),
                $queryBuilder->expr()->eq(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($row['uid'], \PDO::PARAM_INT)
                )
            )
            ->orderBy('sorting_foreign', 'ASC');

        $rows = DatabaseUtility::getRowsFromQueryBuilder($queryBuilder, 'uid');

        $imageTags = [];
        foreach (array_keys($rows) as $fileReferenceUid) {
            $fileReference = ResourceFactory::getInstance()->getFileReferenceObject($fileReferenceUid, []);
            $fileObject = $fileReference->getOriginalFile();
            if ($fileObject->isMissing()) {
                continue;
            }
            $image = $fileObject->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, [
                'width' => PageTsUtility::get('tx_dce.defaults.simpleBackendView.imageWidth', '50c'),
                'height' => PageTsUtility::get('tx_dce.defaults.simpleBackendView.imageWidth', '50')
            ]);
            $imageTags[] = '<img src="' . $image->getPublicUrl(true) . '" class="dceFieldImage">';
        }
        return implode('', $imageTags);
    }

    /**
     * Uses the uid of the first content object to get a color code
     *
     * @param Dce $dce
     * @return string color code. empty string, if container is not enabled.
     */
    protected function getContainerFlag(Dce $dce) : string
    {
        if (!$dce->getEnableContainer()) {
            return false;
        }
        if (ContainerFactory::checkContentElementForBeingRendered($dce->getContentObject())) {
            return static::$lastContainerColor;
        }
        $container = ContainerFactory::makeContainer($dce, true);
        static::$lastContainerColor = $container->getContainerColor();
        return static::$lastContainerColor;
    }
}
