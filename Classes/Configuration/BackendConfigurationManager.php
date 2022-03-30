<?php

namespace T3\Dce\Configuration;

use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager as BaseBackendConfigurationManager;

class BackendConfigurationManager extends BaseBackendConfigurationManager
{
    public function setCurrentPageId(int $currentPageId)
    {
        $this->currentPageId = $currentPageId;
    }
}
