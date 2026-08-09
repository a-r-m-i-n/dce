<?php

namespace T3\Dce\Components\TemplateRenderer;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2026 Armin Vieweg <armin@v.ieweg.de>
 */

/**
 * Contains all template types a DCE can have.
 */
enum DceTemplateTypes: int
{
    // Identifier for: "default DCE templates"
    case DEFAULT = 0;
    // Identifier for: "detail page templates"
    case DETAILPAGE = 3;
    // Identifier for: "dce container templates"
    case CONTAINER = 4;
    // Identifier for: "backend template"
    case BACKEND_TEMPLATE = 5;

    /**
     * Returns the database field names for this template type.
     *
     * @return array{type: string, inline: string, file: string}
     */
    public function getTemplateFields(): array
    {
        return match ($this) {
            self::DEFAULT => [
                'type' => 'template_type',
                'inline' => 'template_content',
                'file' => 'template_file',
            ],
            self::DETAILPAGE => [
                'type' => 'detailpage_template_type',
                'inline' => 'detailpage_template',
                'file' => 'detailpage_template_file',
            ],
            self::CONTAINER => [
                'type' => 'container_template_type',
                'inline' => 'container_template',
                'file' => 'container_template_file',
            ],
            self::BACKEND_TEMPLATE => [
                'type' => 'backend_template_type',
                'inline' => 'backend_template_content',
                'file' => 'backend_template_file',
            ],
        };
    }
}
