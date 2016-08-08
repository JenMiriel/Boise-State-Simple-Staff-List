<?php

/**
* Plugin Name:	Boise State Simple Staff List
* Plugin URI:	www.boisestate.edu
* Description:	A simple plugin to build and display a staff listing for your website, optimized for BSU.
* Version:		0.8.1
* Author:		Jen West
**/





/*
// Include some files and setup our plugin dir url
//////////////////////////////*/

define( 'STAFFLIST_PATH', plugin_dir_url(__FILE__) );
include_once('inc/admin-install-uninstall.php');
include_once('inc/admin-views.php');
include_once('inc/admin-save-data.php');
include_once('inc/admin-utilities.php');
include_once('inc/user-view-show-staff-list.php');
include_once('inc/updater.php');




/*
// Add post-thumbnails support for our custom post type
//////////////////////////////*/

add_theme_support( 'post-thumbnails', array( 'staff-member' ));





/*
// Register Activation/Deactivation Hooks
//////////////////////////////*/

// function location: /inc/admin-install-uninstall.php

register_activation_hook( __FILE__, 'boise_state_ssl_staff_member_activate' );
register_deactivation_hook( __FILE__, 'boise_state_ssl_staff_member_deactivate' );
register_uninstall_hook( __FILE__, 'boise_state_ssl_staff_member_uninstall' );

// Need to check plugin version here and run boise_state_ssl_staff_member_plugin_update()
// function location: /inc/admin-install-uninstall.php
/*if ( ! function_exists( 'get_plugins' ) )
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
$plugin_file = basename( ( __FILE__ ) );
$plugin_version = $plugin_folder[$plugin_file]['Version'];    
$boise_state_ssl_ver_option = get_option('_simple_staff_list_version');
if ($boise_state_ssl_ver_option == "" || $boise_state_ssl_ver_option <= $plugin_version){
	boise_state_ssl_staff_member_plugin_update($boise_state_ssl_ver_option, $plugin_version);
}
*/
// End plugin version check

//Check the BSU GitHub repo for updates to this plugin
if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
    $config = array(
        'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
        'proper_folder_name' => 'BSUSimpleStaffList', // this is the name of the folder your plugin lives in
        'api_url' => 'https://api.github.com/repos/JenMiriel/BSU_SimpleStaffList', // the github API url of your github repo
        'raw_url' => 'https://raw.github.com/JenMiriel/BSU_SimpleStaffList/master', // the github raw url of your github repo
        'github_url' => 'https://github.com/JenMiriel/BSU_SimpleStaffList', // the github url of your github repo
        'zip_url' => 'https://github.com/JenMiriel/BSU_SimpleStaffList/zipball/master', // the zip url of the github repo
        'sslverify' => true, // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
        'requires' => '3.0', // which version of WordPress does your plugin require?
        'tested' => '4.5.3', // which version of WordPress is your plugin tested up to?
        'readme' => 'README.MD' // which file to use as the readme for the version number
    );
    new WPGitHubUpdater($config);
}
//End BSU GitHub repo check



/*
// Enqueue Plugin Scripts and Styles
//////////////////////////////*/


function boise_state_ssl_staff_member_admin_print_scripts() {
	//** Admin Scripts
	wp_register_script( 'staff-member-admin-scripts', STAFFLIST_PATH . 'js/staff-member-admin-scripts.js', array('jquery', 'jquery-ui-sortable' ), '1.0', false );
	wp_enqueue_script( 'staff-member-admin-scripts' );
}

add_action( 'admin_enqueue_scripts', 'boise_state_ssl_staff_member_admin_enqueue_styles' );

function boise_state_ssl_staff_member_admin_enqueue_styles() {
	//** Admin Styles
	wp_register_style( 'staff-list-css', STAFFLIST_PATH . 'css/admin-staff-list.css' );
	wp_enqueue_style ( 'staff-list-css' );
}

add_action( 'wp_enqueue_scripts', 'boise_state_ssl_staff_member_public_enqueue_styles');

