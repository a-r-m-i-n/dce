<?php

namespace T3\Dce\ViewHelpers\Format;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Escapes char list in given subject.
 */
class AddcslashesViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('subject', 'string', 'The subject');
        $this->registerArgument('charlist', 'string', 'A list of characters to be escaped', false, "'");
    }

    public function render(): string
    {
        $subject = $this->arguments['subject'];
        if (null === $subject) {
            $subject = (string)$this->renderChildren();
        }

        return addcslashes($subject, $this->arguments['charlist']);
    }
}
