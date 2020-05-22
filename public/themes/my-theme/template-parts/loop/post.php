
<a id="post-<?php the_ID();?>"
    href="<?php the_permalink();?>"
    <?php post_class('c-postlist-item');?> 
    data-news-modal="<?php the_permalink();?>">
   
    <div class="c-postlist-item__inner">
        <div class="c-postlist-item__meta">
            <time class="c-postlist-meta__date" datetime="<?php the_time('c');?>" >
                <?php echo esc_html(get_the_date()); ?>
            </time>
        </div>
        <div class="c-postlist-item__content">
            <h4 class="c-postlist-item__heading">
                <?php the_title();?>
            </h4>
            
        </div>
    </div>
</a>
