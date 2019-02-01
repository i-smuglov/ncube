<?php
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


/*
    WARNING: page_template is extremely theme-specific and highly depends on where templates are located and theme HTML/CSS,
    so my approach is good for Twenty Seventeen theme.
 */

function register_post_type_reviews()
{
    // just a standard post_type registration
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

add_action('init', 'register_post_type_reviews');

/*
    LOADMORE
*/

function get_reviews_post_type_callback()
{
    $paged = $_POST['page'];
    $args = array(
        'post_type' => 'reviews_post_type',
        'posts_per_page' => 10,
        'paged' => $paged,
        'post_status' => 'publish'
    );


    $loop = new WP_Query($args);
    while ($loop->have_posts()) : $loop->the_post();
        get_template_part('template-parts/post/content', 'reviews_post_type'); // theme-specific part. template-parts could be located anywhere in different themes
        wp_reset_postdata();

    endwhile;
    wp_die();
}

if (wp_doing_ajax()) {
    add_action('wp_ajax_get_reviews_post_type', 'get_reviews_post_type_callback');
    add_action('wp_ajax_nopriv_get_reviews_post_type', 'get_reviews_post_type_callback');
}

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
    My method is just to inject code after the loop (or other place), so it can fit most themes without problems with their structure.
    But we can create custom action or just manually place it in the correct place/template
 */

function loadmore_button()
{
    if (is_post_type_archive('reviews_post_type')) {
        echo '<button id="loadmore">Load more</button>';
    }
}

add_action('loop_end', 'loadmore_button');


/*
    EMAIL FORM

    I`m sure that it is better to create draft post instead of emailing content. It will make posting much more friendly for content manager.
    But task was to email info, so:
*/

function add_review_form()
{
    if (is_post_type_archive('reviews_post_type')) {
        echo '
        <form class="form" id="ajax-contact-form" action="#" method="post" enctype=”multipart/form-data” style="margin-top: 30px;">                            
        <input type="email" name="email" id="email" placeholder="Your Email" required="required">
        <input type="name" name="name" id="name" placeholder="Your Name" required="required">
        <textarea type="review" name="review" id="review" placeholder="Your review" required="required"></textarea>
        <input type="file" name="avatar" id="avatar" accept="image/x-png,image/gif,image/jpeg" />
        <button type="button" class="btn">Submit</button>';
        echo wp_nonce_field('add_review_form');
        echo '</form>
        <p class="report-a-bug-response"></p>';
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
                form_data.append('action', 'send_review');
                form_data.append('email', $("#ajax-contact-form #email").val());
                form_data.append('name', $("#ajax-contact-form #name").val());
                form_data.append('review', $("#ajax-contact-form #review").val());
                form_data.append('file_attach', $("#ajax-contact-form #avatar").prop('files')[0]);
                form_data.append('nonce', $("#ajax-contact-form #_wpnonce").val());

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

function send_review()
{
    $data = $_POST;
    check_ajax_referer('add_review_form', 'nonce');
    $to = get_option('admin_email');
    $subject = 'New review from ' . $data['name'];

    $body = $data['name'] . "\r\n";
    $body .= $data['email'] . "\r\n";
    $body .= $data['review'] . "\r\n";

    // There are a lot of other options fot file handling, but this pretty wp function doing this job too
    $upload = wp_upload_bits($_FILES["file_attach"]["name"], null, file_get_contents($_FILES["file_attach"]["tmp_name"]));
    if ($upload['error']) {
        $body .= 'File upload error: ' . $upload['error'];
    } else {
        $body .= 'Avatar URL: ' . $upload['url'];
    }

    $mail = wp_mail($to, $subject, $body);

    if ($mail) {
        wp_send_json_success('Thanks for reporting!');
    } else {
        wp_send_json_success('An error occur');
    }

}

add_action('wp_ajax_nopriv_send_review', 'send_review');
add_action('wp_ajax_send_review', 'send_review');
