<?php
/**
 * @package Ncube
 * @version 0.1
 */
/*
Plugin Name: Ncube
Plugin URI: http://wordpress.org/
Description: Lorem ipsum
Author: Iurii Smuglov
Version: 0.1
*/

/*


	General notes
	----------------

	Please:
	- try to use WordPress functions
	- don't forget to write the comments to your code
	- don't worry if you can't do all the tasks, send it as it is

	Thank you for taking your time to complete this test!


*/

wp_enqueue_script('jquery');
/*

	Task: 1
	----------------

	Please make the function "ncube_related_posts" return an array of 10 random blog
	posts that related to the specified post ($post_id) by its tags

*/
function ncube_related_posts($post_id) // get_the_ID(); inside the Loop
{
    $tags = wp_get_post_terms($post_id, 'post_tag', ['fields' => 'ids']); // get current ost tags
    $args = [
        'post__not_in' => array($post_id),
        'posts_per_page' => 10, // idea: pass it as param
        'ignore_sticky_posts' => 1,
        'orderby' => 'rand', // randomize results
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
        'posts_per_page' => 2, // idea: pass it as param
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


/*

	Task: 3
	----------------

	Please create custom post type "reviews_post_type" which supports only:
	- title
	- editor
	- thumbnail


	Please create "page_template" for Reviews frontend-page.

	On this page print:
	- last 10 reviews
	- button "Load more"
	- form to add new review with the next fields:
		- email (required)
		- name (required)
		- review (required)
		- load avatar

	The form for adding new review should send by ajax request an email to the "admin_email" with all data from the form.
	Button "Load more" should load next 10 reviews by ajax request.


*/

function register_post_types()
{
    register_post_type('reviews_post_type', array(
        'labels' => array(
            'name' => 'Reviews',
            'singular_name' => 'Reviews',
            'add_new' => 'Add new',
            'add_new_item' => 'Add new review',
            'edit_item' => 'Edit review',
            'new_item' => 'New review',
            'view_item' => 'View review',
            'search_items' => 'Search review',
            'not_found' => 'Reviews not found',
            'not_found_in_trash' => 'Reviews not found in trash',
            'parent_item_colon' => '',
            'menu_name' => 'Reviews'

        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'thumbnail')
    ));
}

add_action('init', 'register_post_types');

function get_reviews_post_type_callback()
{
    $paged = $_POST['page'];
    $args = array(
        'post_type' => 'reviews_post_type',
        'posts_per_page' => 3,
        'paged' => $paged,
        'post_status' => 'publish'
    );


    $loop = new WP_Query($args);
    while ($loop->have_posts()) : $loop->the_post();
        get_template_part('template-parts/content', 'review_post_type');
        wp_reset_postdata();

    endwhile;
    wp_die();
}

if (wp_doing_ajax()) {
    add_action('wp_ajax_get_reviews_post_type', 'get_reviews_post_type_callback');
    add_action('wp_ajax_nopriv_get_reviews_post_type', 'get_reviews_post_type_callback');
}


function define_ajaxurl()
{

    echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

add_action('wp_head', 'define_ajaxurl');

function get_reviews_post_type_javascript()
{
    ?>
    <script type="text/javascript">
        let page = 2;
        jQuery(document).ready(function ($) {
            $('#loadmore').click(function () {
                var data = {
                    action: 'get_reviews_post_type',
                    'page': page
                };
                $.post(ajaxurl, data, function (res) {
                    $('#loadmore').prev().after(res); // loadmore_button() places this button in the right place
                    //TODO: disable button on last page
                    page++
                });
                return false;
            });
        });
    </script>
    <?php
}

add_action('wp_footer', 'get_reviews_post_type_javascript', 99);

/*
    Now we can create new page_template as file, but its quite hard to make it fit all themes.
    My method is just to inject code after the loop (or other place), so it can fit  most themes without problems with their structure.
 */

function loadmore_button()
{
    if (is_post_type_archive('reviews_post_type')) {
        echo '<button id="loadmore">Load more</button>';
    }
}

add_action('loop_end', 'loadmore_button');

/*
    This on is more theme-specific so I decided to put it into sidebar for now. But we can create custom action or just manually place it in the right place/template
*/

function add_review_form()
{
    if (is_post_type_archive('reviews_post_type')) {
        echo '
        <form class="form" id="ajax-contact-form" action="#" method="post" enctype=”multipart/form-data”>                            
        <input type="email" name="email" id="email" placeholder="Your Email" required="required">
        <input type="name" name="name" id="name" placeholder="Your Name" required="required">
        <input type="review" name="review" id="review" placeholder="Your review" required="required">
        <input type="file" name="avatar" id="avatar" accept="image/x-png,image/gif,image/jpeg" />
        <button type="button" class="btn">Submit</button>
</form>
<p class="report-a-bug-response"></p>

      ';
    }
}

add_action('loop_end', 'add_review_form');

function send_review_javascript()
{
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {

            $('#ajax-contact-form .btn').click(function (e) {


                var form_data = new FormData();
                form_data.append('action', 'send_bug_report');
                form_data.append('email', $("#ajax-contact-form #email").val());
                form_data.append('name', $("#ajax-contact-form #name").val());
                form_data.append('review', $("#ajax-contact-form #review").val());
                form_data.append('file_attach', $("#ajax-contact-form #avatar").prop('files')[0]);

                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    contentType: false,
                    processData: false,
                    data: form_data,
                    success: function (response) {
                        $('.report-a-bug-response').html(response.data);
                    }
                });
            });
        });
    </script>
    <?php
}

