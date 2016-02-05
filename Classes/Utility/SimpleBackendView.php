<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
 */
use ArminVieweg\Dce\Domain\Model\Dce;
use ArminVieweg\Dce\Domain\Model\DceField;
use TYPO3\CMS\Backend\View\PageLayoutView;
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
     * Returns configured rendered field value
     *
     * @param Dce $dce
     * @param bool $textOnly When true the return value is not wrapped by <strong>-tags
     * @return string
     */
    public function getSimpleBackendViewHeaderContent(Dce $dce, $textOnly = false)
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
    public function getSimpleBackendViewBodytextContent(Dce $dce, array $row)
    {
        $fields = array();
        foreach ($dce->getBackendViewBodytextArray() as $fieldIdentifier) {
            if (strpos($fieldIdentifier, '*') === 0) {
                $fields[] = $fieldIdentifier;
            } else {
                $fields[] = $dce->getFieldByVariable($fieldIdentifier);
            }
        }

        $content = '<table class="dceSimpleBackendView"><tbody>';
        /** @var DceField|string $field */
        foreach ($fields as $field) {
            if ($field === '*empty') {
                $content .= '<tr><td class="dceFull" colspan="2"></td></tr>';
            } elseif ($field === '*dcetitle') {
                $content .= '<tr><td class="dceFull" colspan="2">' . $GLOBALS['LANG']->sL($dce->getTitle()) .
                            '</td></tr>';
            } else {
                $content .= '<tr><td class="dceFieldTitle">' . $this->getFieldLabel($field) . '</td>' .
                    '<td class="dceFieldValue">' . $this->renderDceFieldValue($field, $row) . '</td></tr>';
            }
        }
        $content .= '</tbody></table>';
        return $content;
    }

    /**
     * Returns label of given field and crops it
     *
     * @param DceField $field
     * @return string Cropped field label
     */
    protected function getFieldLabel(DceField $field)
    {
        $fieldTitle = $GLOBALS['LANG']->sL($field->getTitle());

        // TODO: Write class for getting settings from pageTS
        $cropLength = 10;
        $pageTs = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig(GeneralUtility::_GP('id'));
        if (isset($pageTs['tx_dce.']['defaults.']['simpleBackendView.']['titleCropLength'])) {
            $cropLength = $pageTs['tx_dce.']['defaults.']['simpleBackendView.']['titleCropLength'];
        }

        // TODO: Write class for getting settings from pageTS
        $cropString = '';
        if (isset($pageTs['tx_dce.']['defaults.']['simpleBackendView.']['titleCropAppendix'])) {
            $cropString = $pageTs['tx_dce.']['defaults.']['simpleBackendView.']['titleCropAppendix'];
        }

        /** @var \TYPO3\CMS\Core\Charset\CharsetConverter $charsetConverter */
        $charsetConverter = GeneralUtility::makeInstance('TYPO3\CMS\Core\Charset\CharsetConverter');
        return $charsetConverter->crop('utf-8', $fieldTitle, $cropLength, $cropString);
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
        $database = DatabaseUtility::getDatabaseConnection();
        $fieldConfiuration = $field->getConfigurationAsArray();

        /** @var \TYPO3\CMS\Frontend\Page\PageRepository $pageRepository */
        $pageRepository = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Page\PageRepository');
        $rows = $database->exec_SELECTgetRows(
            'uid',
            'sys_file_reference',
            'tablenames=' . $database->fullQuoteStr('tt_content', 'sys_file_reference') .
            ' AND uid_foreign=' . $row['uid'] . ' AND fieldname=' . $database->fullQuoteStr(
                $fieldConfiuration['foreign_match_fields']['fieldname'],
                'sys_file_reference'
            ) . $pageRepository->enableFields('sys_file_reference', $pageRepository->showHiddenRecords),
            '',
            'sorting_foreign',
            '',
            'uid'
        );

        // TODO: Write class for getting settings from pageTS
        $imageWidth = '50c';
        $pageTs = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig(GeneralUtility::_GP('id'));
        if (isset($pageTs['tx_dce.']['defaults.']['simpleBackendView.']['imageWidth'])) {
            $imageWidth = $pageTs['tx_dce.']['defaults.']['simpleBackendView.']['imageWidth'];
        }
        // TODO: Write class for getting settings from pageTS
        $imageHeight = '50c';
        $pageTs = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig(GeneralUtility::_GP('id'));
        if (isset($pageTs['tx_dce.']['defaults.']['simpleBackendView.']['imageHeight'])) {
            $imageHeight = $pageTs['tx_dce.']['defaults.']['simpleBackendView.']['imageHeight'];
        }

        $imageTags = array();
        foreach (array_keys($rows) as $fileReferenceUid) {
            $fileReference = ResourceFactory::getInstance()->getFileReferenceObject($fileReferenceUid, array());
            $fileObject = $fileReference->getOriginalFile();
            if ($fileObject->isMissing()) {
                continue;
            }
            $image = $fileObject->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, array(
                'width' => $imageWidth,
                'height' => $imageHeight
            ));
            $imageTags[] = '<img src="' . $image->getPublicUrl(true) . '" class="dceFieldImage">';
        }
        return implode('', $imageTags);
    }
}
