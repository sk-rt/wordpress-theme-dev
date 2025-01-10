<?php

namespace Theme\Controllers;

use Theme\Functions\MetaService;

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
    public function head()
    {
        $description = MetaService::getDescription();
        $ogImage = MetaService::getOgImageUrl();
        $canonical = MetaService::getCanonicalUrl();
        ?>
        <meta name="description" content="<?php echo $description; ?>">
        <meta property="og:type"
            content="<?php if (is_front_page()) : ?>website<?php else : ?>article<?php endif; ?>">
        <meta property="og:url" content="<?php echo $canonical; ?>">
        <meta property="og:title"
            content="<?php echo wp_get_document_title(); ?>">
        <meta property="og:description"
            content="<?php echo $description; ?>">
        <meta property="og:image" content="<?php echo $ogImage; ?>">
        <meta property="og:site_name"
            content="<?php bloginfo('name'); ?>">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title"
            content="<?php echo wp_get_document_title(); ?>">
        <meta itemprop="image" content="<?php echo $ogImage; ?>">
        <link rel="icon" type="image/png" sizes="48x48"
            href="<?php echo get_template_directory_uri(); ?>/site-icons/favicon.png">
        <link rel="shortcut icon"
            href="<?php echo get_template_directory_uri(); ?>/site-icons/favicon.png">
        <link rel="canonical" href="<?php echo $canonical; ?>">
<?php
    }
}
?>