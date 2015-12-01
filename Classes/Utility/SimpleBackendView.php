<?php
namespace ArminVieweg\Dce\Utility;

/*  | This extension is part of the TYPO3 project. The TYPO3 project is
 *  | free software and is licensed under GNU General Public License.
 *  |
 *  | (c) 2012-2015 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
     * @return string
     */
    public function getSimpleBackendViewHeaderContent(Dce $dce)
    {
        if ($dce->getBackendViewHeader() === '*empty') {
            return '';
        }
        if ($dce->getBackendViewHeader() === '*dcetitle') {
            return $dce->getTitle();
        }

        $field = $dce->getFieldByVariable($dce->getBackendViewHeader());
        if (!$field) {
            return '';
        }
        return $field->getValue();
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

        $content = '<table class="table table-bordered table-responsive"><tbody>';
        /** @var DceField|string $field */
        foreach ($fields as $field) {
            if ($field === '*empty') {
                $content .= '<tr><td colspan="2"></td></tr>';
            } elseif ($field === '*dcetitle') {
                $content .= '<tr><td colspan="2">' . $GLOBALS['LANG']->sL($dce->getTitle()) . '</td></tr>';
            } else {
                $content .= '<tr><td>' . $GLOBALS['LANG']->sL($field->getTitle()) . '</td>' .
                    '<td>' . $this->renderDceFieldValue($field, $row) . '</td></tr>';
            }
        }
        $content .= '</tbody></table>';
        return $content;
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
            if (count($field->getSectionFields()) === 1) {
                $label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('entry', 'dce');
            } else {
                $label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('entries', 'dce');
            }
            return count($field->getSectionFields()) . $label;
        }

        if ($field->isFal()) {
            return $this->getFalMediaPreview($field, $row);
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

        $imageTags = array();
        foreach (array_keys($rows) as $fileReferenceUid) {
            $fileReference = ResourceFactory::getInstance()->getFileReferenceObject($fileReferenceUid, array());
            $fileObject = $fileReference->getOriginalFile();
            if ($fileObject->isMissing()) {
                continue;
            }
            $image = $fileObject->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, array(
                'width' => '50c',
                'height' => '50c'
            ));
            $imageTags[] = '<img src="' . $image->getPublicUrl(true) . '" style="margin: 0 3px 3px 0;">';
        }
        return implode('', $imageTags);
    }
}
