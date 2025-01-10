<?php

namespace EditorSetting\classes;

class DisabledBlockEditor
{
    private static $instance = null;
    private $ignore_post_types = [];

    private function __construct($ignore_post_types = [])
    {
        $this->ignore_post_types = $ignore_post_types;
        if (!is_admin()) {
            $this->removeGutenbergHooks();
        }
        add_action('wp_enqueue_scripts', [$this, 'dequeueGutenbergAssets'], 10);
        add_filter('use_block_editor_for_post_type', [$this, 'setIgnorePostTypes'], 100, 2);
    }
    public static function getInstance($ignore_post_types = []): self
    {
        $class = get_called_class();
        if (!isset(self::$instance)) {
            self::$instance = new $class($ignore_post_types);
        }

        return self::$instance;
    }
    public function setIgnorePostTypes($use_block_editor, string $post_type)
    {

        return in_array($post_type, $this->ignore_post_types);
    }
    /**
     * Remove Gutenberg Hooks
     * @see https://github.com/WordPress/classic-editor/blob/6868c9c9eed5397c49046e3f0d13da1cd3f0f1d7/classic-editor.php#L137-L200
     */
    public function removeGutenbergHooks()
    {
        // \error_log(var_export(get_post_type()) . "\n", 3, \get_template_directory() . '/log/debug.log');

        remove_action('admin_menu', 'gutenberg_menu');
        remove_action('admin_init', 'gutenberg_redirect_demo');
        // Gutenberg 5.3+
        remove_action('wp_enqueue_scripts', 'gutenberg_register_scripts_and_styles');
        remove_action('admin_enqueue_scripts', 'gutenberg_register_scripts_and_styles');
        remove_action('admin_notices', 'gutenberg_wordpress_version_notice');
        remove_action('rest_api_init', 'gutenberg_register_rest_widget_updater_routes');
        remove_action('admin_print_styles', 'gutenberg_block_editor_admin_print_styles');
        remove_action('admin_print_scripts', 'gutenberg_block_editor_admin_print_scripts');
        remove_action('admin_print_footer_scripts', 'GUTENBERG_BLOCK_EDITOR_ADMIN_PRINT_FOOTER_SCRIPTS');
        remove_action('admin_footer', 'gutenberg_block_editor_admin_footer');
        remove_action('admin_enqueue_scripts', 'gutenberg_widgets_init');
        remove_action('admin_notices', 'gutenberg_build_files_notice');

        remove_filter('load_script_translation_file', 'gutenberg_override_translation_file');
        remove_filter('block_editor_settings', 'gutenberg_extend_block_editor_styles');
        remove_filter('default_content', 'gutenberg_default_demo_content');
        remove_filter('default_title', 'gutenberg_default_demo_title');
        remove_filter('block_editor_settings', 'gutenberg_legacy_widget_settings');
        remove_filter('rest_request_after_callbacks', 'gutenberg_filter_oembed_result');

        // Previously used, compat for older Gutenberg versions.
        remove_filter('wp_refresh_nonces', 'gutenberg_add_rest_nonce_to_heartbeat_response_headers');
        remove_filter('get_edit_post_link', 'gutenberg_revisions_link_to_editor');
        remove_filter('wp_prepare_revision_for_js', 'gutenberg_revisions_restore');

        remove_action('rest_api_init', 'gutenberg_register_rest_routes');
        remove_action('rest_api_init', 'gutenberg_add_taxonomy_visibility_field');
        remove_filter('registered_post_type', 'gutenberg_register_post_prepare_functions');

        remove_action('do_meta_boxes', 'gutenberg_meta_box_save');
        remove_action('submitpost_box', 'gutenberg_intercept_meta_box_render');
        remove_action('submitpage_box', 'gutenberg_intercept_meta_box_render');
        remove_action('edit_page_form', 'gutenberg_intercept_meta_box_render');
        remove_action('edit_form_advanced', 'gutenberg_intercept_meta_box_render');
        remove_filter('redirect_post_location', 'gutenberg_meta_box_save_redirect');
        remove_filter('filter_gutenberg_meta_boxes', 'gutenberg_filter_meta_boxes');

        remove_filter('body_class', 'gutenberg_add_responsive_body_class');
        remove_filter('admin_url', 'gutenberg_modify_add_new_button_url'); // old
        remove_action('admin_enqueue_scripts', 'gutenberg_check_if_classic_needs_warning_about_blocks');
        remove_filter('register_post_type_args', 'gutenberg_filter_post_type_labels');
    }
    /**
     * Remove block editor styles
     */
    public function dequeueGutenbergAssets()
    {
        if (is_admin()) {
            return;
        }
        if (is_singular($this->ignore_post_types)) {
            return;
        }
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('global-styles');
    }
}
