<?php

namespace T3\Dce\EventListener;

use TYPO3\CMS\Backend\Controller\Event\AfterFormEnginePageInitializedEvent;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * @see \T3\Dce\UserFunction\FormEngineNode\DceCodeMirrorFieldRenderType
 */
class AfterFormEnginePageInitializedEventListener
{
    public function __construct(private readonly PageRenderer $pageRenderer)
    {
    }

    public function loadDceCodeEditor(AfterFormEnginePageInitializedEvent $event): void
    {
        $editParams = $event->getRequest()->getQueryParams()['edit'] ?? null;

        if (array_key_exists('tx_dce_domain_model_dce', $editParams)) {
            $this->pageRenderer->getJavaScriptRenderer()->addJavaScriptModuleInstruction(
                JavaScriptModuleInstruction::create('@t3/dce/code-editor')
            );
        }
    }
}
