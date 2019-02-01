<?php
/*

	Task: 2
	----------------

	Blog posts have next meta fields:
	"post_likes_count" = the number of likes of the post
	"post_views_count" = the number of views of the post

	Please make the function "ncube_get_posts" return an array of all published blog posts
	- considering pagination
	- by specified categories ($post_categories)
	- sticky or not sticky posts ($sticky_posts)
	- order posts by:
		- likes (DESC)
		- views (DESC)
		- publication date (DESC)

*/

function ncube_get_posts($post_categories = [], $sticky_posts = false)
// '$sticky_posts = false' will exclude sticky posts completely from result
{
    $sticky_posts ? null : $sticky = get_option('sticky_posts');
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = [
        'post_status' => 'publish',
        'post__not_in' => $sticky,
        'posts_per_page' => 10, // idea: pass it as param
        'paged' => $paged,
        'ignore_sticky_posts' => $sticky_posts,
        'cat' => $post_categories,
        'meta_query' => array(
            'relation' => 'AND',
            'post_likes_count_clause' => array(
                'key' => 'post_likes_count',
            ),
            'post_views_count_clause' => array(
                'key' => 'post_views_count',
            ),
        ),
        'orderby' => array(
            'post_likes_count_clause' => 'DESC',
            'post_views_count_clause' => 'DESC',
            'date' => 'DESC',
        ),
    ];

    /* Just a standard query */
    $get_posts_query = new WP_Query($args);
    echo '<div id="related"><h4>Top Posts</h4>';
    if ($get_posts_query->have_posts()) {

        while ($get_posts_query->have_posts()) {
            $get_posts_query->the_post(); ?>
            <div class="posts">
                <h5><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h5>
                <?php the_excerpt(); ?>
            </div><!--posts-->
        <?php } ?>


        <?php
        /* Just a standard pagination */
        echo '<div class="pagination">';
        echo paginate_links(array(
            'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'total' => $get_posts_query->max_num_pages,
            'current' => max(1, get_query_var('paged')),
            'format' => '?paged=%#%',
            'show_all' => false,
            'type' => 'plain',
            'end_size' => 2,
            'mid_size' => 1,
            'prev_next' => true,
            'prev_text' => sprintf('<i></i> %1$s', __('Newer Posts', 'text-domain')),
            'next_text' => sprintf('%1$s <i></i>', __('Older Posts', 'text-domain')),
            'add_args' => false,
            'add_fragment' => '',
        ));
        echo '</div>';
        ?>

        <?php wp_reset_postdata();
        echo '</div><!--related-->';
    }


}
