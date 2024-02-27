<?php

namespace Theme\Admin;

class AdminMenu
{
    protected static $instance;
    protected function __construct()
    {
        add_filter('admin_menu', [$this, 'removeAdminMenu'], 10, 1);
        add_filter('admin_bar_menu',  [$this, 'removeAdminBarMenu'], 99, 1);
    }
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * メニューから削除
     */
    public function removeAdminMenu($var)
    {
        remove_menu_page('edit-comments.php'); // コメント削除
        $customizer_url = add_query_arg('return', urlencode(remove_query_arg(wp_removable_query_args(), wp_unslash($_SERVER['REQUEST_URI']))), 'customize.php');
        remove_submenu_page('themes.php', $customizer_url); // カスタマイズ
    }
    /**
     * Admin barから削除
     */
    public function removeAdminBarMenu($wp_admin_bar)
    {
        $wp_admin_bar->remove_menu('wp-logo'); // ロゴ
        $wp_admin_bar->remove_menu('comments'); // コメント
    }
}
