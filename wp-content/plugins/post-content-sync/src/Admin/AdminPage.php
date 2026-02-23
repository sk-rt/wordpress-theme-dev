<?php

namespace PostContentSync\Admin;

/**
 * Admin Page Class
 *
 * Handles the admin interface for content synchronization.
 */
class AdminPage
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
     * Register admin page
     *
     * @return void
     */
    public function register()
    {
        add_management_page(
            __('Content Sync', 'post-content-sync'),
            __('Content Sync', 'post-content-sync'),
            'manage_options',
            'post-content-sync',
            [$this, 'renderPage']
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueueAssets($hook)
    {
        if ($hook !== 'tools_page_post-content-sync') {
            return;
        }

        // Enqueue styles
        wp_enqueue_style(
            'post-content-sync-admin',
            POST_CONTENT_SYNC_PLUGIN_URL . 'assets/admin.css',
            [],
            '1.0.0'
        );

        // Enqueue scripts
        wp_enqueue_script(
            'post-content-sync-admin',
            POST_CONTENT_SYNC_PLUGIN_URL . 'assets/admin.js',
            ['jquery'],
            '1.0.0',
            true
        );

        // Localize script
        wp_localize_script(
            'post-content-sync-admin',
            'postContentSync',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('post_content_sync'),
                'strings' => [
                    'syncing' => __('Syncing...', 'post-content-sync'),
                    'success' => __('Sync completed successfully!', 'post-content-sync'),
                    'error' => __('Sync failed. Please check the logs.', 'post-content-sync'),
                    'confirm' => __('Are you sure you want to sync all files?', 'post-content-sync'),
                ],
            ]
        );
    }

    /**
     * Render admin page
     *
     * @return void
     */
    public function renderPage()
    {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $fileData = [];
        $scanError = null;

        try {
            $scanner = new \PostContentSync\FileScanner($this->config);
            $files = $scanner->scan();
            $parser = new \PostContentSync\Parser\HtmlParser($this->config);
            $postFinder = new \PostContentSync\Sync\PostFinder($this->config);

            // Prepare file data with sync status
            $metadataHandler = new \PostContentSync\Sync\MetadataHandler($this->config);

            foreach ($files as $file) {
                $parsed = null;
                $status = 'unknown';
                $postId = null;
                $syncedAt = null;

                try {
                    $parsed = $parser->parse($file['path']);

                    if ($parsed !== null) {
                        $postId = $postFinder->findPost($parsed['metadata']);
                        if ($postId) {
                            // Check if this post was synced by the plugin
                            if ($metadataHandler->isSynced($postId)) {
                                $status = 'synced';
                                $syncedAt = $metadataHandler->getSyncedAt($postId);
                            } else {
                                $status = 'exists';
                            }
                        } else {
                            $status = 'new';
                        }
                    } else {
                        $status = 'error';
                    }
                } catch (\Exception $e) {
                    $status = 'error';
                }

                $fileData[] = [
                    'file' => $file,
                    'parsed' => $parsed,
                    'status' => $status,
                    'post_id' => $postId,
                    'synced_at' => $syncedAt,
                ];
            }
        } catch (\Exception $e) {
            $scanError = $e->getMessage();
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php if ($scanError): ?>
                <div class="notice notice-error">
                    <p><strong><?php _e('Error:', 'post-content-sync'); ?></strong> <?php echo esc_html($scanError); ?></p>
                </div>
            <?php endif; ?>

            <div class="post-content-sync-header">
                <div class="post-content-sync-config">
                    <h2><?php _e('Configuration', 'post-content-sync'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Base Directory', 'post-content-sync'); ?></th>
                            <td>
                                <code><?php echo esc_html($this->config['base_directory']); ?></code>
                                <?php
                                $baseDir = $this->config['base_directory'];
                                if (!empty($baseDir) && is_dir($baseDir)) {
                                    echo '<br><span style="color: green;">✓ ' . __('Directory exists', 'post-content-sync') . '</span>';
                                } else {
                                    echo '<br><span style="color: red;">✗ ' . __('Directory not found', 'post-content-sync') . '</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Prefix', 'post-content-sync'); ?></th>
                            <td><code><?php echo esc_html($this->config['prefix']); ?></code></td>
                        </tr>
                        <tr>
                            <th><?php _e('Language', 'post-content-sync'); ?></th>
                            <td><code><?php echo esc_html(get_bloginfo('language')); ?></code></td>
                        </tr>
                    </table>
                </div>

                <div class="post-content-sync-actions">
                    <h2><?php _e('Sync Actions', 'post-content-sync'); ?></h2>
                    <div class="sync-options">
                        <label>
                            <input type="checkbox" id="sync-dry-run" value="1">
                            <?php _e('Dry Run (preview only)', 'post-content-sync'); ?>
                        </label>
                    </div>
                    <button type="button" class="button button-primary" id="sync-all-files">
                        <?php _e('Sync All Files', 'post-content-sync'); ?>
                    </button>

                    <div id="sync-progress" style="display: none; margin-top: 15px;">
                        <div class="">
                            <p><strong><?php _e('Syncing...', 'post-content-sync'); ?></strong></p>
                            <div class="progress-bar">
                                <div class="progress-bar-fill" style="width: 0%;"></div>
                            </div>
                            <p class="sync-status"></p>
                        </div>
                    </div>

                    <div id="sync-result" style="display: none; margin-top: 15px;"></div>
                </div>
            </div>

            <h2><?php _e('Files', 'post-content-sync'); ?></h2>

            <?php if (empty($fileData)): ?>
                <div class="notice notice-warning">
                    <p><?php _e('No HTML files found in the base directory.', 'post-content-sync'); ?></p>
                </div>
            <?php else: ?>
                <?php
                // Get sorting parameters
                $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'file';
                $order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';

                // Sort fileData
                usort($fileData, function($a, $b) use ($orderby, $order) {
                    $result = 0;

                    if ($orderby === 'file') {
                        $result = strcmp($a['file']['filename'], $b['file']['filename']);
                    } elseif ($orderby === 'status') {
                        $result = strcmp($a['status'], $b['status']);
                    }

                    return $order === 'desc' ? -$result : $result;
                });

                // Helper function to generate sortable column header
                $getSortableColumnHeader = function($column, $label) use ($orderby, $order) {
                    $newOrder = ($orderby === $column && $order === 'asc') ? 'desc' : 'asc';
                    $url = add_query_arg([
                        'page' => 'post-content-sync',
                        'orderby' => $column,
                        'order' => $newOrder
                    ], admin_url('admin.php'));

                    $arrow = '';
                    if ($orderby === $column) {
                        $arrow = $order === 'asc' ? ' ▲' : ' ▼';
                    }

                    return '<a href="' . esc_url($url) . '">' . esc_html($label) . $arrow . '</a>';
                };

                // カスタム列定義用フック
                $custom_columns = apply_filters('post-content-sync/admin_columns', []);
                ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo $getSortableColumnHeader('file', __('File', 'post-content-sync')); ?></th>
                            <th><?php _e('Identifier', 'post-content-sync'); ?></th>
                            <th><?php _e('Post Type', 'post-content-sync'); ?></th>
                            <th><?php echo $getSortableColumnHeader('status', __('Status', 'post-content-sync')); ?></th>
                            <th><?php _e('Post', 'post-content-sync'); ?></th>
                            <?php
                            // カスタム列ヘッダー
                            foreach ($custom_columns as $column_key => $column_label) {
                                echo '<th>' . esc_html($column_label) . '</th>';
                            }
                            ?>
                            <th><?php _e('Actions', 'post-content-sync'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fileData as $data): ?>
                            <?php
                            // Check if row should be grayed out (synced and unchanged)
                            $isUnchanged = false;
                            if ($data['status'] === 'synced' && $data['synced_at'] && $data['post_id']) {
                                $post = get_post($data['post_id']);
                                if ($post) {
                                    // Compare timestamps rounded to seconds (ignore sub-seconds)
                                    $syncedTime = date('Y-m-d H:i', $data['synced_at']);
                                    $modifiedTime = date('Y-m-d H:i', strtotime($post->post_modified));
                                    if ($syncedTime === $modifiedTime) {
                                        $isUnchanged = true;
                                    }
                                }
                            }
                            ?>
                            <tr<?php echo $isUnchanged ? ' class="synced-unchanged"' : ''; ?>>
                                <td>
                                    <strong><?php echo esc_html($data['file']['filename']); ?></strong>
                                    <br>
                                    <small>
                                        <strong><?php _e('Path:', 'post-content-sync'); ?></strong>
                                        <?php echo esc_html($data['file']['relative_path']); ?>
                                    </small>
                                    <?php if ($data['parsed'] && !empty($data['parsed']['metadata']['post_title'])): ?>
                                        <br>
                                        <small>
                                            <strong><?php _e('Title:', 'post-content-sync'); ?></strong>
                                            <?php echo esc_html($data['parsed']['metadata']['post_title']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($data['parsed']) {
                                        $metadata = $data['parsed']['metadata'];
                                        $identifier = '';
                                        $identifierType = '';

                                        // Check for identifiers in priority order
                                        if (!empty($metadata['id'])) {
                                            $identifier = $metadata['id'];
                                            $identifierType = 'ID';
                                        } elseif (!empty($metadata['slug'])) {
                                            $identifier = $metadata['slug'];
                                            $identifierType = 'Slug';
                                        } elseif (!empty($metadata['path'])) {
                                            $identifier = $metadata['path'];
                                            $identifierType = 'Path';
                                        }

                                        if ($identifier) {
                                            echo '<strong>' . esc_html($identifierType) . ':</strong> ';
                                            echo esc_html($identifier);
                                        } else {
                                            echo '-';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($data['parsed']) {
                                        $postType = $data['parsed']['metadata']['post_type'] ?? null;
                                        if ($postType) {
                                            $postTypeObject = get_post_type_object($postType);
                                            if ($postTypeObject) {
                                                // Get post type label
                                                $label = $postTypeObject->labels->name ?? $postType;

                                                // Build admin URL
                                                if ($postType === 'post') {
                                                    $url = admin_url('edit.php');
                                                } else {
                                                    $url = admin_url('edit.php?post_type=' . $postType);
                                                }

                                                echo '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
                                            } else {
                                                echo esc_html($postType);
                                            }
                                        } else {
                                            echo '-';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $statusLabels = [
                                        'synced' => __('Synced', 'post-content-sync'),
                                        'exists' => __('Exists', 'post-content-sync'),
                                        'new' => __('New', 'post-content-sync'),
                                        'error' => __('Error', 'post-content-sync'),
                                        'unknown' => __('Unknown', 'post-content-sync'),
                                    ];
                                    $statusClasses = [
                                        'synced' => 'status-synced',
                                        'exists' => 'status-exists',
                                        'new' => 'status-new',
                                        'error' => 'status-error',
                                        'unknown' => 'status-unknown',
                                    ];
                                    ?>
                                    <span class="status-badge <?php echo esc_attr($statusClasses[$data['status']]); ?>">
                                        <?php echo esc_html($statusLabels[$data['status']]); ?>
                                        <?php if ($data['status'] === 'synced' && !$isUnchanged): ?>
                                            <span class="status-indicator status-indicator--modified"></span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($data['status'] === 'synced' || $data['status'] === 'exists') && $data['post_id']): ?>
                                        <?php
                                        $post = get_post($data['post_id']);
                                        if ($post):
                                        ?>
                                            <a href="<?php echo esc_url(get_edit_post_link($data['post_id'])); ?>" target="_blank">
                                                <strong><?php echo esc_html($post->post_title); ?></strong>
                                            </a>
                                          
                                            <?php if ($data['status'] === 'synced' && $data['synced_at']): ?>
                                                <br>
                                                <small>
                                                    <strong><?php _e('Last synced:', 'post-content-sync'); ?></strong>
                                                    <?php
                                                    echo date_i18n(
                                                        get_option('date_format') . ' ' . get_option('time_format'),
                                                        $data['synced_at']
                                                    );
                                                    ?>
                                                </small>
                                            <?php endif; ?>
                                            <br>
                                            <small>
                                                <strong><?php _e('Modified:', 'post-content-sync'); ?></strong>
                                                <?php
                                                echo date_i18n(
                                                    get_option('date_format') . ' ' . get_option('time_format'),
                                                    strtotime($post->post_modified)
                                                );
                                                ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <?php
                                // カスタム列コンテンツ用フック
                                foreach ($custom_columns as $column_key => $column_label) {
                                    echo '<td>';
                                    do_action('post-content-sync/admin_column_' . $column_key, $data);
                                    echo '</td>';
                                }
                                ?>
                                <td>
                                    <?php if ($data['parsed']): ?>
                                        <button type="button" class="button button-small sync-single-file"
                                                data-file="<?php echo esc_attr($data['file']['path']); ?>">
                                            <?php _e('Sync', 'post-content-sync'); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="post-content-sync-footer">
                <h2><?php _e('WP-CLI Usage', 'post-content-sync'); ?></h2>
                <p><?php _e('You can also use WP-CLI to sync content:', 'post-content-sync'); ?></p>
                <pre><code>wp content-sync sync [--dry-run] [--verbose]</code></pre>
            </div>
        </div>
        <?php
    }
}
