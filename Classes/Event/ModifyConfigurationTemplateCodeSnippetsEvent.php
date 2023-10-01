<?php

namespace T3\Dce\Event;

class ModifyConfigurationTemplateCodeSnippetsEvent
{
    public function __construct(private array $templates)
    {
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }

    public function setTemplates(array $templates): void
    {
        $this->templates = $templates;
    }
}
