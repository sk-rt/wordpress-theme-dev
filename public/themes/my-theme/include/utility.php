<?php
/***************************************************************

utility

 ***************************************************************/

/* --------------------------------
カレントURL取得
----------------------------------*/
function util_get_canonical_url()
{
    return esc_html((empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
}

/* --------------------------------
ブログトップ URL取得
----------------------------------*/
function util_get_blog_home_url()
{
    if (get_option('page_for_posts')) {
        $blog_index = get_post(get_option('page_for_posts'));
        return get_permalink($blog_index->ID);
    } else {
        return home_url();
    }
}

/* --------------------------------
投稿・固定ページ用ショートコード
[tempUrl][homeUrl][rootUrl]
----------------------------------*/

add_shortcode('tempUrl', function () {
    return get_template_directory_uri() . '/';
});

add_shortcode('homeUrl', function () {
    return home_url('/');
});

function util_images_path()
{
    $pass = get_template_directory_uri() . "/images/";
    return $pass;
}
add_shortcode('imgUrl', 'util_images_path');

/* --------------------------------
OGP画像取得
----------------------------------*/
function util_get_featured_image_url()
{
    $image_url = '';
    $def_image = get_template_directory_uri() . '/site-icons/ogp.png';
    if (is_single() || is_page()) {
        global $post;
        $postid = $post->ID;
        if (get_the_post_thumbnail($postid)) {
            $image_id = get_post_thumbnail_id($postid);
            $image_url = wp_get_attachment_image_src($image_id, 'large', true);
            $image_url = $image_url[0];
        } else {
            $image_url = $def_image;
        }
    } else {
        $image_url = $def_image;
    }
    ;
    return $image_url;
}
/* --------------------------------
Share文言 取得
----------------------------------*/
function util_get_share_text()
{
    if (is_single() || is_page()) {
        return wp_title('｜', false, 'right') . get_bloginfo('name');
    } else {
        return wp_title('｜', false, 'right') . get_bloginfo('name');
    }
}
/* --------------------------------
Description 取得
----------------------------------*/
function util_get_description()
{
    if (is_single() || is_page()) {
        global $post;
        setup_postdata($post);
        if ($excerpt = get_the_excerpt()) {
            return $excerpt;
        } else {
            return get_the_title() . "｜" . get_bloginfo('description');
        }
        wp_reset_postdata();
    }  else {
        return get_bloginfo('description');
    }
}

/* --------------------------------
テンプレートパーツ 取得
----------------------------------*/
/**
 * WPの `get_template_part()` を出力せずにhtmlで返す
 * @param $temp_path string テンプレートパス
 * @return html
 */
function my_get_template_part($temp_path)
{
    ob_start();
    $view = get_template_part($temp_path);
    $view = ob_get_contents();
    ob_end_clean();
    return $view;
}
/**
 * WPの `get_template_part()` ショートコード
 * [template temp="temp-path"]
 */
function my_get_template_part_sc($atts)
{
    $atts = shortcode_atts(
        array(
            'temp' => '',
        ),
        $atts
    );
    $temp_path = 'template-parts/' . esc_attr($atts['temp']);
    $view = my_get_template_part($temp_path);
    return $view;
}
add_shortcode('template', 'my_get_template_part_sc');

/* --------------------------------
ナビ 配列
----------------------------------*/
function get_navi_arr()
{
    return array(
        array(
            'name' => 'about-us',
            'permalink' => home_url("about-us/"),
            'label' => __('About us', 'andes-collection'),
            'label-ja' => 'コレクション概要',
            'nav-slug' => '/about-us',
            'disabled' => false,
        ),
        array(
            'name' => 'abstract',
            'permalink' => home_url("abstract/"),
            'label' => __('Abstract', 'andes-collection'),
            'label-ja' => 'アンデス文明概要',
            'nav-slug' => '/abstract',
            'disabled' => false,
        ),
        array(
            'name' => 'database',
            'permalink' => home_url("database/"),
            'label' => __('Database', 'andes-collection'),
            'label-ja' => '資料データ',
            'nav-slug' => '/database',
            'disabled' => false,
        ),
        array(
            'name' => 'news',
            'permalink' => home_url("news/"),
            'label' => __('News', 'andes-collection'),
            'label-ja' => '最新情報',
            'nav-slug' => '/news',
            'disabled' => false,
        ),
        array(
            'name' => 'contact',
            'permalink' => home_url("contact/"),
            'label' => __('Contact', 'andes-collection'),
            'label-ja' => 'お問い合わせ',
            'nav-slug' => '/contact',
            'disabled' => false,
        ),
    );
}