function boise_state_ssl_staff_member_public_enqueue_styles() {
	//** Front-end/Public facing Styles
	wp_register_style( 'staff-list-public-css', STAFFLIST_PATH . 'css/public-staff-list.css' );
	wp_enqueue_style ( 'staff-list-public-css' );
}

add_action( 'wp_enqueue_scripts', 'boise_state_ssl_staff_member_enqueue_styles');

function boise_state_ssl_staff_member_enqueue_styles(){
	//** Front-end Custom Style
	if (get_option('_staff_listing_write_external_css') == "yes") {
		wp_register_style( 'staff-list-custom-css', get_stylesheet_directory_uri() . '/simple-staff-list-custom.css' );
		wp_enqueue_style ( 'staff-list-custom-css' );
	}
}





/*
// Setup Our Staff Member CPT
//////////////////////////////*/

add_action( 'init', 'boise_state_ssl_staff_member_init' );

function boise_state_ssl_staff_member_init() {
    $labels = array(
        'name' => _x('Staff Members', 'post type general name'),
        'singular_name' => _x('Staff Member', 'post type singular name'),
        'add_new' => _x('Add New', 'staff member'),
        'add_new_item' => __('Add New Staff Member'),
        'edit_item' => __('Edit Staff Member'),
        'new_item' => __('New Staff Member'),
        'view_item' => __('View Staff Member'),
        'search_items' => __('Search Staff Members'),
        'exclude_from_search' => true,
        'not_found' =>  __('No staff members found'),
        'not_found_in_trash' => __('No staff members found in Trash'),
        'parent_item_colon' => '',
        'all_items' => 'All Staff Members',
        'menu_name' => 'Boise State Staff Members'
);

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'page',
        'has_archive' => false,
        'hierarchical' => false,
        'menu_position' => 100,
        'rewrite' => array('slug'=>'staff-members','with_front'=>false),
        'supports' => array( 'title', 'thumbnail', 'excerpt' )
    );

    register_post_type( 'staff-member', $args );
}





/*
// Setup Our Staff Group Taxonomy
//////////////////////////////*/

add_action( 'init', 'boise_state_ssl_custom_tax' );

function boise_state_ssl_custom_tax() {
	
	$labels = array(
		'name' => _x( 'Groups', 'taxonomy general name' ),
		'singular_name' => _x( 'Group', 'taxonomy singular name' ),
		'search_items' => __( 'Search Groups' ),
		'all_items' => __( 'All Groups' ),
		'parent_item' => __( 'Parent Group' ),
		'parent_item_colon' => __( 'Parent Group:' ),
		'edit_item' => __( 'Edit Group' ), 
		'update_item' => __( 'Update Group' ),
		'add_new_item' => __( 'Add New Group' ),
		'new_item_name' => __( 'New Group Name' ),
	); 	

	register_taxonomy( 'staff-member-group', array( 'staff-member' ), array(
		'hierarchical' => true,
		'labels' => $labels, /* NOTICE: Here is where the $labels variable is used */
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'group' ),
	));
}





/*
// Hide Excerpt Box by default
//////////////////////////////*/

// Change what's hidden by default
add_filter('default_hidden_meta_boxes', 'boise_state_ssl_hide_meta_lock', 10, 2);
function boise_state_ssl_hide_meta_lock($hidden, $screen) {
        if ( $screen->base == 'staff-member' )
                $hidden = array( 'postexcerpt' );
        return $hidden;
}





/*
// Change Title Text
//////////////////////////////*/

/**
 * Change "Enter Title Here" text
 * 
 * Changes title text on post edit screen for staff-member CPT
 *
 * @param    string    $screen    	get_current_screen()
 * @return   string               	returns new placeholder text for "Enter title here" input
 */
 
add_filter( 'enter_title_here', 'boise_state_ssl_staff_member_change_title' );
function boise_state_ssl_staff_member_change_title( $title ){
    $screen = get_current_screen();
    if ( $screen->post_type == 'staff-member' ) {
        $title = 'Staff Name';
    }
    return $title;
}





/*
// Add MetaBoxes
//////////////////////////////*/

/**
 * Change Featured Image title
 *
 * Removes the default featured image box and adds a new one with a new title
 * 
 */
 
