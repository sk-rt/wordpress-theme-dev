<?php

namespace PostContentSync\Admin;

/**
 * Ajax Handler Class
 *
 * Handles AJAX requests from the admin interface.
 */
class AjaxHandler
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
     * Register AJAX handlers
     *
     * @return void
     */
    public function register()
    {
        add_action('wp_ajax_post_content_sync_all', [$this, 'handleSyncAll']);
        add_action('wp_ajax_post_content_sync_single', [$this, 'handleSyncSingle']);
    }

    /**
     * Handle sync all files request
     *
     * @return void
     */
    public function handleSyncAll()
    {
        // Verify nonce
        check_ajax_referer('post_content_sync', 'nonce');

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'post-content-sync')]);
            return;
        }

        // Get options
        $dryRun = isset($_POST['dry_run']) && $_POST['dry_run'] === 'true';

        // Scan files
        $scanner = new \PostContentSync\FileScanner($this->config);
        $files = $scanner->scan();

        // Parse files
        $parser = new \PostContentSync\Parser\HtmlParser($this->config);
        $parsedFiles = [];

        foreach ($files as $file) {
            $parsed = $parser->parse($file['path']);
            if ($parsed !== null) {
                $parsedFiles[] = [
                    'parsed' => $parsed,
                    'file' => $file,
                ];
            }
        }

        // Sync files
        $syncer = new \PostContentSync\Sync\PostSyncer($this->config);
        $syncOptions = [
            'dry_run' => $dryRun,
        ];

        $results = [];
        $stats = [
            'total' => count($parsedFiles),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        foreach ($parsedFiles as $item) {
            $result = $syncer->sync($item['parsed'], $syncOptions);
            $result['file'] = $item['file']['filename'];
            $results[] = $result;

            // Update stats
            switch ($result['action']) {
                case 'created':
                case 'would_create':
                    $stats['created']++;
                    break;
                case 'updated':
                case 'would_update':
                    $stats['updated']++;
                    break;
                case 'skipped':
                    $stats['skipped']++;
                    break;
                case 'error':
                    $stats['errors']++;
                    break;
            }
        }

        wp_send_json_success([
            'stats' => $stats,
            'results' => $results,
            'dry_run' => $dryRun,
        ]);
    }

    /**
     * Handle sync single file request
     *
     * @return void
     */
    public function handleSyncSingle()
    {
        // Verify nonce
        check_ajax_referer('post_content_sync', 'nonce');

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'post-content-sync')]);
            return;
        }

        // Get file path
        $filePath = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : null;

        if (!$filePath || !file_exists($filePath)) {
            wp_send_json_error(['message' => __('File not found', 'post-content-sync')]);
            return;
        }

        // Parse file
        $parser = new \PostContentSync\Parser\HtmlParser($this->config);
        $parsed = $parser->parse($filePath);

        if ($parsed === null) {
            wp_send_json_error(['message' => __('Failed to parse file', 'post-content-sync')]);
            return;
        }

        // Sync file
        $syncer = new \PostContentSync\Sync\PostSyncer($this->config);
        $result = $syncer->sync($parsed, ['dry_run' => false]);

        if ($result['action'] === 'error') {
            wp_send_json_error([
                'message' => $result['message'],
                'result' => $result,
            ]);
            return;
        }

        wp_send_json_success([
            'result' => $result,
            'post_id' => $result['post_id'] ?? null,
        ]);
    }
}
