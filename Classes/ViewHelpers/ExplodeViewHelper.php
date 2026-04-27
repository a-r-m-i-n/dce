<?php

namespace T3\Dce\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Explode ViewHelper which uses the trimExplode method of \TYPO3\CMS\Core\Utility\GeneralUtility.
 */
class ExplodeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('subject', 'string', 'The subject');
        $this->registerArgument('delimiter', 'string', '', false, ',');
        $this->registerArgument('removeEmpty', 'boolean', '', false, true);
    }

    public function render(): array
    {
        $subject = $this->arguments['subject'];
        if (null === $subject) {
            $subject = $this->renderChildren();
        }

        $delimiter = $this->arguments['delimiter'];
        switch ($delimiter) {
            case '\n':
                $delimiter = "\n";
                break;
            case '\r':
                $delimiter = "\r";
                break;
            case '\r\n':
                $delimiter = "\r\n";
                break;
            case '\t':
                $delimiter = "\t";
                break;
            default:
        }

        return GeneralUtility::trimExplode(
            $delimiter,
            $subject,
            $this->arguments['removeEmpty']
        );
    }
}
