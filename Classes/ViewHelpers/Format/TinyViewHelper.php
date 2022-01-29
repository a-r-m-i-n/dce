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
 * Removes new lines and tabs from given subject.
 */
class TinyViewHelper extends AbstractViewHelper
{
    /**
     * @var bool we accept value and children interchangeably, thus we must disable children escaping
     */
    protected $escapeChildren = false;

    /**
     * @var bool if we decode, we must not encode again after that
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('subject', 'string', 'The subject');
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
            $subject = (string)$renderChildrenClosure();
        }

        return str_replace(["\r", "\n", "\t"], '', $subject);
    }
}
