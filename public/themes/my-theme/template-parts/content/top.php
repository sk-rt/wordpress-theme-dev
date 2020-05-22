

<div class="p-top-main">
    <?php if (have_posts()) :  while ( have_posts() ) : the_post(); ?>
    <section class="p-top-content">
        <?php the_content() ?>
    </section>
    <?php endwhile; endif; ?>


    <?php

    /* News */
    $news_arg = array(
        'post_type' => 'post',
        'posts_per_page' => 4,
    );
    $news_list = get_posts($news_arg);

    ?>
    <?php if ($news_list): ?>
    <section id="topNews" class="p-top-news u-gutter--lg">
        <div class="p-top-news__inner ">
            <div class="p-top-news__header">
                <h2 class="p-top-news__title c-heading-featured">
                News
                </h2>
                <a href="<?php echo home_url() ?>/news/" class="p-top-news__link u-font-featured"><span>More</span><i class="c-icon-angle--right"></i></a>
            </div>
                
            <div class="p-top-news__list">
                <?php foreach ($news_list as $post): setup_postdata($post);
                    get_template_part("template-parts/loop/post"); 
                endforeach;
                wp_reset_postdata();?>
            </div>
        </div>

    </section>
    <?php endif;?>
    <?php

    /* 資料データ */
    $db_arg = array(
        'post_type' => 'database',
        'posts_per_page' => 16,
    );
    $db_list = get_posts($db_arg);

    ?>
    <?php if ($db_list): ?>
    <section id="topDatabase" class="p-top-database">
        <div class="u-only--sp p-top-database__header">
            <h2 class="p-top-database__title c-heading-featured u-color-main">
                        Database
            </h2>
        </div>
    <?php get_template_part('template-parts/content/database-header'); ?>
    <div class="p-top-database__inner u-gutter--lg">
        <div class="p-top-database__list u-container--xl">
            <div class="c-postcard">
                <?php foreach ($db_list as $post): setup_postdata($post);
                    get_template_part('template-parts/loop/database' );
                endforeach;?>
            </div>
            <div class="p-top-database__footer u-align--right">
            <a href="<?php echo home_url() ?>/database/" class="c-button is-lg">
                資料データ一覧へ
                <span class="c-button__icon is-search" aria-hidden="true">
                    <i class="c-icon-angle--right"></i>
                </span>
            </a>
        </div>
        </div>
        
    </div>
    </section>
    <?php endif;?>
</div>