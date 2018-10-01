<?php
namespace ArminVieweg\Dce\ViewHelpers;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2018 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Explode viewhelper which uses the trimExplode method of \TYPO3\CMS\Core\Utility\GeneralUtility
 */
class ExplodeViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('subject', 'string', 'The subject');
        $this->registerArgument('delimiter', 'string', '', false, ',');
        $this->registerArgument('removeEmpty', 'boolean', '', false, true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $subject = $arguments['subject'];
        if ($subject === null) {
            $subject = $renderChildrenClosure();
        }

        $delimiter = $arguments['delimiter'];
        switch ($delimiter) {
            case '\n':
                $delimiter = "\n";
                break;
            case '\r':
                $delimiter = "\r";
                break;
            case '\r\n':
                $delimiter = "\r\n";
                break;
            case '\t':
                $delimiter = "\t";
                break;
            default:
        }
        return \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
            $delimiter,
            $subject,
            $arguments['removeEmpty']
        );
    }
}
