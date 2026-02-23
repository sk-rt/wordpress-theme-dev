<?php

namespace Theme\Controllers;

use Theme\Functions\TemplateTags;

class PostContentController
{
    protected static $instance;
    protected function __construct()
    {
        $this->defineShortCode();
    }
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * ショートコードの定義
     */
    public function defineShortCode()
    {
        /**
         * [themeUrl]
         */
        add_shortcode('themeUrl', function () {
            return get_template_directory_uri() . '/';
        });
        /**
         * [homeUrl]
         */
        add_shortcode('homeUrl', function () {
            return home_url('/');
        });

        /**
         * WPの `get_template_part()` ショートコード
         * [templates path="temp-path"]
         */
        add_shortcode('templates', function ($atts) {
            $path = shortcode_atts(
                [
                    'path' => '',
                ],
                $atts
            )['path'];
            $temp_path = 'template-parts/' . esc_attr($path);
            $args = $atts;
            unset($args['path']);
            return TemplateTags::getTemplatePartString($temp_path, $args);
        });
    }
}
