<?php

namespace PostContentSync\Sync;

/**
 * Metadata Handler Class
 *
 * Handles post metadata, custom fields, and taxonomies.
 */
class MetadataHandler
{
    /**
     * Meta key: Last sync timestamp
     * Also serves as sync flag - if exists, post was synced
     */
    const META_SYNCED_AT = '_psc_synced_at';

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
     * Update post metadata
     *
     * @param int $postId Post ID
     * @param array $metadata Metadata from parsed file
     * @param array $parsedData Full parsed data
     * @return void
     */
    public function updateMetadata($postId, $metadata, $parsedData)
    {
        // Store custom fields (postmeta)
        if (!empty($metadata['postmeta'])) {
            foreach ($metadata['postmeta'] as $key => $value) {
                update_post_meta($postId, $key, $value);
            }
        }

        // Store sync timestamp (also serves as sync flag)
        $this->setSyncedAt($postId, current_time('timestamp'));

        // Update taxonomies
        if (!empty($metadata['taxonomies'])) {
            $this->updateTaxonomies($postId, $metadata['taxonomies']);
        }

        // Run custom metadata handlers
        $this->runCustomHandlers($postId, $metadata);
    }

    /**
     * Update taxonomies
     *
     * @param int $postId Post ID
     * @param array $taxonomies Taxonomies data
     * @return void
     */
    private function updateTaxonomies($postId, $taxonomies)
    {
        foreach ($taxonomies as $taxonomy => $terms) {
            if (empty($terms)) {
                continue;
            }

            // Check if taxonomy exists
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }

            // Convert term names to term IDs
            $termIds = [];
            foreach ($terms as $termName) {
                $term = get_term_by('name', $termName, $taxonomy);

                if (!$term) {
                    // Create term if it doesn't exist
                    $result = wp_insert_term($termName, $taxonomy);
                    if (!is_wp_error($result)) {
                        $termIds[] = $result['term_id'];
                    }
                } else {
                    $termIds[] = $term->term_id;
                }
            }

            // Set terms for post
            if (!empty($termIds)) {
                wp_set_object_terms($postId, $termIds, $taxonomy);
            }
        }
    }

    /**
     * Run custom metadata handlers
     *
     * @param int $postId Post ID
     * @param array $metadata Metadata
     * @return void
     */
    private function runCustomHandlers($postId, $metadata)
    {
        $handlers = $this->config['metadata_handlers'] ?? [];
        $handlers = apply_filters('post-content-sync/metadata_handlers', $handlers);

        foreach ($handlers as $key => $handler) {
            if (is_callable($handler) && isset($metadata[$key])) {
                // Pass full metadata as third parameter for access to all fields
                call_user_func($handler, $metadata[$key], $postId, $metadata);
            }
        }
    }

    /**
     * Delete post metadata
     *
     * @param int $postId Post ID
     * @return void
     */
    public function deleteMetadata($postId)
    {
        delete_post_meta($postId, self::META_SYNCED_AT);
    }

    /**
     * Check if post was synced by plugin
     *
     * @param int $postId Post ID
     * @return bool
     */
    public function isSynced($postId)
    {
        return $this->getSyncedAt($postId) !== null;
    }

    /**
     * Get last sync timestamp
     *
     * @param int $postId Post ID
     * @return int|null Unix timestamp or null if not synced
     */
    public function getSyncedAt($postId)
    {
        $timestamp = get_post_meta($postId, self::META_SYNCED_AT, true);
        return $timestamp ? (int) $timestamp : null;
    }

    /**
     * Set last sync timestamp
     *
     * @param int $postId Post ID
     * @param int $timestamp Unix timestamp
     * @return void
     */
    private function setSyncedAt($postId, $timestamp)
    {
        update_post_meta($postId, self::META_SYNCED_AT, (int) $timestamp);
    }
}
