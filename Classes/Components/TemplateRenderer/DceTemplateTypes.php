<?php

namespace T3\Dce\Components\TemplateRenderer;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2022 Armin Vieweg <armin@v.ieweg.de>
 */

/**
 * This static class contains all template types a DCE can have.
 */
class DceTemplateTypes
{
    // Identifier for: "default DCE templates"
    public const DEFAULT = 0;
    // Identifier for: "detail page templates"
    public const DETAILPAGE = 3;
    // Identifier for: "dce container templates"
    public const CONTAINER = 4;
    // Identifier for: "backend template"
    public const BACKEND_TEMPLATE = 5;

    /**
     * @var array Database field names of columns for different types of templates
     */
    public static $templateFields = [
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
    ];
}
