<?php
/***************************************************************

管理画面の設定

 ***************************************************************/

/* --------------------------------
投稿の機能を追加・削除
----------------------------------*/
function my_handle_post_suppurt()
{
    //remove from post
    remove_post_type_support('post', 'comments');
    remove_post_type_support('post', 'trackbacks');
    remove_post_type_support('post', 'post-formats');
    //remove from page
    remove_post_type_support('page', 'comments');
    remove_post_type_support('page', 'trackbacks');

    // add excerpt to page
    add_post_type_support('page', 'excerpt');
}
add_action('init', 'my_handle_post_suppurt');

/* --------------------------------
投稿画面の項目を非表示
----------------------------------*/
function my_remove_default_post_metaboxes()
{
    remove_meta_box('postcustom', 'post', 'normal'); // カスタムフィールド
    remove_meta_box('commentstatusdiv', 'post', 'normal'); // ディスカッション
    remove_meta_box('commentsdiv', 'post', 'normal'); // コメント
    remove_meta_box('trackbacksdiv', 'post', 'normal'); // トラックバック
    remove_meta_box('slugdiv', 'post', 'normal'); // スラッグ
    remove_meta_box('postimagediv', 'post', 'normal'); // スラッグ
    remove_meta_box('tagsdiv-post_tag', 'post', 'side'); // 投稿のタグ
    remove_meta_box('tagsdiv-post_tag', 'post', 'side'); // 投稿のタグ

}
add_action('admin_menu', 'my_remove_default_post_metaboxes');

/* --------------------------------
左メニューのカスタマイズ
----------------------------------*/
function remove_menu()
{
    remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'remove_menu');

/* --------------------------------
Admin bar メニュー カスタマイズ
----------------------------------*/
function remove_bar_menus($wp_admin_bar)
{
    $wp_admin_bar->remove_menu('comments'); // コメント
    $wp_admin_bar->remove_menu('new-content'); // 新規
    $wp_admin_bar->remove_menu('customize'); // カスタマイズ
}
add_action('admin_bar_menu', 'remove_bar_menus', 500);

/*-------------------------------------------
【投稿】のラベルを変更
-------------------------------------------*/

add_action('post_type_labels_post', function ($labels) {
    $post_label = __("News");
    $def_label = __(get_post_type_object('post')->label);
    $def_label_singular = __(get_post_type_object('post')->labels->singular_name);
    foreach ($labels as $key => &$label) {
        $label = str_replace($def_label, $post_label, $label);
        $label = str_replace($def_label_singular, $post_label, $label);
    }
    return $labels;
}, 10, 1);

/* --------------------------------
投稿からカテゴリ・タグの削除
----------------------------------*/
function my_remove_tax_from_post()
{
    global $wp_taxonomies;
    /*
     * 投稿機能から「タグ」を削除
     */
    if (!empty($wp_taxonomies['post_tag']->object_type)) {
        foreach ($wp_taxonomies['post_tag']->object_type as $i => $object_type) {
            if ($object_type == 'post') {
                unset($wp_taxonomies['post_tag']->object_type[$i]);
            }
        }
    }
    /*
     * 投稿機能から「カテゴリ」を削除
     */
    if (!empty($wp_taxonomies['category']->object_type)) {
        foreach ($wp_taxonomies['category']->object_type as $i => $object_type) {
            if ($object_type == 'post') {
                unset($wp_taxonomies['category']->object_type[$i]);
            }
        }
    }
    return true;
};
add_action('init', 'my_remove_tax_from_post');

/* --------------------------------
カテゴリーの順番が変わるの機能を削除
----------------------------------*/
function my_category_terms_checklist_no_top($args, $post_id = null)
{
    $args['checked_ontop'] = false;
    return $args;
}
add_action('wp_terms_checklist_args', 'my_category_terms_checklist_no_top');



/* --------------------------------
Gutenbergを無効化
----------------------------------*/
function disable_block_editor($use_block_editor, $post_type)
{
    if ($post_type === 'page') {
        return false;
    }

    return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'disable_block_editor', 10, 2);

/* --------------------------------
ウィジウィグのボタンを削除
@see http://cly7796.net/wp/cms/delete-button-of-widgwig-at-wordpress/
----------------------------------*/
function remove_tinymce_buttons($buttons)
{
    $remove = array('wp_more', 'dfw', 'alignleft', 'aligncenter', 'alignright', 'bullist', 'numlist', 'spellchecker');
    return array_diff($buttons, $remove);
}
add_filter('mce_buttons', 'remove_tinymce_buttons');


