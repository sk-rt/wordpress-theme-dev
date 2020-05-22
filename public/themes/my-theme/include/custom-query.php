<?php
/***************************************************************

Custom Queries

 ***************************************************************/

/* --------------------------------
固定ページ 自動整形削除
----------------------------------*/
function my_remove_wpautop_filter($content)
{
    global $post;
    $remove_filter = false;
    $arr_types = array('page');
    $post_type = get_post_type($post->ID);
    if (in_array($post_type, $arr_types)) {
        $remove_filter = true;
    }
    if ($remove_filter) {
        remove_filter('the_content', 'wpautop');
        remove_filter('the_excerpt', 'wpautop');
    }
    return $content;
}
add_filter('the_content', 'my_remove_wpautop_filter', 9);

/* --------------------------------
固定ページのビジュアルエディタ無効
----------------------------------*/
function my_disable_visual_editor_in_page()
{
    global $typenow;
    if ($typenow == 'page') {
        add_filter('user_can_richedit', 'disable_visual_editor_filter');
    }
}
function disable_visual_editor_filter()
{
    return false;
}
add_action('load-post.php', 'my_disable_visual_editor_in_page');
add_action('load-post-new.php', 'my_disable_visual_editor_in_page');

/* --------------------------------
srcset内のショートコードがそのまま表示されてしまう現象を解決
----------------------------------*/
function my_wp_kses_allowed_html($tags, $context)
{
    $tags['img']['srcset'] = true;
    $tags['source']['srcset'] = true;
    return $tags;
}
add_filter('wp_kses_allowed_html', 'my_wp_kses_allowed_html', 10, 2);

/* --------------------------------
不要なページを404
----------------------------------*/
function my_custom_handle_404()
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
add_action('template_redirect', 'my_custom_handle_404');

/* --------------------------------
抜粋の最後を調整
----------------------------------*/
function my_custom_excerpt_more($more)
{
    return '...';
}
add_filter('excerpt_more', 'my_custom_excerpt_more');

/* --------------------------------
excerpt文字数変更
----------------------------------*/
function my_the_excerpt($postContent)
{
    $postContent = mb_strimwidth($postContent, 0, 200, "…", "UTF-8");
    return $postContent;
}
add_filter('the_excerpt', 'my_the_excerpt');

/***************************************************************

embed youtube フォーマット変更

 ***************************************************************/

function my_custom_youtube_oembed($code)
{
    if (strpos($code, 'youtu.be') !== false || strpos($code, 'youtube.com') !== false || strpos($code, 'vimeo') !== false) {
        $param = "rel=0";
        $html = preg_replace("@src=(['\"])?([^'\">\s]*)@", "src=$1$2&$param", $code);
        $html = '<div class="c-iframe-video"><div class="c-iframe-video__inner">' . $html . '</div></div>';
        return $html;
    }
    return $code;
}

add_filter('embed_handler_html', 'my_custom_youtube_oembed');
add_filter('embed_oembed_html', 'my_custom_youtube_oembed');
