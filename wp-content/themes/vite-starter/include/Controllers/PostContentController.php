<?php

namespace Theme\Editor;

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
         * [template temp="temp-path"]
         */
        add_shortcode('template', function ($atts) {
            $atts = shortcode_atts(
                [
                    'temp' => '',
                ],
                $atts
            );
            $temp_path = 'template-parts/' . esc_attr($atts['temp']);
            return TemplateTags::getTemplatePartString($temp_path);
        });
    }
}
