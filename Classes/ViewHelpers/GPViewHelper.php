<?php

namespace T3\Dce\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2025 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * GP viewhelper which returns get or post variables using _GP method of TYPO3\CMS\Core\Utility\GeneralUtility.
 * Never use this viewhelper for direct output!! This would provoke XSS (Cross site scripting).
 *
 * @deprecated Will not work anymore in TYPO3 v13
 */
class GPViewHelper extends AbstractViewHelper
{
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
            $subject = $renderChildrenClosure();
        }

        trigger_error('Do not use GP viewhelper anymore. It will not work in TYPO3 v13 and get removed.', E_USER_DEPRECATED);

        return GeneralUtility::_GP($subject);
    }
}
