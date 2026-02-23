<?php
/**
 * Default Plugin Configuration
 *
 * This file contains the default configuration for the Content Sync plugin.
 * These settings can be overridden using the 'post-content-sync/config' filter.
 *
 * @package PostContentSync
 */

return [
    /**
     * Base directory for HTML content files
     *
     * Default: {theme_directory}/_static-posts
     * Can be absolute or relative to theme directory
     */
    'base_directory' => get_template_directory() . '/_static-posts',

    /**
     * Meta tag prefix
     *
     * Used in HTML files for meta tags and content element ID
     * Example: <meta name="psc:post:post_type" content="page">
     */
    'prefix' => 'psc',

    /**
     * Content element ID
     *
     * The ID of the HTML element that contains the post content
     * Example: <div id="psc:content">...</div>
     */
    'content_element_id' => '{prefix}:content',

    /**
     * Log level
     *
     * Available levels: debug, info, warning, error
     */
    'log_level' => 'debug',

    /**
     * Identifier priority
     *
     * Order of priority when searching for existing posts.
     * The plugin will try each identifier in order until a match is found.
     *
     * Options:
     * - 'id': Search by post ID (from psc:identify:id)
     * - 'slug': Search by post slug (from psc:identify:slug)
     * - 'path': Search by WordPress page path including parent-child hierarchy
     *           (from psc:identify:path). Uses get_page_by_path().
     *           Example: "company/about" finds the "about" page under "company"
     *
     * Default order: ['id', 'slug', 'path']
     */
    'identifier_priority' => ['id', 'slug', 'path'],

    /**
     * Required meta tags
     *
     * Meta tags that must be present in HTML files
     */
    'required_meta_tags' => [
        '{prefix}:post:post_type',
    ],

    /**
     * Required meta fields
     *
     * Note: Validation now requires:
     * 1. post_type (always required)
     * 2. At least one identifier (id, slug, or path)
     *
     * This config option is kept for backward compatibility but not actively used.
     */
    'required_meta_fields' => ['post_type', 'slug'],

    /**
     * Default post status
     *
     * Status to use if not specified in HTML file
     * Options: 'publish', 'draft', 'pending', 'private'
     */
    'default_post_status' => 'draft',

    /**
     * Force update
     *
     * Whether to force update even if content hasn't changed
     * Default: false (only update when content changes)
     */
    'force_update' => false,

    /**
     * Sanitize content
     *
     * Whether to sanitize HTML content before saving
     * Uses wp_kses_post() for sanitization
     */
    'sanitize_content' => true,

    /**
     * Allowed HTML tags
     *
     * HTML tags allowed in content when sanitizing
     * Only used if wp_kses_post() is not available
     */
    'allowed_html_tags' => [
        'a', 'abbr', 'address', 'area', 'article', 'aside', 'audio',
        'b', 'bdi', 'bdo', 'blockquote', 'br', 'button',
        'caption', 'cite', 'code', 'col', 'colgroup',
        'data', 'datalist', 'dd', 'del', 'details', 'dfn', 'div', 'dl', 'dt',
        'em', 'embed',
        'fieldset', 'figcaption', 'figure', 'footer', 'form',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hr',
        'i', 'iframe', 'img', 'input', 'ins',
        'kbd',
        'label', 'legend', 'li',
        'main', 'map', 'mark', 'meter',
        'nav',
        'ol', 'optgroup', 'option', 'output',
        'p', 'picture', 'pre', 'progress',
        'q',
        'rp', 'rt', 'ruby',
        's', 'samp', 'section', 'select', 'small', 'source', 'span', 'strong', 'sub', 'summary', 'sup',
        'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'time', 'tr', 'track',
        'u', 'ul',
        'var', 'video',
        'wbr'
    ],

    /**
     * Enable versioning
     *
     * DEPRECATED: Versioning has been removed for simplicity.
     * All syncs now update posts without version checking.
     * This option is kept for backward compatibility but has no effect.
     */
    'enable_versioning' => false,

    /**
     * Metadata handlers
     *
     * Custom handlers for specific metadata
     * Can be extended via 'post-content-sync/metadata_handlers' filter
     *
     * Example:
     * 'bogo:locale' => function($value, $postId) {
     *     update_post_meta($postId, '_locale', $value);
     * }
     */
    'metadata_handlers' => [],

    /**
     * File extensions
     *
     * Allowed file extensions for HTML files
     */
    'allowed_extensions' => ['html', 'htm'],

    /**
     * Ignore files
     *
     * Array of filename patterns to ignore (supports wildcards)
     */
    'ignore_files' => [
        '.*',           // Hidden files
        '_*',           // Files starting with underscore
        'template.*',   // Template files
    ],

    /**
     * Cache settings
     *
     * Whether to clear cache after sync
     */
    'clear_cache_after_sync' => true,

    /**
     * Backup before update
     *
     * Create backup of existing content before updating
     */
    'backup_before_update' => false,
];
