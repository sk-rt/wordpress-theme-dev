<?php

namespace PostContentSync;

/**
 * Main Plugin Class
 *
 * Handles plugin initialization and coordinates all components.
 */
class Plugin
{
    /**
     * Plugin configuration
     *
     * @var array
     */
    private $config;

    /**
     * Plugin version
     *
     * @var string
     */
    private $version;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->version = defined('POST_CONTENT_SYNC_VERSION')
            ? POST_CONTENT_SYNC_VERSION
            : '1.0.0';
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function initialize()
    {
        // Load configuration
        $this->loadConfiguration();

        // Register hooks
        $this->registerHooks();

        // Initialize components
        $this->initializeComponents();
    }

    /**
     * Load plugin configuration
     *
     * @return void
     */
    private function loadConfiguration()
    {
        // Load default configuration
        $defaultConfig = $this->loadDefaultConfig();

        // Allow configuration to be filtered
        $this->config = apply_filters('post-content-sync/config', $defaultConfig);

        // Initialize logger
        Logger::init($this->config);
    }

    /**
     * Load default configuration file
     *
     * @return array
     */
    private function loadDefaultConfig()
    {
        $configFile = POST_CONTENT_SYNC_PLUGIN_DIR . 'config/default.php';

        if (file_exists($configFile)) {
            $config = require $configFile;

            // Replace {prefix} placeholder in content_element_id
            if (isset($config['content_element_id']) && isset($config['prefix'])) {
                $config['content_element_id'] = str_replace(
                    '{prefix}',
                    $config['prefix'],
                    $config['content_element_id']
                );
            }

            return $config;
        }

        // Fallback to hardcoded defaults
        return [
            'base_directory' => get_template_directory() . '/_static-posts/pages',
            'prefix' => 'psc',
            'content_element_id' => 'psc:content',
            'log_level' => 'info',
        ];
    }

    /**
     * Register WordPress hooks
     *
     * @return void
     */
    private function registerHooks()
    {
        // Load text domain for translations
        add_action('init', [$this, 'loadTextDomain']);

        // Always register admin hooks - WordPress will only fire them in admin context
        $this->registerAdminHooks();

        // CLI hooks
        if (defined('WP_CLI') && WP_CLI) {
            $this->registerCliHooks();
        }
    }

    /**
     * Load plugin text domain for translations
     *
     * @return void
     */
    public function loadTextDomain()
    {
        load_plugin_textdomain(
            'post-content-sync',
            false,
            dirname(POST_CONTENT_SYNC_PLUGIN_BASENAME) . '/languages'
        );
    }

    /**
     * Register admin-specific hooks
     *
     * @return void
     */
    private function registerAdminHooks()
    {
        $config = $this->config;

        // Initialize admin page
        add_action('admin_menu', function () use ($config) {
            $adminPage = new \PostContentSync\Admin\AdminPage($config);
            $adminPage->register();
        });

        // Enqueue admin assets
        add_action('admin_enqueue_scripts', function ($hook) use ($config) {
            $adminPage = new \PostContentSync\Admin\AdminPage($config);
            $adminPage->enqueueAssets($hook);
        });

        // Initialize ajax handler
        add_action('admin_init', function () use ($config) {
            $ajaxHandler = new \PostContentSync\Admin\AjaxHandler($config);
            $ajaxHandler->register();
        });

        // Show notice on synced post edit pages
        add_action('admin_notices', [$this, 'showSyncedPostNotice']);
    }

    /**
     * Show admin notice on synced post edit pages
     *
     * @return void
     */
    public function showSyncedPostNotice()
    {
        // Only show on post edit pages
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'post') {
            return;
        }

        // Get current post ID
        $postId = isset($_GET['post']) ? intval($_GET['post']) : 0;
        if (!$postId) {
            return;
        }

        // Check if post is synced
        $syncedAt = get_post_meta($postId, '_psc_synced_at', true);
        if (!$syncedAt) {
            return;
        }

        // Show notice
        $adminUrl = admin_url('tools.php?page=post-content-sync');
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php _e('Notice: Content Sync Management', 'post-content-sync'); ?></strong><br>
                <?php
                printf(
                    /* translators: %s: Link to Content Sync admin page */
                    __('This post is managed by the %s.', 'post-content-sync'),
                    sprintf(
                        '<a href="%s">%s</a>',
                        esc_url($adminUrl),
                        __('Post Content Sync plugin', 'post-content-sync')
                    )
                );
                ?><br>
                <?php _e('Any changes made here may be overwritten during the next sync. Please edit the source HTML file instead.', 'post-content-sync'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Register WP-CLI commands
     *
     * @return void
     */
    private function registerCliHooks()
    {
        \WP_CLI::add_command(
            'content-sync',
            '\\PostContentSync\\CLI\\SyncCommand'
        );
    }

    /**
     * Initialize plugin components
     *
     * @return void
     */
    private function initializeComponents()
    {
        // Components will be initialized as needed
        // This method can be used for any global initialization

        // Fire action hook for extensions
        do_action('post-content-sync/initialized', $this);
    }

    /**
     * Get plugin configuration
     *
     * @param string|null $key Configuration key (dot notation supported)
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function getConfig($key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        // Support dot notation (e.g., 'parser.prefix')
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Get plugin version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}
