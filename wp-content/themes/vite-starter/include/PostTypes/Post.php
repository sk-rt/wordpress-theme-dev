<?php

namespace Theme\PostTypes;

class Post
{
    public static $postTypeName = 'post';
    public static $postTypeLabel = 'News';
    protected static $instance;
    protected function __construct()
    {
        add_action('post_type_labels_post', [$this, 'changePostLabel'], 10, 1);
        add_action('init', [$this, 'removeDefaultTax']);
        require_once __DIR__ . '/CustomFields/postMeta.php';
    }
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * 投稿からカテゴリ・タグの削除
     */
    public function removeDefaultTax()
    {
        global $wp_taxonomies;

        if (!empty($wp_taxonomies['post_tag']->object_type)) {
            foreach ($wp_taxonomies['post_tag']->object_type as $i => $object_type) {
                if ($object_type == 'post') {
                    unset($wp_taxonomies['post_tag']->object_type[$i]);
                }
            }
        }

        if (!empty($wp_taxonomies['category']->object_type)) {
            foreach ($wp_taxonomies['category']->object_type as $i => $object_type) {
                if ($object_type == 'post') {
                    unset($wp_taxonomies['category']->object_type[$i]);
                }
            }
        }
        return true;
    }
    /**
     * `投稿` のラベルを変更
     */
    public function changePostLabel($labels)
    {
        $initial_label = __(get_post_type_object('post')->label);
        $initial_label_singular = __(get_post_type_object('post')->labels->singular_name);
        foreach ($labels as $key => &$label) {
            if (!$label) {
                continue;
            }
            $label = str_replace($initial_label, self::$postTypeLabel, $label);
            $label = str_replace($initial_label_singular, self::$postTypeLabel, $label);
        }
        return $labels;
    }
}
