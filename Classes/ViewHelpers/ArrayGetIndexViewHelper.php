<?php

namespace T3\Dce\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the given index of an array (subject).
 */
class ArrayGetIndexViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('subject', 'array', 'The subject');
        $this->registerArgument('index', 'integer', 'The numeric index of item you want to return', false, 0);
    }

    public function render(): mixed
    {
        $subject = $this->arguments['subject'];
        if (null === $subject) {
            $subject = $this->renderChildren();
        }

        return array_values($subject)[$this->arguments['index']];
    }
}
