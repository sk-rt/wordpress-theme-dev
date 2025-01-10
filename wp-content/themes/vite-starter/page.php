<?php get_header(); ?>
<?php if (have_posts()) :  while (have_posts()) : the_post(); ?>
        <main class="l-page-main">
            <div class="l-page-header u-gutter--lg">
                <h1 class="l-page-header__title">
                    <?php the_title(); ?>
                </h1>
            </div>
            <div class="l-page-content u-gutter--lg">
                <?php the_content() ?>
            </div>
        </main>
<?php endwhile;
endif; ?>
<?php get_footer(); ?>