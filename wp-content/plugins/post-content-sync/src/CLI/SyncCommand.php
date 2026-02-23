<?php

namespace PostContentSync\CLI;

/**
 * WP-CLI Sync Command
 *
 * Handles content synchronization via WP-CLI.
 */
class SyncCommand
{
    /**
     * Sync HTML content to WordPress posts
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Preview changes without actually updating
     *
     * [--verbose]
     * : Show detailed output
     *
     * ## EXAMPLES
     *
     *     wp content-sync sync
     *     wp content-sync sync --dry-run
     *     wp content-sync sync --verbose
     *
     * @when after_wp_load
     */
    public function sync($args, $assocArgs)
    {
        \WP_CLI::line('Content Sync - Synchronization');
        \WP_CLI::line('');

        // Get configuration
        $config = $this->getConfig();

        // Scan files
        $scanner = new \PostContentSync\FileScanner($config);
        $files = $scanner->scan();

        \WP_CLI::line('Configuration:');
        \WP_CLI::line('  Base Directory: ' . $config['base_directory']);
        \WP_CLI::line('  Prefix: ' . $config['prefix']);
        \WP_CLI::line('');

        $totalFiles = count($files);
        \WP_CLI::line('Files found: ' . $totalFiles);
        \WP_CLI::line('');

        // Parse and sync files
        if ($totalFiles > 0) {
            \WP_CLI::line('Parsing and syncing files...');
            \WP_CLI::line('');

            $parser = new \PostContentSync\Parser\HtmlParser($config);
            $syncer = new \PostContentSync\Sync\PostSyncer($config);

            $dryRun = isset($assocArgs['dry-run']);
            $verbose = isset($assocArgs['verbose']);

            $syncOptions = [
                'dry_run' => $dryRun,
            ];

            if ($dryRun) {
                \WP_CLI::warning('DRY RUN MODE - No changes will be made to the database');
                \WP_CLI::line('');
            }

            $stats = [
                'parsed' => 0,
                'parse_failed' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => 0,
            ];

            foreach ($files as $file) {
                $parsed = $parser->parse($file['path']);

                if ($parsed === null) {
                    $stats['parse_failed']++;
                    if ($verbose) {
                        \WP_CLI::warning('  ✗ Parse failed: ' . $file['filename']);
                    }
                    continue;
                }

                $stats['parsed']++;

                // Sync to database
                $result = $syncer->sync($parsed, $syncOptions);

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

                // Display result
                if ($verbose || $result['action'] === 'error') {
                    $icon = $this->getActionIcon($result['action']);
                    $message = sprintf(
                        '  %s %s - %s',
                        $icon,
                        $file['filename'],
                        $result['message']
                    );

                    if ($result['action'] === 'error') {
                        \WP_CLI::error($message, false);
                    } else {
                        \WP_CLI::line($message);
                    }

                    if ($verbose && isset($result['post_id'])) {
                        \WP_CLI::line('    Post ID: ' . $result['post_id']);
                    }
                }
            }

            \WP_CLI::line('');
            \WP_CLI::line('Sync results:');
            \WP_CLI::line('  Parsed: ' . $stats['parsed']);
            \WP_CLI::line('  Parse failed: ' . $stats['parse_failed']);
            \WP_CLI::line('  Created: ' . $stats['created']);
            \WP_CLI::line('  Updated: ' . $stats['updated']);
            \WP_CLI::line('  Skipped: ' . $stats['skipped']);
            \WP_CLI::line('  Errors: ' . $stats['errors']);
            \WP_CLI::line('');

            if ($stats['errors'] > 0) {
                \WP_CLI::error('Sync completed with errors');
            } else {
                \WP_CLI::success('Sync completed successfully');
            }
        } else {
            \WP_CLI::warning('No files found to sync');
        }
    }

    /**
     * Get plugin configuration
     *
     * @return array
     */
    private function getConfig()
    {
        // Load default configuration
        $configFile = POST_CONTENT_SYNC_PLUGIN_DIR . 'config/default.php';

        if (file_exists($configFile)) {
            $config = require $configFile;

            // Replace {prefix} placeholder
            if (isset($config['content_element_id'])) {
                $config['content_element_id'] = str_replace(
                    '{prefix}',
                    $config['prefix'],
                    $config['content_element_id']
                );
            }
        } else {
            // Fallback config
            $config = [
                'base_directory' => get_template_directory() . '/_static-posts/pages',
                'prefix' => 'psc',
                'content_element_id' => 'psc:content',
            ];
        }

        // Apply filters (same as Plugin::loadConfiguration)
        return apply_filters('post-content-sync/config', $config);
    }

    /**
     * Get action icon for display
     *
     * @param string $action Action type
     * @return string Icon character
     */
    private function getActionIcon($action)
    {
        $icons = [
            'created' => '✓',
            'updated' => '↻',
            'skipped' => '○',
            'error' => '✗',
            'would_create' => '✓',
            'would_update' => '↻',
        ];

        return $icons[$action] ?? '•';
    }
}
