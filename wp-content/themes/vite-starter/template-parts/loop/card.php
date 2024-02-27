<article id="post-<?php the_ID(); ?>" class="c-card-item">
    <a href="<?php the_permalink(); ?>" class="c-card-item__container">
        <div class="c-card-item__thumb">
            <div class="c-card-item__thumb__inner">
                <?php the_post_thumbnail('post-thumbnail-lg', ['alt' => get_the_title(), 'loading' => 'lazy']); ?>
            </div>
        </div>
        <div class="c-card-item__content">
            <h3 class="c-card-item__heading">
                <?php the_title(); ?>
            </h3>
            <p class="c-card-item__description">
                <?php echo get_the_excerpt(); ?>
            </p>
            <p class="c-card-item__terms">
                <?php
                $terms = get_the_terms($post->ID, 'category');
                if ($terms && !is_wp_error($terms)) :
                    foreach ($terms as $term) :
                ?>
                        <span class="c-card-item__term">
                            <?php echo $term->name; ?></span>
                <?php endforeach;
                endif; ?>
            </p>
        </div>
    </a>
</article>