<?php
/***
Plugin Name: Curatora Stories by Curatora.io
Plugin URI: https://curatora.io/
Description: Curotara Stories by Curatora.io seamlessly integrates with the Curatora.io app to deliver a seamless content curation experience. This plugin allows you to effortlessly publish your curated posts from the Curatora.io app to your website, without any manual intervention. Making it easier to share the best content with your audience. 
Version: 1.0
Author: Curatora.io
Author URI: https://curatora.io
License: GPLv3
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*  Reading time  */
function curatora_display_read_time($post_id) {
    $content = get_post_field( 'post_content', $post_id);
    $count_words = str_word_count( strip_tags( $content ) );
	
    $read_time = ceil($count_words / 300);
	
    $read_time_output =  $read_time;

    return $read_time_output;
}

/* register custom post type */
function curatora_story_post_type() {
    // set up labels
    $labels = array(
        'name' => 'Stories',
        'singular_name' => 'Story',
        'add_new' => 'Add New Story',
        'add_new_item' => 'Add New Story',
        'edit_item' => 'Edit Story',
        'new_item' => 'New Story',
        'all_items' => 'All Stories',
        'view_item' => 'View Story',
        'search_items' => 'Search Stories',
        'not_found' =>  'No Stories Found',
        'not_found_in_trash' => 'No Stories found in Trash', 
        'parent_item_colon' => '',
        'menu_name' => 'Stories',
    );

    //register post type
    register_post_type( 'story', array(
        'labels' => $labels,
        'has_archive' => true,
        'public' => true,
        "rest_base" => "story",
        'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail','page-attributes','custom-fields' ),
        'taxonomies' => array( 'post_tag', 'category' ),    
        'exclude_from_search' => false,
        'show_in_rest' => true,
        'capability_type' => 'post',
        'rewrite' => array( 'slug' => 'stories','with_front' => false ),
        )
    );

    flush_rewrite_rules();
}
add_action( 'init', 'curatora_story_post_type' );

function curatora_register_story_meta_fields() {
    register_meta( 'post', 'post_permalink', array(
        'type' => 'string',
        'description' => 'Story Permalink',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'post_source', array(
        'type' => 'string',
        'description' => 'Story Source',
        'single' => true,
        'show_in_rest' => true
    ));
}
add_action( 'rest_api_init', 'curatora_register_story_meta_fields');

/**
 * Adding stories as a post type 
 * */
function curatora_add_stories_post_types_to_query( $query ) {
    if ( is_home() && $query->is_main_query() )
        $query->set( 'post_type', array( 'post', 'story' ) );
    return $query;
}
add_action( 'pre_get_posts', 'curatora_add_stories_post_types_to_query' );

add_filter( 'wp_nav_menu_items', 'curatora_add_story_menu_item', 10, 2 );
function curatora_add_story_menu_item ( $items, $args ) {  
	$locations = array_keys(get_theme_mod('nav_menu_locations'));
	$location = $locations[0];

    if ( $args->theme_location == $location) {
        $items .= '<li><a  href="'.get_home_url().'/story" title="Story">Story</a></li>';
    }
	
    return $items;
}

add_filter('template_include', 'curatora_load_stories_template', 99);
 
function curatora_load_stories_template($original_template) {
	$current_url = esc_url(sanitize_url($_SERVER['REQUEST_URI']));
	if (strpos($current_url, '/story/') !== false) {
		// You are on the "story" page
		include plugin_dir_path( __FILE__ )  . '/page-story.php';
        die();
	}

    return $original_template;    
}

add_action('wp_enqueue_scripts', 'curatora_enqueu_template_styles');
function curatora_enqueu_template_styles() {
    wp_register_style( 'template-styles', plugins_url( 'style.css', __FILE__ ) );    
    wp_enqueue_style( 'template-styles' );
}

register_deactivation_hook( __FILE__, 'curatora_deactivate_story_plugin' );
function curatora_deactivate_story_plugin() {
    $page = get_page_by_path( 'story' );
    wp_delete_post($page->ID, true);
}

register_activation_hook(__FILE__, 'curatora_activate_story_plugin');
function curatora_activate_story_plugin() {
    // Create a custom Story page
    $check_page_exist = get_page_by_title('story', 'OBJECT', 'page');

    // Check if the page already exists
    if(empty($check_page_exist)) {
        $page_id = wp_insert_post(
            array(
            'comment_status' => 'close',
            'ping_status'    => 'close',
            'post_author'    => 1,
            'post_title'     => ucwords('story'),
            'post_name'      => sanitize_title('story'), //strtolower(str_replace(' ', '-', trim('title_of_the_page'))),
            'post_status'    => 'publish',
            'post_content'   => '',
            'post_type'      => 'page',
            'post_parent'    => ''
            )
        );
    }
}

function display_curatora_stories($atts) {
    // Extract shortcode attributes
    $atts = shortcode_atts(
        array(
            'posts_per_page'  => 6,         // Number of posts to display
            'template_style'  => 'block', // Default template style
        ),
        $atts
    );

    // Define an array of allowed template styles and their corresponding templates
    $template_styles = array(
        'grid'     => 'curatora-grid-template.php', // Default template file
        'block' => 'curatora-block-template.php', // Custom template file
        // Add more styles as needed
    );

    // Check if the specified template style exists; if not, fall back to the default template
    $template_style = isset($template_styles[$atts['template_style']]) ? $atts['template_style'] : 'block';

    $paged = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
    query_posts( 
        array ( 
            'post_type' => 'story',
            'post_status' => 'publish',
            'posts_per_page' => $atts['posts_per_page'], 
            'paged' => $paged 
        ) 
    );      

    // Output the grid with the selected style and template
    $output = '<div id="story_content" class="story-' . esc_attr($atts['template_style']) . '">';

    // Include the selected template file from the plugin directory
    if (isset($template_styles[$template_style])) {
        $template_file = plugin_dir_path(__FILE__) . 'templates/' . $template_styles[$template_style];

        if (file_exists($template_file)) {
            ob_start();
            include($template_file);
            $output .= ob_get_clean();
        } else {
            $output .= 'Template file not found.';
        }
    } else {
        $output .= 'Invalid template style.';
    }

    $output .= '</div>';
    return $output;
}
add_shortcode('curatora_stories', 'display_curatora_stories');

function curatora_custom_excerpt_length( $length ) {
    return 30;
}
add_filter( 'excerpt_length', 'curatora_custom_excerpt_length', 999 );