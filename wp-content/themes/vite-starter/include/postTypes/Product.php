<?php

namespace Theme\PostTypes;

class Product
{
    static $postTypeName = 'product';
    protected static $instance;
    protected function __construct()
    {
        $this->register();
    }
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function register()
    {
        $postTypeLabe = '商品';
        /**
         * @see https://developer.wordpress.org/reference/functions/register_post_type/
         * @see https://developer.wordpress.org/reference/functions/get_post_type_labels/
         * @see https://developer.wordpress.org/resource/dashicons/
         */
        register_post_type(
            self::$postTypeName,
            array(
                'label' => $postTypeLabe,
                'labels' => [
                    'name' => $postTypeLabe . '',
                    'all_items' => $postTypeLabe . '一覧',
                    'add_new' => $postTypeLabe . 'を追加',
                    'add_new_item' => $postTypeLabe . 'を追加',
                    'edit_item' => $postTypeLabe . 'の編集',
                ],
                'public' => true,
                'show_ui' => true,
                'hierarchical' => false,
                'publicly_queryable' => true,
                'menu_icon' => 'dashicons-media-document',
                'has_archive' => true,
                'supports' => ['title', 'thumbnail', 'editor', 'author', 'revisions'],
                'menu_position' => 5,
                'show_in_rest' => false,
                'rewrite' => ['with_front' => false],
            )
        );
        register_taxonomy(
            'genre',
            self::$postTypeName,
            [
                'label' => 'ジャンル',
                'public' => true,
                'show_ui' => true,
                'show_in_quick_edit' => true,
                'show_admin_column' => true,
                'description' => 'ジャンル',
                'hierarchical' => true,
                'rewrite' => array(
                    'with_front' => true,
                    'hierarchical' => false,
                ),
            ]
        );
    }
}
