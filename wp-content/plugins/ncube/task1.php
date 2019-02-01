<?php
/*

	Task: 1
	----------------

	Please make the function "ncube_related_posts" return an array of 10 random blog
	posts that related to the specified post ($post_id) by its tags

*/
function ncube_related_posts($post_id) // get_the_ID(); inside the Loop
{
    $tags = wp_get_post_terms($post_id, 'post_tag', ['fields' => 'ids']); // get current post tags
    $args = [
        'post__not_in' => array($post_id),
        'posts_per_page' => 10, // idea: pass it as param
        'ignore_sticky_posts' => 1,
        'orderby' => 'rand',
        'tax_query' => [
            [
                'taxonomy' => 'post_tag',
                'terms' => $tags
            ]
        ]
    ];

    /* Just a standard query */
    $related_posts_query = new WP_Query($args);
    echo '<div id="related"><h4>Related Posts</h4>';
    if ($related_posts_query->have_posts()) {

        while ($related_posts_query->have_posts()) {
            $related_posts_query->the_post(); ?>
            <div class="posts">
                <h5><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h5>
                <?php the_excerpt(); ?>
            </div><!--posts-->
        <?php }
        wp_reset_postdata();
        echo '</div><!--related-->';
    }


}