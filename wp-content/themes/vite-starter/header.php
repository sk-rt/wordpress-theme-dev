<?php
$heading_tag = is_front_page() ? 'h1' : 'p';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
   <?php get_template_part('template-parts/common/head'); ?>
</head>

<body <?php body_class(); ?>>
   <header class="l-header">
      <<?= $heading_tag ?> class="l-header__name u-font-display ">
         <a href="<?php echo home_url(); ?>">
            <?php bloginfo('name') ?>
         </a>
      </<?= $heading_tag ?>>
      <nav class="l-header__nav">
         <ul>
            <li><a
                  href="<?php echo home_url('/about/'); ?>">About</a>
            </li>
            <li><a
                  href="<?php echo get_post_type_archive_link('post'); ?>">News</a>
            </li>
         </ul>
      </nav>
   </header>