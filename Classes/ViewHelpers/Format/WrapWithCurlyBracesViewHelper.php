<?php

namespace T3\Dce\ViewHelpers\Format;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the given subject with encircling curly braces.
 */
class WrapWithCurlyBracesViewHelper extends AbstractViewHelper
{
    protected $escapeChildren = false;
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('subject', 'string', 'The subject');
        $this->registerArgument('prepend', 'string', 'Prepend this after open curly brace', false, '');
        $this->registerArgument('append', 'string', 'Append this before closing curly brace', false, '');
    }

    /**
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $subject = $arguments['subject'];
        if (null === $subject) {
            $subject = $renderChildrenClosure();
        }

        return '{' . $arguments['prepend'] . $subject . $arguments['append'] . '}';
    }
}