add_action('wp_footer', 'send_review_javascript', 99);

function send_bug_report()
{
    $data = $_POST;
    $to = get_option('admin_email');
    $subject = 'New review';

    $body = $data['review'] . "\r\n";

    $upload = wp_upload_bits($_FILES["file_attach"]["name"], null, file_get_contents($_FILES["file_attach"]["tmp_name"]));
    if ($upload['error'])
        $body .= 'File upload error: ' . $upload['error'];
    else
        $body .= 'Avatar URL: ' . $upload['url'];

    $mail = wp_mail($to, $subject, $body);

    if ($mail) {
        wp_send_json_success('Thanks for reporting!');
    } else {
        wp_send_json_success('An error occur');
    }

}

add_action('wp_ajax_nopriv_send_bug_report', 'send_bug_report');
add_action('wp_ajax_send_bug_report', 'send_bug_report');

/*

	Task: 4
	----------------

	Please create custom post type "portfolio_post_type" which supports only:
	- title
	- editor
	- thumbnail

	Add custom meta boxes to the custom post type "portfolio_post_type":
	- object_location ( the address of the portfolio object )
	- object_area ( the area of the portfolio object in square meters )
	- images ( set of images with an ability to add/remove gallery images to it on the post edit page )

	Please create "Options" page in admin area.
	Add option "portfolio_objects_per_page" to this page.

	Please create "page_template" for Portfolio frontend-page.
	On this page print:
	- last N portfolio objects ( N = option "portfolio_objects_per_page" )
	- button "Load more"

	Button "Load more" should load next N reviews by ajax request ( N = option "portfolio_objects_per_page" ).


*/


/*

	Task: 5
	----------------

	Make WordPress show the same pages on two different url patterns:
	https://sitename.com/%postname%/
	https://sitename.com/%postname%/tail/

*/

/*
    IMPORTANT:
    don't forget to flush the rules by visiting Settings > Permalinks
    OR use flush_rewrite_rules() in the plugin activation (don't execute it at every page load).
 */

function ncube_rewrite()
{
    add_rewrite_rule('^([^/]*)/tail?', 'index.php?name=$matches[1]', 'top');
}

add_action('init', 'ncube_rewrite');

/*
    The easiest approach is just adding /tail/ to WP permalinks ( /%postname%/tail/ ).
    The downsides:
        * will make all post links generated with /tail/ by default
        * potentially unexpected behavior
 */