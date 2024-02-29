<?php

namespace Theme\Controllers;

/***************************************************************

Frontend Assets

 ***************************************************************/

class AssetsController
{
    private const PRODUCTION_HANDLE = 'app';
    private const DEVELOPMENT_HANDLE = 'vite-dev';
    private $is_development;
    private static $instance;
    private function __construct()
    {
        $this->is_development = defined('VITE_IS_DEVELOPMENT') && VITE_IS_DEVELOPMENT === true && defined('VITE_ENDPOINT');

        if ($this->is_development) {
            add_action('wp_enqueue_scripts', [$this, 'enqueueDevScript'], 10);
        } else {
            add_action('wp_enqueue_scripts', [$this, 'enqueueProdScript'], 10);
        }
        add_action('wp_enqueue_scripts', [$this, 'dequeueScript'], 10);
        add_action('script_loader_tag', [$this, 'customizeScriptTag'], 10, 2);
    }
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function dequeueScript()
    {
        if (!is_user_logged_in()) {
            wp_deregister_script('jquery');
        }
    }
    public function enqueueDevScript()
    {
        wp_enqueue_script(self::DEVELOPMENT_HANDLE, VITE_ENDPOINT, [], null, false);
    }
    public function enqueueProdScript()
    {
        $theme_url = get_stylesheet_directory_uri();
        $manifest = json_decode(@file_get_contents(get_template_directory() . '/.vite/manifest.json'), true);
        if (!is_array($manifest)) {
            return;
        }
        foreach ($manifest as $value) {
            if (isset($value['file'])) {
                wp_enqueue_script(self::PRODUCTION_HANDLE, $theme_url . '/' . $value['file'], [], null, false);
            }
            if (isset($value['css']) && is_array($value['css'])) {
                foreach ($value['css'] as $file) {
                    wp_enqueue_style(self::PRODUCTION_HANDLE, $theme_url . '/' . $file, [], null, false);
                }
            }
        }
    }
    /**
     * Add `defer` attribute in script tag
     */
    public function customizeScriptTag($tag, $handle)
    {
        if ($handle === self::PRODUCTION_HANDLE) {
            return str_replace('type="text/javascript"', 'defer', $tag);
        }
        if ($handle === self::DEVELOPMENT_HANDLE) {
            return str_replace('type="text/javascript"', 'type="module" crossorigin', $tag);
        }
        return $tag;
    }
}
