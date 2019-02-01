<?php
/**
 * @package Ncube
 * @version 0.1
 */
/*
Plugin Name: Ncube
Plugin URI: http://wordpress.org/
Description: Test task for Ncube
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
wp_enqueue_script('jquery'); // Just to make sure that we have jquery for our ajax scripts

function define_ajaxurl() // Just to make sure that we have ajaxurl defined for our ajax scripts
{

    echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

add_action('wp_head', 'define_ajaxurl');

// Separated tasks for easy reading
define( 'NCUBE__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once( NCUBE__PLUGIN_DIR . 'task1.php' );
require_once( NCUBE__PLUGIN_DIR . 'task2.php' );
require_once( NCUBE__PLUGIN_DIR . 'task3.php' );
require_once( NCUBE__PLUGIN_DIR . 'task4.php' );
require_once( NCUBE__PLUGIN_DIR . 'task5.php' );










