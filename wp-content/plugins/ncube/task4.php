<?php
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

	Button "Load more" should load next N portfolios by ajax request ( N = option "portfolio_objects_per_page" ).


*/

/*
 Steps for this task:
1) create post_type with metabox function
2) create metaboxes itself
    I`m sure that it will be much easier to just install ACF or any other metabox plugin.
    It is much more expensive to maintain that custom code, especially for gallery
3) Create options page
    Pretty straightforward: https://codex.wordpress.org/Creating_Options_Pages
4) Create page_template with portfolio_objects_per_page in the query
5) Add loadmore button with portfolio_objects_per_page in the query
 */

