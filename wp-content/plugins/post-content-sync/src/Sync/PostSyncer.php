<?php

namespace PostContentSync\Sync;

/**
 * Post Syncer Class
 *
 * Handles synchronization of parsed HTML data to WordPress posts.
 */
class PostSyncer
{
    /**
     * Plugin configuration
     *
     * @var array
     */
    private $config;

    /**
     * Post finder
     *
     * @var PostFinder
     */
    private $postFinder;

    /**
     * Metadata handler
     *
     * @var MetadataHandler
     */
    private $metadataHandler;

    /**
     * Constructor
     *
     * @param array $config Plugin configuration
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->postFinder = new PostFinder($config);
        $this->metadataHandler = new MetadataHandler($config);
    }

    /**
     * Sync parsed data to WordPress
     *
     * @param array $parsedData Parsed data from HtmlParser
     * @param array $options Sync options (e.g., ['dry_run' => true])
     * @return array Sync result
     */
    public function sync($parsedData, $options = [])
    {
        $metadata = $parsedData['metadata'] ?? [];
        $content = $parsedData['content'] ?? '';

        // Find existing post
        $postId = $this->postFinder->findPost($metadata);
        $isUpdate = ($postId !== null);

        // Dry run mode
        if (!empty($options['dry_run'])) {
            return [
                'action' => $isUpdate ? 'would_update' : 'would_create',
                'post_id' => $postId,
                'message' => $isUpdate ? 'Would update post' : 'Would create new post',
                'metadata' => $metadata,
            ];
        }

        // Prepare post data
        $postData = $this->preparePostData($metadata, $content, $postId);

        // Allow custom post creation/update logic
        // Return post ID to skip default wp_insert_post/wp_update_post, or null to use default
        $customPostId = apply_filters('post-content-sync/pre_sync_post', null, $metadata, $content, $postId, $isUpdate);

        if ($customPostId !== null && is_int($customPostId) && $customPostId > 0) {
            // Custom handler created/updated the post, use that post ID
            $postId = $customPostId;
        } else {
            // Default behavior: Insert or update post
            if ($isUpdate) {
                $postData['ID'] = $postId;
                $result = wp_update_post($postData, true);
            } else {
                $result = wp_insert_post($postData, true);
            }

            // Check for errors
            if (is_wp_error($result)) {
                return [
                    'action' => 'error',
                    'post_id' => $postId,
                    'message' => $result->get_error_message(),
                    'error' => $result,
                ];
            }

            $postId = $result;
        }

        // Update metadata
        $this->metadataHandler->updateMetadata($postId, $metadata, $parsedData);

        // Clear cache if configured
        if ($this->config['clear_cache_after_sync'] ?? true) {
            clean_post_cache($postId);
        }

        return [
            'action' => $isUpdate ? 'updated' : 'created',
            'post_id' => $postId,
            'message' => $isUpdate ? 'Post updated successfully' : 'Post created successfully',
        ];
    }

    /**
     * Prepare post data for wp_insert_post/wp_update_post
     *
     * @param array $metadata Metadata
     * @param string $content Post content
     * @param int|null $postId Existing post ID (for updates)
     * @return array Post data
     */
    private function preparePostData($metadata, $content, $postId = null)
    {
        $isUpdate = ($postId !== null);

        $postData = [
            'post_type' => $metadata['post_type'] ?? 'page',
            'post_title' => $metadata['post_title'] ?? '',
            'post_content' => $content,
            'post_status' => $metadata['post_status'] ?? ($this->config['default_post_status'] ?? 'draft'),
        ];

        // Only set post_name if explicitly provided via slug
        // When updating existing posts, don't overwrite the slug unless explicitly specified
        if (!empty($metadata['slug'])) {
            $postData['post_name'] = sanitize_title($metadata['slug']);
        } elseif (!$isUpdate) {
            // For new posts, use path as fallback if slug is not provided
            $path = $metadata['path'] ?? '';
            if (!empty($path)) {
                // Check if path contains hierarchy (e.g., "company/about")
                if (strpos($path, '/') !== false) {
                    $segments = explode('/', $path);
                    $parentPath = implode('/', array_slice($segments, 0, -1));
                    $childSlug = end($segments);

                    // Try to find parent page
                    $parentPost = get_page_by_path($parentPath, OBJECT, $metadata['post_type'] ?? 'page');

                    if ($parentPost) {
                        // Parent found, set hierarchy
                        $postData['post_parent'] = $parentPost->ID;
                        $postData['post_name'] = sanitize_title($childSlug);
                    } else {
                        // Parent not found, use full path as slug
                        $postData['post_name'] = sanitize_title($path);
                    }
                } else {
                    // No hierarchy, use path as slug
                    $postData['post_name'] = sanitize_title($path);
                }
            }
        }
        // If updating and no slug specified, don't include post_name (keep existing)

        // Add optional fields if present
        if (!empty($metadata['post_excerpt'])) {
            $postData['post_excerpt'] = $metadata['post_excerpt'];
        }

        if (!empty($metadata['post_date'])) {
            $postData['post_date'] = $metadata['post_date'];
        }

        if (!empty($metadata['post_author'])) {
            $postData['post_author'] = $metadata['post_author'];
        }

        if (!empty($metadata['menu_order'])) {
            $postData['menu_order'] = intval($metadata['menu_order']);
        }

        if (!empty($metadata['post_parent'])) {
            $postData['post_parent'] = intval($metadata['post_parent']);
        }

        // Allow filtering of post data
        $postData = apply_filters('post-content-sync/post_data', $postData, $metadata, $postId);

        return $postData;
    }

    /**
     * Sync multiple files
     *
     * @param array $parsedDataArray Array of parsed data
     * @param array $options Sync options
     * @return array Array of sync results
     */
    public function syncMultiple($parsedDataArray, $options = [])
    {
        $results = [];

        foreach ($parsedDataArray as $parsedData) {
            $result = $this->sync($parsedData, $options);
            $results[] = $result;
        }

        return $results;
    }
}
