<?php
namespace ArminVieweg\Dce\Components\BackendView;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2017 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Components\DceContainer\ContainerFactory;
use ArminVieweg\Dce\Domain\Model\Dce;
use ArminVieweg\Dce\Domain\Model\DceField;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Simple backend view utility
 *
 * @package ArminVieweg\Dce
 */
class SimpleBackendView
{
    /**
     * @var string
     */
    protected static $lastContainerColor;

    /**
     * Returns configured rendered field value
     *
     * @param Dce $dce
     * @param bool $textOnly When true the return value is not wrapped by <strong>-tags
     * @return string
     */
    public function getHeaderContent(Dce $dce, $textOnly = false)
    {
        if ($dce->getBackendViewHeader() === '*empty') {
            return '';
        }
        if ($dce->getBackendViewHeader() === '*dcetitle') {
            if ($textOnly) {
                return $dce->getTitle();
            }
            return '<strong class="dceHeader">' . $dce->getTitle() . '</strong>';
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
     */
    public function getBodytextContent(Dce $dce, array $row)
    {
        $fields = [];
        foreach ($dce->getBackendViewBodytextArray() as $fieldIdentifier) {
            if (strpos($fieldIdentifier, '*') === 0) {
                $fields[] = $fieldIdentifier;
            } else {
                $dceField = $dce->getFieldByVariable($fieldIdentifier);
                if (!is_null($dceField)) {
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
                            \ArminVieweg\Dce\Utility\LanguageService::sL($dce->getTitle()) . '</td></tr>';
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
    protected function getFieldLabel(DceField $field)
    {
        /** @var \TYPO3\CMS\Core\Charset\CharsetConverter $charsetConverter */
        $charsetConverter = GeneralUtility::makeInstance('TYPO3\CMS\Core\Charset\CharsetConverter');
        return $charsetConverter->crop(
            'utf-8',
            \ArminVieweg\Dce\Utility\LanguageService::sL($field->getTitle()),
            \ArminVieweg\Dce\Utility\PageTS::get('tx_dce.defaults.simpleBackendView.titleCropLength', 10),
            \ArminVieweg\Dce\Utility\PageTS::get('tx_dce.defaults.simpleBackendView.titleCropAppendix', '...')
        );
    }

    /**
     * Renders given dce field for simple backend view (bodytext)
     *
     * @param DceField $field
     * @param array $row Content element row
     * @return string Rendered DceField value for simple backend view
     */
    protected function renderDceFieldValue(DceField $field, array $row)
    {
        if ($field->isSection()) {
            $sectionRowAmount = 0;
            foreach ($field->getSectionFields() as $sectionField) {
                $sectionFieldValue = $sectionField->getValue();
                if (is_array($sectionFieldValue)) {
                    $sectionRowAmount = count($sectionFieldValue);
                }
            }
            if ($sectionRowAmount === 1) {
                $label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('entry', 'dce');
            } else {
                $label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('entries', 'dce');
            }
            return $sectionRowAmount . ' ' . $label;
        }

        if ($field->isFal()) {
            return $this->getFalMediaPreview($field, $row);
        }

        if (is_array($field->getValue()) || $field->getValue() instanceof \Countable) {
            if (count($field->getValue()) === 1) {
                $label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('entry', 'dce');
            } else {
                $label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('entries', 'dce');
            }
            return count($field->getValue()) . ' ' . $label;
        }

        return $field->getValue();
    }

    /**
     * Get FAL media preview
     *
     * @param DceField $field
     * @param array $row
     * @return string
     */
    protected function getFalMediaPreview(DceField $field, array $row)
    {
        $database = \ArminVieweg\Dce\Utility\DatabaseUtility::getDatabaseConnection();
        $fieldConfiguration = $field->getConfigurationAsArray();
        $fieldConfiguration = $fieldConfiguration['config'];

        /** @var \TYPO3\CMS\Frontend\Page\PageRepository $pageRepository */
        $pageRepository = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Page\PageRepository');
        $rows = $database->exec_SELECTgetRows(
            'uid',
            'sys_file_reference',
            'tablenames=' . $database->fullQuoteStr('tt_content', 'sys_file_reference') .
            ' AND uid_foreign=' . $row['uid'] . ' AND fieldname=' . $database->fullQuoteStr(
                $fieldConfiguration['foreign_match_fields']['fieldname'],
                'sys_file_reference'
            ) . $pageRepository->enableFields('sys_file_reference', $pageRepository->showHiddenRecords),
            '',
            'sorting_foreign',
            '',
            'uid'
        );

        $imageTags = [];
        foreach (array_keys($rows) as $fileReferenceUid) {
            $fileReference = ResourceFactory::getInstance()->getFileReferenceObject($fileReferenceUid, []);
            $fileObject = $fileReference->getOriginalFile();
            if ($fileObject->isMissing()) {
                continue;
            }
            $image = $fileObject->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, [
                'width' => \ArminVieweg\Dce\Utility\PageTS::get('tx_dce.defaults.simpleBackendView.imageWidth', '50c'),
                'height' => \ArminVieweg\Dce\Utility\PageTS::get('tx_dce.defaults.simpleBackendView.imageWidth', '50')
            ]);
            $imageTags[] = '<img src="' . $image->getPublicUrl(true) . '" class="dceFieldImage">';
        }
        return implode('', $imageTags);
    }

    /**
     * Uses the uid of the first content object to get a color code
     *
     * @param Dce $dce
     * @return int|bool color code or false if container is not enabled
     */
    protected function getContainerFlag(Dce $dce)
    {
        if (!$dce->getEnableContainer()) {
            return false;
        }
        if (ContainerFactory::checkContentElementForBeingRendered($dce->getContentObject())) {
            return static::$lastContainerColor;
        }
        $container = ContainerFactory::makeContainer($dce);
        static::$lastContainerColor = $container->getContainerColor();
        return static::$lastContainerColor;
    }
}
