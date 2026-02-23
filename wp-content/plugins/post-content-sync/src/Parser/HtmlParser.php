<?php

namespace PostContentSync\Parser;

/**
 * HTML Parser Class
 *
 * Parses HTML files and extracts metadata and content.
 */
class HtmlParser
{
    /**
     * Plugin configuration
     *
     * @var array
     */
    private $config;

    /**
     * Metadata extractor
     *
     * @var MetadataExtractor
     */
    private $metadataExtractor;

    /**
     * Content extractor
     *
     * @var ContentExtractor
     */
    private $contentExtractor;

    /**
     * Shortcode placeholder map
     *
     * @var array
     */
    private $shortcodeMap = [];

    /**
     * Constructor
     *
     * @param array $config Plugin configuration
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->metadataExtractor = new MetadataExtractor($config);
        $this->contentExtractor = new ContentExtractor($config);
    }

    /**
     * Parse HTML file
     *
     * @param string $filePath Path to HTML file
     * @return array|null Parsed data or null on failure
     */
    public function parse($filePath)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return null;
        }

        $html = file_get_contents($filePath);
        if ($html === false) {
            return null;
        }

        // Create DOMDocument
        $dom = new \DOMDocument();

        // Suppress warnings for malformed HTML
        $previousErrorLevel = libxml_use_internal_errors(true);

        // Load HTML with UTF-8 encoding
        // Add meta charset if not present to ensure proper UTF-8 handling
        if (stripos($html, '<meta charset') === false && stripos($html, '<meta http-equiv="Content-Type"') === false) {
            $html = preg_replace('/<head>/i', '<head><meta charset="UTF-8">', $html, 1);
        }

        // Preserve shortcodes by replacing them with placeholders before parsing
        $shortcodeMap = [];
        $placeholderIndex = 0;
        $html = preg_replace_callback(
            '/\[([^\]]+)\]/',
            function ($matches) use (&$shortcodeMap, &$placeholderIndex) {
                $placeholder = '__SHORTCODE_PLACEHOLDER_' . $placeholderIndex . '__';
                $shortcodeMap[$placeholder] = $matches[0];
                $placeholderIndex++;
                return $placeholder;
            },
            $html
        );

        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Store the shortcode map in the parser for later restoration
        $this->shortcodeMap = $shortcodeMap;

        // Restore error handling
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorLevel);

        // Extract metadata from meta tags
        $metadata = $this->metadataExtractor->extract($dom);

        // Extract content from body
        $content = $this->contentExtractor->extract($dom);

        // Restore shortcodes from placeholders
        if (!empty($this->shortcodeMap)) {
            $content = str_replace(
                array_keys($this->shortcodeMap),
                array_values($this->shortcodeMap),
                $content
            );
        }

        // Validate required fields
        if (!$this->validateParsedData($metadata)) {
            return null;
        }

        return [
            'metadata' => $metadata,
            'content' => $content,
            'file_path' => $filePath,
            'file_modified_time' => filemtime($filePath),
        ];
    }

    /**
     * Validate parsed data
     *
     * @param array $metadata Metadata array
     * @return bool True if valid
     */
    private function validateParsedData($metadata)
    {
        // Post type is always required
        if (empty($metadata['post_type'])) {
            return false;
        }

        // At least one identifier must be present (id, slug, or path)
        $hasIdentifier = !empty($metadata['id']) ||
                         !empty($metadata['slug']) ||
                         !empty($metadata['path']);

        if (!$hasIdentifier) {
            return false;
        }

        return true;
    }

    /**
     * Parse multiple files
     *
     * @param array $files Array of file paths
     * @return array Array of parsed data
     */
    public function parseMultiple($files)
    {
        $results = [];

        foreach ($files as $file) {
            $filePath = is_array($file) ? $file['path'] : $file;
            $parsed = $this->parse($filePath);

            if ($parsed !== null) {
                $results[] = $parsed;
            }
        }

        return $results;
    }
}
