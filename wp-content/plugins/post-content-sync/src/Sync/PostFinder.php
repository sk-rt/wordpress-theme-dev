<?php

namespace PostContentSync\Sync;

/**
 * Post Finder Class
 *
 * Finds existing WordPress posts based on identifiers.
 */
class PostFinder
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
     * Find post by metadata
     *
     * @param array $metadata Parsed metadata
     * @return int|null Post ID if found, null otherwise
     */
    public function findPost($metadata)
    {
        $identifierPriority = $this->config['identifier_priority'] ?? ['id', 'slug', 'path'];

        // Allow custom implementation to override the entire search process
        // Return post ID to use it, or null to use default behavior
        $postId = apply_filters('post-content-sync/pre_find_post', null, $metadata, $identifierPriority);

        if (is_int($postId) && $postId > 0) {
            return $postId;
        }

        // If filter returned null, use default behavior
        if ($postId === null) {
            foreach ($identifierPriority as $identifier) {
                // Default behavior
                switch ($identifier) {
                    case 'id':
                        $postId = $this->findById($metadata);
                        break;

                    case 'slug':
                        $postId = $this->findBySlug($metadata);
                        break;

                    case 'path':
                        $postId = $this->findByPath($metadata);
                        break;
                }

                if ($postId !== null) {
                    return $postId;
                }
            }
        }

        return null;
    }

    /**
     * Find post by ID
     *
     * @param array $metadata Metadata
     * @return int|null Post ID if found
     */
    private function findById($metadata)
    {
        if (empty($metadata['id'])) {
            return null;
        }

        $postId = intval($metadata['id']);
        $post = get_post($postId);

        if ($post && $post->post_type === ($metadata['post_type'] ?? 'page')) {
            return $postId;
        }

        return null;
    }

    /**
     * Find post by slug
     *
     * @param array $metadata Metadata
     * @return int|null Post ID if found
     */
    private function findBySlug($metadata)
    {
        if (empty($metadata['slug'])) {
            return null;
        }

        $postType = $metadata['post_type'] ?? 'page';
        $slug = sanitize_title($metadata['slug']);

        $posts = get_posts([
            'name' => $slug,
            'post_type' => $postType,
            'post_status' => 'any',
            'numberposts' => 1,
            'fields' => 'ids',
        ]);

        if (!empty($posts)) {
            return $posts[0];
        }

        return null;
    }

    /**
     * Find post by WordPress page path
     *
     * Uses get_page_by_path() to find posts by their hierarchical path.
     * Example: "company/about" finds a page with slug "about" under parent "company"
     *
     * @param array $metadata Metadata
     * @return int|null Post ID if found
     */
    private function findByPath($metadata)
    {
        if (empty($metadata['path'])) {
            return null;
        }

        $postType = $metadata['post_type'] ?? 'page';
        $path = $metadata['path'];

        // Sanitize each path segment separately to preserve hierarchy
        if (strpos($path, '/') !== false) {
            $segments = explode('/', $path);
            $segments = array_map('sanitize_title', $segments);
            $path = implode('/', $segments);
        } else {
            $path = sanitize_title($path);
        }

        $post = get_page_by_path($path, OBJECT, $postType);

        if ($post && isset($post->ID)) {
            return $post->ID;
        }

        return null;
    }

    /**
     * Check if post exists
     *
     * @param int $postId Post ID
     * @return bool True if post exists
     */
    public function postExists($postId)
    {
        if (empty($postId)) {
            return false;
        }

        $post = get_post($postId);
        return $post !== null;
    }
}
