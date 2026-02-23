<?php

namespace PostContentSync\Parser;

/**
 * Metadata Extractor Class
 *
 * Extracts metadata from HTML meta tags.
 */
class MetadataExtractor
{
    /**
     * Plugin configuration
     *
     * @var array
     */
    private $config;

    /**
     * Constructor
     *
     * @param array $config Plugin configuration
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Extract metadata from DOMDocument
     *
     * @param \DOMDocument $dom DOM document
     * @return array Metadata array
     */
    public function extract($dom)
    {
        $metadata = [];
        $prefix = $this->config['prefix'] ?? 'psc';

        // Get all meta tags
        $metaTags = $dom->getElementsByTagName('meta');

        foreach ($metaTags as $meta) {
            $name = $meta->getAttribute('name');
            $content = $meta->getAttribute('content');

            // Only process meta tags with our prefix
            if (strpos($name, $prefix . ':') !== 0) {
                continue;
            }

            // Remove prefix and parse the meta name
            $metaKey = substr($name, strlen($prefix) + 1);
            $this->parseMetaKey($metaKey, $content, $metadata);
        }

        return $metadata;
    }

    /**
     * Parse meta key and store in metadata array
     *
     * @param string $metaKey Meta key (e.g., "post:post_title")
     * @param string $content Meta content value
     * @param array &$metadata Metadata array (passed by reference)
     * @return void
     */
    private function parseMetaKey($metaKey, $content, &$metadata)
    {
        $parts = explode(':', $metaKey);

        if (count($parts) === 1) {
            // Simple key (e.g., "version")
            $metadata[$parts[0]] = $content;
            return;
        }

        // Nested key (e.g., "post:post_title")
        $category = $parts[0];
        $field = $parts[1];

        switch ($category) {
            case 'post':
                // Post data (e.g., post_title, post_status)
                $metadata[$field] = $content;
                break;

            case 'identify':
                // Identification data (e.g., slug)
                $metadata[$field] = $content;
                break;

            case 'postmeta':
                // Custom fields
                if (!isset($metadata['postmeta'])) {
                    $metadata['postmeta'] = [];
                }
                $metadata['postmeta'][$field] = $content;
                break;

            case 'taxonomy':
                // Taxonomy terms (e.g., category, post_tag)
                if (!isset($metadata['taxonomies'])) {
                    $metadata['taxonomies'] = [];
                }
                if (!isset($metadata['taxonomies'][$field])) {
                    $metadata['taxonomies'][$field] = [];
                }
                // Split comma-separated values
                $terms = array_map('trim', explode(',', $content));
                $metadata['taxonomies'][$field] = array_merge(
                    $metadata['taxonomies'][$field],
                    $terms
                );
                break;

            default:
                // Unknown category, store as-is
                if (!isset($metadata[$category])) {
                    $metadata[$category] = [];
                }
                $metadata[$category][$field] = $content;
                break;
        }
    }

    /**
     * Get default metadata values
     *
     * @return array Default metadata
     */
    public function getDefaults()
    {
        return [
            'post_type' => 'page',
            'post_status' => 'draft',
            'post_title' => '',
            'slug' => '',
            'version' => '1.0',
            'postmeta' => [],
            'taxonomies' => [],
        ];
    }

    /**
     * Merge metadata with defaults
     *
     * @param array $metadata Extracted metadata
     * @return array Merged metadata
     */
    public function mergeWithDefaults($metadata)
    {
        return array_merge($this->getDefaults(), $metadata);
    }
}
