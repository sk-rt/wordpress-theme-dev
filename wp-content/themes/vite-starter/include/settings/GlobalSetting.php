<?php

namespace Theme\Settings;

/***************************************************************

General Setting

 ***************************************************************/

class GlobalSettings
{
    private static $instance;
    private function __construct()
    {

        add_action('after_setup_theme', [$this, 'addFeatureSupports'], 10);
        add_action('after_setup_theme', [$this, 'addMediaThumbnails'], 10);
        add_action('after_setup_theme', [$this, 'cleanUpWpHead'], 10);
    }
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function addFeatureSupports()
    {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
    }

    public function addMediaThumbnails()
    {
        remove_image_size('1536x1536');
        remove_image_size('2048x2048');

        // custom thumbnails
        add_image_size('thumb-for-admin-auto', 100, 100, false);
    }
    public function cleanUpWpHead()
    {
        remove_action('wp_head', 'wp_generator'); // generator
        remove_action('wp_head', 'wlwmanifest_link'); //wlwmanifest.xml
        remove_action('wp_head', 'rsd_link'); //RPC用XML
        remove_action('wp_head', 'feed_links', 2); // 投稿フィード、コメントフィードを消去
        remove_action('wp_head', 'feed_links_extra', 3); // その他フィードを消去
        remove_action('wp_head', 'wp_shortlink_wp_head'); // shortlink
        remove_action('wp_head', 'rest_output_link_wp_head'); // rest api
        remove_action('wp_head', 'rel_canonical'); // canonical


        // 絵文字機能削除
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    }
}
