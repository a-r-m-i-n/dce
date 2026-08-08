<?php

namespace T3\Dce\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Checks if the given subject is an array.
 */
class IsArrayViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('subject', 'mixed', 'The subject');
    }

    public function render(): bool
    {
        $subject = $this->arguments['subject'];
        if (null === $subject) {
            $subject = $this->renderChildren();
        }

        return is_array($subject);
    }
}
