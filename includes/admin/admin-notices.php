<?php
/**
 * Admin Notices
 *
 * @package     EGT
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2014, Bryan Monzon
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Messages
 *
 * @since  1.0
 * @author Bryan Monzon
 * @global $egt_settings Array of all the EGT Options
 * @return void
 */
function egt_admin_messages() {
    global $egt_settings;

    settings_errors( 'egt-notices' );
}
add_action( 'admin_notices', 'egt_admin_messages' );


/**
 * Dismisses admin notices when Dismiss links are clicked
 *
 * @since 1.0
 * @return void
*/
function egt_dismiss_notices() {

    $notice = isset( $_GET['egt_notice'] ) ? $_GET['egt_notice'] : false;

    if( ! $notice )
        return; // No notice, so get out of here

    update_user_meta( get_current_user_id(), '_egt_' . $notice . '_dismissed', 1 );

    wp_redirect( remove_query_arg( array( 'egt_action', 'egt_notice' ) ) ); exit;

}
add_action( 'egt_dismiss_notices', 'egt_dismiss_notices' );
