<?php

namespace T3\Dce\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Checks if the given subject is an array.
 */
class IsArrayViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('subject', 'string', 'The subject');
    }

    /**
     * @return bool
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

        return \is_array($subject);
    }
}
