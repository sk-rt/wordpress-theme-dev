<?php
/**
 * Plugin Name: Post Content Sync
 * Plugin URI:
 * Description: Sync static HTML content files to WordPress posts.
 * Version: 1.0.0
 * Author: Ryuta Sakai
 * Author URI:
 * License: MIT
 * Text Domain: post-content-sync
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('POST_CONTENT_SYNC_VERSION', '1.0.0');
define('POST_CONTENT_SYNC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('POST_CONTENT_SYNC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('POST_CONTENT_SYNC_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Require Composer autoloader
if (file_exists(POST_CONTENT_SYNC_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once POST_CONTENT_SYNC_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Show admin notice if autoloader is missing
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>Post Content Sync:</strong> ';
        echo 'Composer autoloader not found. Please run <code>composer install</code> in the plugin directory.';
        echo '</p></div>';
    });
    return;
}

// Initialize the plugin
// Use 'after_setup_theme' to allow theme filters to be registered first
add_action('after_setup_theme', function () {
    try {
        $plugin = new \PostContentSync\Plugin();
        $plugin->initialize();
    } catch (\Exception $e) {
        // Log error and show admin notice
        error_log('Post Content Sync Error: ' . $e->getMessage());

        add_action('admin_notices', function () use ($e) {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>Post Content Sync:</strong> ' . esc_html($e->getMessage());
            echo '</p></div>';
        });
    }
}, 20);

// Activation hook
register_activation_hook(__FILE__, function () {
    // Future: Create database tables, set default options, etc.
    if (!get_option('post_content_sync_version')) {
        add_option('post_content_sync_version', POST_CONTENT_SYNC_VERSION);
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function () {
    // Future: Cleanup tasks if needed
});
