<?php

namespace T3\Dce\ViewHelpers\Format;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Strips slashes from given subject.
 */
class StripslashesViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('subject', 'string', 'The subject');
        $this->registerArgument(
            'performTrim',
            'boolean',
            'If TRUE a trim will be made on subject before stripping slashes'
        );
    }

    public function render(): string
    {
        $subject = $this->arguments['subject'];
        if (null === $subject) {
            $subject = (string)$this->renderChildren();
        }
        if (true === $this->arguments['performTrim']) {
            $subject = trim($subject);
        }

        return stripslashes($subject);
    }
}