add_action('do_meta_boxes', 'boise_state_ssl_staff_member_featured_image_text');
function boise_state_ssl_staff_member_featured_image_text() {

    remove_meta_box( 'postimagediv', 'staff-member', 'side' );
    if (current_theme_supports('post-thumbnails')) {
	    add_meta_box('postimagediv', __('Staff Photo'), 'post_thumbnail_meta_box', 'staff-member', 'normal', 'high');
	} else {
		add_meta_box('staff-member-warning', __('Staff Photo'), 'boise_state_ssl_staff_member_warning_meta_box', 'staff-member', 'normal', 'high');
	}
}


/**
 * Adds MetaBoxes for staff-member
 * 
 * All metabox callback functions are located in inc/admin-views.php
 *
 */

add_action('do_meta_boxes', 'boise_state_ssl_staff_member_add_meta_boxes');
function boise_state_ssl_staff_member_add_meta_boxes() {

    add_meta_box('staff-member-info', __('Staff Member Info'), 'boise_state_ssl_staff_member_info_meta_box', 'staff-member', 'normal', 'high');
    
    add_meta_box('staff-member-bio', __('Staff Member Bio'), 'boise_state_ssl_staff_member_bio_meta_box', 'staff-member', 'normal', 'high');
}





/*
// Create Custom Columns
//////////////////////////////*/


/**
 * Adds custom columns for staff-member CPT admin display
 *
 * @param    array    $cols    New column titles
 * @return   array             Column titles
 */
 
add_filter( "manage_staff-member_posts_columns", "boise_state_ssl_staff_member_custom_columns" );
function boise_state_ssl_staff_member_custom_columns( $cols ) {
	$cols = array(
		'cb'				  =>     '<input type="checkbox" />',
		'title'				  => __( 'Name' ),
		'photo'				  => __( 'Photo' ),
		'_staff_member_title' => __( 'Position' ),
		'_staff_member_email' => __( 'Email' ),
		'_staff_member_phone' => __( 'Phone' ),
		'_staff_member_bio'   => __( 'Bio' ),
	);
	return $cols;
}





/*
// Add SubPage for Ordering function
//////////////////////////////*/

/**
 * Registers sub pages for staff-member CPT
 * 
 * Adds "Order" and "Templates" page to WP nav. 
 * ALSO adds the print scripts action to load our js only on the pages we need it.
 *
 * @param    function    $order_page	    sets up the Order page
 * @param    function    $templates_page    sets up the Order page
 * 
 */
 
add_action( 'admin_menu', 'boise_state_ssl_staff_member_register_menu' );
function boise_state_ssl_staff_member_register_menu() {
	$order_page 	= add_submenu_page(
						'edit.php?post_type=staff-member',
						'Order Staff Members',
						'Order',
						'edit_pages', 'staff-member-order',
						'boise_state_ssl_staff_member_order_page'
					);
	
	$templates_page = add_submenu_page(
						'edit.php?post_type=staff-member',
						'Display Templates',
						'Templates',
						'edit_pages', 'staff-member-template',
						'boise_state_ssl_staff_member_template_page'
					);
	
	$usage_page 	= add_submenu_page(
						'edit.php?post_type=staff-member',
						'Simple Staff List Usage',
						'Usage',
						'edit_pages', 'staff-member-usage',
						'boise_state_ssl_staff_member_usage_page'
					);
	
	add_action( 'admin_print_scripts-'.$order_page, 'boise_state_ssl_staff_member_admin_print_scripts' );
	add_action( 'admin_print_scripts-'.$templates_page, 'boise_state_ssl_staff_member_admin_print_scripts' );
}





/*
// Make Sure We Add The Custom CSS File on Theme Switch
//////////////////////////////*/

function boise_state_ssl_staff_member_create_css_on_switch_theme($new_theme) {
    $filename = get_stylesheet_directory() . '/simple-staff-list-custom.css';
    $custom_css = get_option('_staff_listing_custom_css');
    file_put_contents($filename, $custom_css);
}
if ( get_option('_staff_listing_write_external_css') == 'yes' ){
	add_action('switch_theme', 'boise_state_ssl_staff_member_create_css_on_switch_theme');
}
?>