<?php
/**
 * Scripts
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Loads scripts needed for the admin area
 *
 * @since  1.0
 * @author Bryan Monzon
 */
function egt_load_admin_scripts( $hook ) 
{
    global $post,
    $egt_settings,
    $egt_settings_page,
    $wp_version;

    $js_dir  = EGT_PLUGIN_URL . 'assets/js/';
    $css_dir = EGT_PLUGIN_URL . 'assets/css/';

    wp_register_script( 'egt-admin-scripts', $js_dir . 'admin-scripts.js', array('jquery'), '1.0', true );

    wp_enqueue_script( 'egt-admin-scripts' );
    wp_localize_script( 'egt-admin-scripts', 'egt_vars', array(
        'new_media_ui'            => apply_filters( 'egt_use_35_media_ui', 1 ),
        ) 
    );

    if ( $hook == $egt_settings_page ) {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_style( 'colorbox', $css_dir . 'colorbox.css', array(), '1.3.20' );
        wp_enqueue_script( 'colorbox', $js_dir . 'jquery.colorbox-min.js', array( 'jquery' ), '1.3.20' );
        if( function_exists( 'wp_enqueue_media' ) && version_compare( $wp_version, '3.5', '>=' ) ) {
            //call for new media manager
            wp_enqueue_media();
        }
    }

}
add_action( 'admin_enqueue_scripts', 'egt_load_admin_scripts', 100 );