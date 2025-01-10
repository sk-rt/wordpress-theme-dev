<?php

use Theme\Functions\TemplateTags;
?>

<div class="p-top-main">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <section class="p-top-content">
                <?php the_content() ?>
            </section>
        <?php endwhile; ?>
    <?php endif; ?>
    <?php

    /* News */
    $news_arg = array(
        'post_type' => 'post',
        'posts_per_page' => 4,
    );
    $news_list = get_posts($news_arg);

    ?>
    <?php if ($news_list) : ?>
        <section id="recentNews" class="p-top-news u-gutter--lg">
            <div class="p-top-news__inner ">
                <div class="p-top-news__header">
                    <h2 class="p-top-news__title">
                        News
                    </h2>
                    <a href="<?php echo get_post_type_archive_link('post'); ?>" class="p-top-news__link">More</a>
                </div>

                <div class="p-top-news__list">
                    <?php foreach ($news_list as $post) : setup_postdata($post);
                        TemplateTags::includeComponent("loop/post");
                    endforeach;
                    wp_reset_postdata(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>