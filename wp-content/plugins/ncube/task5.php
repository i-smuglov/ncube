<?php
/*

	Task: 5
	----------------

	Make WordPress show the same pages on two different url patterns:
	https://sitename.com/%postname%/
	https://sitename.com/%postname%/tail/

*/

/*
    The easiest approach is just adding /tail/ to WP permalinks ( /%postname%/tail/ ).
    The downsides:
        * will make all post links generated with /tail/ by default (but they are still available at /%postname%/)
        * potentially unexpected behavior

    So this method is safer:
 */

function ncube_rewrite()
{
    add_rewrite_rule('^([^/]*)/tail?', 'index.php?name=$matches[1]', 'top');
}

add_action('init', 'ncube_rewrite');

/*
    IMPORTANT:
    don't forget to flush the rules by visiting Settings > Permalinks
    OR use flush_rewrite_rules() in the plugin activation (don't execute it at every page load).
 */