<?php
/**
 * Metabox Functions
 *
 * @package     EGT
 * @subpackage  Admin/Classes
 * @copyright   Copyright (c) 2014, Bryan Monzon
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Register all the meta boxes for the Download custom post type
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return void
 */
function egt_add_meta_box() {


    $post_types = apply_filters( 'egt_metabox_post_types' , array( 'campaigns' ) );

    foreach ( $post_types as $post_type ) {

        /** Class Configuration */
        add_meta_box( 'campaigndetails', sprintf( __( '%1$s Details', 'egt' ), egt_get_label_singular(), egt_get_label_plural() ),  'egt_render_meta_box', $post_type, 'side', 'core' );
        add_meta_box( 'donorlist', 'Donor List',  'egt_render_donor_list_meta_box', $post_type, 'normal', 'core' );
        
    }
}
add_action( 'add_meta_boxes', 'egt_add_meta_box' );


/**
 * Sabe post meta when the save_post action is called
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param int $post_id Download (Post) ID
 * @global array $post All the data of the the current post
 * @return void
 */
function egt_meta_box_save( $post_id) {
    global $post, $egt_settings;

    if ( ! isset( $_POST['egt_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['egt_meta_box_nonce'], basename( __FILE__ ) ) )
        return $post_id;

    if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) )
        return $post_id;

    if ( isset( $post->post_type ) && $post->post_type == 'revision' )
        return $post_id;




    // The default fields that get saved
    $fields = apply_filters( 'egt_metabox_fields_save', array(
            'egt_goal',
            'egt_percent_funded',
            'egt_amount_raised',
            'egt_donor_count',
            'egt_donor_list',
            'egt_default_donation_amount',
            'egt_donation_type',
            'egt_button_class',
            'egt_button_text'


        )
    );


    foreach ( $fields as $field ) {
        if ( ! empty( $_POST[ $field ] ) ) {
            $new = apply_filters( 'egt_metabox_save_' . $field, $_POST[ $field ] );
            update_post_meta( $post_id, $field, $new );
        } else {
            delete_post_meta( $post_id, $field );
        }
    }
}
add_action( 'save_post', 'egt_meta_box_save' );





/** Class Configuration *****************************************************************/

/**
 * Class Metabox
 *
 * Extensions (as well as the core plugin) can add items to the main download
 * configuration metabox via the `egt_meta_box_fields` action.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return void
 */
function egt_render_meta_box() {
    global $post, $egt_settings;

    do_action( 'egt_meta_box_fields', $post->ID );
    wp_nonce_field( basename( __FILE__ ), 'egt_meta_box_nonce' );
}

/**
 * Guest List Metabox
 *
 * Extensions (as well as the core plugin) can add items to the main download
 * configuration metabox via the `ifg_gatherings_meta_box_fields` action.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return void
 */
function egt_render_donor_list_meta_box() {
    global $post, $egt_settings;

    do_action( 'egt_meta_box_donor_list_fields', $post->ID );
    wp_nonce_field( basename( __FILE__ ), 'egt_meta_box_nonce' );
}




/**
 * Render the fields
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param  [type] $post [description]
 * @return [type]       [description]
 */
function egt_render_fields( $post )
{
    global $post, $egt_settings; 

    $egt_goal                    = get_post_meta( $post->ID, 'egt_goal', true);
    $egt_amount_raised           = get_post_meta( $post->ID, 'egt_amount_raised', true);
    $donor_count                     = get_post_meta( $post->ID, 'egt_donor_count', true);
    $egt_default_donation_amount = get_post_meta( $post->ID, 'egt_default_donation_amount', true );
    $percent_funded                  = !empty( $egt_goal ) ? get_percent_funded( $egt_amount_raised, $egt_goal ) : '';
    $egt_donation_type           = get_post_meta( $post->ID, 'egt_donation_type', true );      
    $egt_button_class            = get_post_meta( $post->ID, 'egt_button_class', true ); 
    $egt_button_text             = get_post_meta( $post->ID, 'egt_button_text', true );                

    $donor_count    = !empty( $donor_count ) ? $donor_count : 0;
    $percent_funded = !empty( $percent_funded ) ? $percent_funded : 0;
    

    ?>  
    
    <div id="campaign_details_wrapper">
        <p>
            <strong>Campaign Goal</strong><br>
            <label for="egt_goal">
                $<input type="number" step="0.01" name="egt_goal" value="<?php echo $egt_goal ?>" /><br>
            </label>
        </p>
        <hr>
        <p>
            <label for="egt_amount_raised">
                <strong>Amount Raised</strong><br>
                <?php $egt_amount_raised = ($egt_amount_raised > 0) ? '$' . $egt_amount_raised : '$0.00'; echo $egt_amount_raised; ?><br>
            </label>
        </p>
        <hr>
        <p>
            <label for="egt_percent_funded">
                <strong>Percet Raised</strong><br>
                <?php echo $percent_funded; ?>%<br>
            </label>
        </p>
        <hr>
        <p>
            <label for="egt_donor_count">
                <strong>Donor Count</strong><br>
                <?php echo $donor_count; ?><br>
            </label>
        </p>
        <hr>
        <p>
            <label for="egt_default_donation_amount">
                $<input type="number" step="0.01" name="egt_default_donation_amount" value="<?php echo $egt_default_donation_amount ?>" /><br>
                <em class="hint">Enter a default donation amount.</em>
            </label>
        </p>
        <hr>
        <p>
            <label for="egt_donation_type">
                <strong>Donation Type</strong><br>
                <select name="egt_donation_type" id="">
                    <option value="one-time" <?php selected( $egt_donation_type, 'one-time' ); ?>>One Time</option>
                    <option value="recurring" <?php selected( $egt_donation_type, 'recurring' ); ?>>Recurring</option>
                </select>
            </label>
        </p>
        <hr>
        <p>
            <label for="egt_button_text">
                <strong>Custom Button Text</strong><br>
                <input type="text" value="<?php echo $egt_button_text; ?>" name="egt_button_text"><br>
                <em class="hint">Override the button text.</em>
            </label>
        </p>
        <hr>
        <p>
            <label for="egt_button_class">
                <strong>Button Class</strong><br>
                <input type="text" value="<?php echo $egt_button_class; ?>" name="egt_button_class"><br>
                <em class="hint">If needed you can override the button classes.</em>
            </label>
        </p>
    
    </div>
    
    <?php

}
add_action( 'egt_meta_box_fields', 'egt_render_fields', 10 );



/**
 * Render the fields
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param  [type] $post [description]
 * @return [type]       [description]
 */
function egt_render_donor_list_fields( $post )
{
    global $post;

    $donor_list  = get_post_meta( $post->ID, 'egt_donor_list', true );
    $donor_count = get_post_meta( $post->ID, 'egt_donor_count', true);

    $style = ($donor_count >= 1) ? ' donor ' : '';
    ?>
    <style>
    .date-registered{ float:right;}
    .donor{ border-bottom:1px solid #E5E5E5; padding:5px 0; display:block; line-height:200%; }
    </style>
    <div class="admin_donor_list_wrapper">

        <?php if( $donor_list ) : $line_count = 1; ?>
            <?php foreach( $donor_list as $donor ) : ?>
                <?php 
                    $date_created = strtotime( $donor['date_created'] ); 
                    $date         = date( 'm/d - g:ia', $date_created );

                ?>
                <div class="<?php echo $style; ?>"><a href="mailto:<?php echo $donor['email'] ?>" title="Send <?php echo $donor['donor_name']; ?> an email"><?php echo $donor['donor_name']; ?></a> <?php if( isset( $donor['org_name'] ) ) : echo ' - ' . $donor['org_name']; endif; ?><span class="date-registered"><?php echo $date; ?></span> </div>
            <?php endforeach; ?>
        <?php else: ?>
        <p>No donations for this campaign, yet.</p>
        <?php endif; ?>
    </div>
    <?php
}
add_action( 'egt_meta_box_donor_list_fields', 'egt_render_donor_list_fields', 10 );


