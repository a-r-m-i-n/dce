<?php

namespace T3\Dce\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Domain\Repository\DceRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Allows you to render a DCE content element, in any Fluid template.
 *
 * Example
 * =======
 *
 * <dce:dce uid="1">
 *   {dce.render}
 * </dce:dce>
 *
 * <dce:dce uid="1">
 *   {field.firstName} {field.lastName}
 * </dce:dce>
 */
class DceViewHelper extends AbstractViewHelper
{
    protected $escapeChildren = false;

    protected $escapeOutput = false;

    /**
     * @var DceRepository|null
     */
    private static $dceRepository;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'uid',
            'integer',
            'Content Element UID (tt_content)',
            true
        );
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $contentElementUid = $arguments['uid'];

        if (!self::$dceRepository) {
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            self::$dceRepository = $objectManager->get(DceRepository::class);
        }

        $dce = self::$dceRepository->getDceInstance($contentElementUid);

        $templateVariableContainer = $renderingContext->getVariableProvider();
        $templateVariableContainer->add('dce', $dce);
        $templateVariableContainer->add('fields', $fields = $dce->getGet());
        $templateVariableContainer->add('field', $fields);
        $templateVariableContainer->add('contentObject', $dce->getContentObject());

        return $renderChildrenClosure();
    }
}
