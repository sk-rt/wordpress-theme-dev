<?php

namespace Theme\Functions;

use Theme\PostTypes\Post;
use Theme\PostTypes\Product;

class MetaService
{
    public const TITLE_SEP = ' | ';

    
    /**
     * canonical URL取得
     */
    public static function getCanonicalUrl()
    {
        if (is_singular()) {
            global $post;
            return \trailingslashit(\wp_get_canonical_url($post));
        }
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return \trailingslashit((empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $path);
    }
    /**
     * カレントページか判定
     */
    public static function isCurrentPage(string $regex)
    {
        if (!$regex) {
            return false;
        }
        $current_url = self::getCanonicalUrl();
        $current_path = str_replace(home_url(), '', $current_url);
        if (preg_match($regex, $current_path)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Description 取得
     */
    public static function getDescription()
    {
        if (is_front_page()) {
            return get_bloginfo('description');
        }
        // Product archive
        if (is_post_type_archive(Product::$postTypeName)) {
            return Product::$postTypeLabel . self::TITLE_SEP . get_bloginfo('description');
        }
        if (is_home() || is_archive()) {
            return Post::$postTypeLabel . self::TITLE_SEP . get_bloginfo('description');
        }
       
        if (is_single() || is_page()) {
            global $post;
            setup_postdata($post);
            if ($excerpt = get_the_excerpt()) {
                return $excerpt;
            } else {
                return get_the_title() . self::TITLE_SEP . get_bloginfo('description');
            }
            wp_reset_postdata();
        }
        return get_bloginfo('description');
    }
    /**
     * OGImage取得
     */
    public static function getOgImageUrl()
    {
        $def_image = get_template_directory_uri() . '/site-icons/ogp.png';
        if (is_single() || is_page()) {
            global $post;
            $postid = $post->ID;
            if (get_the_post_thumbnail($postid)) {
                $image_id = get_post_thumbnail_id($postid);
                $image_src = wp_get_attachment_image_src($image_id, 'large', true);
                return $image_src[0];
            }
        }
        return $def_image;
    }
}
