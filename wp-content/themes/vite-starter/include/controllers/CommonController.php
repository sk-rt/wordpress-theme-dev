<?php

namespace Theme\Controllers;

class CommonController
{
    protected static $instance;
    protected function __construct()
    {
        add_action('pre_get_posts', [$this, 'overrideMainQuery']);
        add_action('template_redirect', [$this, 'invalidatePage']);
    }
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * メインクエリの変更
     */
    function overrideMainQuery($query)
    {
        if (is_admin() && !$query->is_main_query()) {
            return;
        }
        // customize MainQuery
    }
    /**
     * 不要なページを404
     */
    function invalidatePage()
    {
        global $wp_query;
        if (
            is_author()
            || is_attachment()
        ) {
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
        }
    }
}
