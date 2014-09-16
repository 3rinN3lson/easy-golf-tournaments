<?php
/**
 * Post Type Functions
 *
 * @package     EGT
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, Bryan Monzon
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers and sets up the Downloads custom post type
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return void
 */
function setup_egt_post_types() {
	global $egt_settings;

	//Check to see if anything is set in the settings area.
	if( !empty( $egt_settings['team'] ) ) {
	    $slug = defined( 'EGT_SLUG' ) ? EGT_SLUG : $egt_settings['team'];
	} else {
	    $slug = defined( 'EGT_SLUG' ) ? EGT_SLUG : 'teams';
	}

	if( !isset( $egt_settings['disable_archive'] ) ) {
	    $archives = true;
	}else{
	    $archives = false;
	}

	$exclude_from_search = isset( $egt_settings['exclude_from_search'] ) ? true : false;
	
	$rewrite  = defined( 'EGT_DISABLE_REWRITE' ) && EGT_DISABLE_REWRITE ? false : array('slug' => $slug, 'with_front' => false);

	$team_labels =  apply_filters( 'egt_team_labels', array(
		'name' 				=> '%2$s',
		'singular_name' 	=> '%1$s',
		'add_new' 			=> __( 'Add New', 'egt' ),
		'add_new_item' 		=> __( 'Add New %1$s', 'egt' ),
		'edit_item' 		=> __( 'Edit %1$s', 'egt' ),
		'new_item' 			=> __( 'New %1$s', 'egt' ),
		'all_items' 		=> __( 'All %2$s', 'egt' ),
		'view_item' 		=> __( 'View %1$s', 'egt' ),
		'search_items' 		=> __( 'Search %2$s', 'egt' ),
		'not_found' 		=> __( 'No %2$s found', 'egt' ),
		'not_found_in_trash'=> __( 'No %2$s found in Trash', 'egt' ),
		'parent_item_colon' => '',
		'menu_name' 		=> __( '%2$s', 'egt' )
	) );

	foreach ( $team_labels as $key => $value ) {
	   $team_labels[ $key ] = sprintf( $value, egt_get_label_singular(), egt_get_label_plural() );
	}

	$team_args = array(
		'labels'              => $team_labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-groups',
		'query_var'           => true,
		'exclude_from_search' => $exclude_from_search,
		'rewrite'             => $rewrite,
		'map_meta_cap'        => true,
		'has_archive'         => $archives,
		'show_in_nav_menus'   => true,
		'hierarchical'        => false,
		'supports'            => apply_filters( 'egt_supports', array( 'title', 'editor', 'thumbnail', 'excerpt' ) ),
	);
	register_post_type( 'team', apply_filters( 'egt_post_type_args', $team_args ) );
	
}
add_action( 'init', 'setup_egt_post_types', 1 );

/**
 * Get Default Labels
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return array $defaults Default labels
 */
function egt_get_default_labels() {
	global $egt_settings;

	if( !empty( $egt_settings['team_label_plural'] ) || !empty( $egt_settings['team_label_singular'] ) ) {
	    $defaults = array(
	       'singular' => $egt_settings['team_label_singular'],
	       'plural' => $egt_settings['team_label_plural']
	    );
	 } else {
		$defaults = array(
		   'singular' => __( 'Team', 'egt' ),
		   'plural' => __( 'Teams', 'egt')
		);
	}
	
	return apply_filters( 'egt_default_name', $defaults );

}

/**
 * Get Singular Label
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return string $defaults['singular'] Singular label
 */
function egt_get_label_singular( $lowercase = false ) {
	$defaults = egt_get_default_labels();
	return ($lowercase) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Label
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return string $defaults['plural'] Plural label
 */
function egt_get_label_plural( $lowercase = false ) {
	$defaults = egt_get_default_labels();
	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

/**
 * Change default "Enter title here" input
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param string $title Default title placeholder text
 * @return string $title New placeholder text
 */
function egt_change_default_title( $title ) {
     $screen = get_current_screen();

     if  ( 'egt' == $screen->post_type ) {
     	$label = egt_get_label_singular();
        $title = sprintf( __( 'Enter %s title here', 'egt' ), $label );
     }

     return $title;
}
add_filter( 'enter_title_here', 'egt_change_default_title' );

/**
 * Registers the custom taxonomies for the downloads custom post type
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return void
*/
function egt_setup_taxonomies() {

	$slug     = defined( 'EGT_SLUG' ) ? EGT_SLUG : 'team';

	/** Categories */
	$category_labels = array(
		'name' 				=> sprintf( _x( '%s Categories', 'taxonomy general name', 'egt' ), egt_get_label_singular() ),
		'singular_name' 	=> _x( 'Category', 'taxonomy singular name', 'egt' ),
		'search_items' 		=> __( 'Search Categories', 'egt'  ),
		'all_items' 		=> __( 'All Categories', 'egt'  ),
		'parent_item' 		=> __( 'Parent Category', 'egt'  ),
		'parent_item_colon' => __( 'Parent Category:', 'egt'  ),
		'edit_item' 		=> __( 'Edit Category', 'egt'  ),
		'update_item' 		=> __( 'Update Category', 'egt'  ),
		'add_new_item' 		=> __( 'Add New Category', 'egt'  ),
		'new_item_name' 	=> __( 'New Category Name', 'egt'  ),
		'menu_name' 		=> __( 'Categories', 'egt'  ),
	);

	$category_args = apply_filters( 'egt_category_args', array(
			'hierarchical' 		=> true,
			'labels' 			=> apply_filters('egt_category_labels', $category_labels),
			'show_ui' 			=> true,
			'query_var' 		=> 'team_category',
			'rewrite' 			=> array('slug' => $slug . '/category', 'with_front' => false, 'hierarchical' => true ),
			'capabilities'  	=> array( 'manage_terms','edit_terms', 'assign_terms', 'delete_terms' ),
			'show_admin_column'	=> true
		)
	);
	register_taxonomy( 'team_category', array( 'team' ), $category_args );
	register_taxonomy_for_object_type( 'team_category', 'team' );

}
add_action( 'init', 'egt_setup_taxonomies', 0 );



/**
 * Updated Messages
 *
 * Returns an array of with all updated messages.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $messages Post updated message
 * @return array $messages New post updated messages
 */
function egt_updated_messages( $messages ) {
	global $post, $post_ID;

	$url1 = '<a href="' . get_permalink( $post_ID ) . '">';
	$url2 = egt_get_label_singular();
	$url3 = '</a>';

	$messages['campaigns'] = array(
		1 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'egt' ), $url1, $url2, $url3 ),
		4 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'egt' ), $url1, $url2, $url3 ),
		6 => sprintf( __( '%2$s published. %1$sView %2$s%3$s.', 'egt' ), $url1, $url2, $url3 ),
		7 => sprintf( __( '%2$s saved. %1$sView %2$s%3$s.', 'egt' ), $url1, $url2, $url3 ),
		8 => sprintf( __( '%2$s submitted. %1$sView %2$s%3$s.', 'egt' ), $url1, $url2, $url3 )
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'egt_updated_messages' );
