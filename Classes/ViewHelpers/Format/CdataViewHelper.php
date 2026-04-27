<?php

namespace T3\Dce\ViewHelpers\Format;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Wraps given subject with CDATA. Good for fluid templates which render XML.
 *
 * @deprecated since TYPO3 v14/Fluid 5 using CDATA is nativley supported
 */
class CdataViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('subject', 'string', 'The subject');
    }

    public function render(): string
    {
        $subject = $this->arguments['subject'];
        if (null === $subject) {
            $subject = (string)$this->renderChildren();
        }

        trigger_error('Do not use dce:format.cdata view helper anymore. Since TYPO3 v14 Fluid does support usage of CDATA natively', E_USER_DEPRECATED);

        return '<![CDATA[' . $subject . ']]>';
    }
}
