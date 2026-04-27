<?php

namespace T3\Dce\ViewHelpers\Format;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Performs str_replace on given subject.
 */
class ReplaceViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('subject', 'string', 'The subject');
        $this->registerArgument('search', 'string', 'String to search for');
        $this->registerArgument('replace', 'string', 'String to replace with');
    }

    public function render(): string
    {
        $subject = $this->arguments['subject'];
        if (null === $subject) {
            $subject = (string)$this->renderChildren();
        }

        return str_replace($this->arguments['search'], $this->arguments['replace'], $subject);
    }
}
