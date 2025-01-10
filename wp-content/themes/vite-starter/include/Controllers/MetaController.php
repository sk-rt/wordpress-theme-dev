<?php

namespace Theme\Controllers;

class MetaController
{
    protected static $instance;
    protected function __construct()
    {
        add_action('wp_head', [$this, 'head']);
    }
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * Headに追加
     */
    function head()
    {
        $description = $this->getPageDescription();
        $ogImage = $this->getOgImageUrl();
        $canonical = $this->getCanonicalUrl();
?>
        <meta name="description" content="<?php echo $description; ?>">
        <meta property="og:type" content="<?php if (is_front_page()) : ?>website<?php else : ?>article<?php endif; ?>">
        <meta property="og:url" content="<?php echo $canonical; ?>">
        <meta property="og:title" content="<?php echo wp_get_document_title(); ?>">
        <meta property="og:description" content="<?php echo $description; ?>">
        <meta property="og:image" content="<?php echo $ogImage; ?>">
        <meta property="og:site_name" content="<?php bloginfo('name'); ?>">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo wp_get_document_title(); ?>">
        <meta itemprop="image" content="<?php echo $ogImage; ?>">
        <link rel="icon" type="image/png" sizes="48x48" href="<?php echo get_template_directory_uri(); ?>/site-icons/favicon.png">
        <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/site-icons/favicon.png">
        <link rel="canonical" href="<?php echo $canonical; ?>">
<?php
    }
    /**
     * Description 取得
     */
    function getPageDescription()
    {
        if (is_front_page()) {
            return get_bloginfo('description');
        }
        if (is_single() || is_page()) {
            global $post;
            setup_postdata($post);
            if ($excerpt = get_the_excerpt()) {
                return $excerpt;
            } else {
                return get_the_title() . '｜' . get_bloginfo('description');
            }
            wp_reset_postdata();
        } else {
            return get_bloginfo('description');
        }
    }
    /**
     * OGP画像取得
     */
    function getOgImageUrl()
    {
        $def_image = get_template_directory_uri() . '/site-icons/ogp.png';
        if (is_single() || is_page()) {
            global $post;
            $postid = $post->ID;
            if ($image_id = get_post_thumbnail_id($postid)) {
                $image = wp_get_attachment_image_src($image_id, 'large', true);
                return $image[0];
            }
        }
        return $def_image;
    }
    /**
     * Cannonical URL取得
     */
    function getCanonicalUrl()
    {
        return esc_html((empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    }
}
