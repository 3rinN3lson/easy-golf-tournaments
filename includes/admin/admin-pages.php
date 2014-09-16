<?php
/**
 * Admin Pages
 *
 * @package     EGT
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2014, Bryan Monzon
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;




/**
 * Creates the admin menu pages under Donately and assigns them their global variables
 *
 * @since  1.0
 * @author Bryan Monzon
 * @global  $egt_settings_page
 * @return void
 */
function egt_add_menu_page() {
    global $egt_settings_page;

    $egt_settings_page = add_submenu_page( 'edit.php?post_type=team', __( 'Settings', 'egt' ), __( 'Settings', 'egt'), 'edit_pages', 'team-settings', 'egt_settings_page' );
    
}
add_action( 'admin_menu', 'egt_add_menu_page', 11 );
