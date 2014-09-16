<?php
/**
 * Register Settings
 *
 * @package     EGT
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2014, Bryan Monzon
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return mixed
 */
function egt_get_option( $key = '', $default = false ) {
    global $egt_settings;
    return isset( $egt_settings[ $key ] ) ? $egt_settings[ $key ] : $default;
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return array EGT settings
 */
function egt_get_settings() {

    $settings = get_option( 'egt_settings' );
    if( empty( $settings ) ) {

        // Update old settings with new single option

        $general_settings = is_array( get_option( 'egt_settings_general' ) )    ? get_option( 'egt_settings_general' )      : array();


        $settings = array_merge( $general_settings );

        update_option( 'egt_settings', $settings );
    }
    return apply_filters( 'egt_get_settings', $settings );
}

/**
 * Add all settings sections and fields
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return void
*/
function egt_register_settings() {

    if ( false == get_option( 'egt_settings' ) ) {
        add_option( 'egt_settings' );
    }

    foreach( egt_get_registered_settings() as $tab => $settings ) {

        add_settings_section(
            'egt_settings_' . $tab,
            __return_null(),
            '__return_false',
            'egt_settings_' . $tab
        );

        foreach ( $settings as $option ) {
            add_settings_field(
                'egt_settings[' . $option['id'] . ']',
                $option['name'],
                function_exists( 'egt_' . $option['type'] . '_callback' ) ? 'egt_' . $option['type'] . '_callback' : 'egt_missing_callback',
                'egt_settings_' . $tab,
                'egt_settings_' . $tab,
                array(
                    'id'      => $option['id'],
                    'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
                    'name'    => $option['name'],
                    'section' => $tab,
                    'size'    => isset( $option['size'] ) ? $option['size'] : null,
                    'options' => isset( $option['options'] ) ? $option['options'] : '',
                    'std'     => isset( $option['std'] ) ? $option['std'] : ''
                )
            );
        }

    }

    // Creates our settings in the options table
    register_setting( 'egt_settings', 'egt_settings', 'egt_settings_sanitize' );

}
add_action('admin_init', 'egt_register_settings');

/**
 * Retrieve the array of plugin settings
 *
 * @since  1.0
 * @author Bryan Monzon
 * @return array
*/
function egt_get_registered_settings() {

    $pages = get_pages();
    $pages_options = array( 0 => '' ); // Blank option
    if ( $pages ) {
        foreach ( $pages as $page ) {
            $pages_options[ $page->ID ] = $page->post_title;
        }
    }

    if( class_exists( 'RGFormsModel' ) ) {
       $form_options = array( 0 => '' ); // Blank option
        $forms = RGFormsModel::get_forms( null, 'title' );
        if( $forms ) {
            foreach( $forms as $form ) {
                $form_options[$form->id] = $form->title;
            }
        }    
    }
    

    /**
     * 'Whitelisted' EGT settings, filters are provided for each settings
     * section to allow extensions and other plugins to add their own settings
     */
    $egt_settings = array(
        /** General Settings */
        'general' => apply_filters( 'egt_settings_general',
            array(
                'basic_settings' => array(
                    'id' => 'basic_settings',
                    'name' => '<strong>' . __( 'Basic Settings', 'egt' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header'
                ),
                'team' => array(
                    'id' => 'team',
                    'name' => __( egt_get_label_plural() . ' URL Slug', 'egt' ),
                    'desc' => __( 'Enter the slug you would like to use for your ' . strtolower( egt_get_label_plural() ) . '. (<em>You will need to <a href="' . admin_url( 'options-permalink.php' ) . '">refresh permalinks</a>, after saving changes</em>).'  , 'egt' ),
                    'type' => 'text',
                    'size' => 'medium',
                    'std' => strtolower( egt_get_label_plural() )
                ),
                'team_label_plural' => array(
                    'id' => 'team_label_plural',
                    'name' => __( egt_get_label_plural() . ' Label Plural', 'egt' ),
                    'desc' => __( 'Enter the label you would like to use for your ' . strtolower( egt_get_label_plural() ) . '.', 'egt' ),
                    'type' => 'text',
                    'size' => 'medium',
                    'std' => egt_get_label_plural()
                ),
                'team_label_singular' => array(
                    'id' => 'team_label_singular',
                    'name' => __( egt_get_label_singular() . ' Label Singular', 'egt' ),
                    'desc' => __( 'Enter the label you would like to use for your ' . strtolower( egt_get_label_singular() ) . '.', 'egt' ),
                    'type' => 'text',
                    'size' => 'medium',
                    'std' => egt_get_label_singular()
                ),
                'disable_archive' => array(
                    'id' => 'disable_archive',
                    'name' => __( 'Disable Archives Page', 'egt' ),
                    'desc' => __( 'Check to disable archives page. (<em>You might need to <a href="' . admin_url( 'options-permalink.php' ) . '">refresh permalinks</a>, after saving changes</em>).', 'egt' ),
                    'type' => 'checkbox',
                    'std' => ''
                ),
                'exclude_from_search' => array(
                    'id' => 'exclude_from_search',
                    'name' => __( 'Exclude from Search', 'egt' ),
                    'desc' => __( 'Check to exclude from search. (<em>You might need to <a href="' . admin_url( 'options-permalink.php' ) . '">refresh permalinks</a>, after saving changes</em>)', 'egt' ),
                    'type' => 'checkbox',
                    'std' => ''
                ),
            )
        ),
        
    );

    return $egt_settings;
}

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @return void
 */
function egt_header_callback( $args ) {
    $html = '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
    echo $html;
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @return void
 */
function egt_checkbox_callback( $args ) {
    global $egt_settings;

    $checked = isset($egt_settings[$args['id']]) ? checked(1, $egt_settings[$args['id']], false) : '';
    $html = '<input type="checkbox" id="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" name="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" value="1" ' . $checked . '/>';
    $html .= '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @return void
 */
function egt_multicheck_callback( $args ) {
    global $egt_settings;

    foreach( $args['options'] as $key => $option ):
        if( isset( $egt_settings[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
        echo '<input name="egt_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" id="egt_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
        echo '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
    endforeach;
    echo '<p class="description">' . $args['desc'] . '</p>';
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @return void
 */
function egt_radio_callback( $args ) {
    global $egt_settings;

    foreach ( $args['options'] as $key => $option ) :
        $checked = false;

        if ( isset( $egt_settings[ $args['id'] ] ) && $egt_settings[ $args['id'] ] == $key )
            $checked = true;
        elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $egt_settings[ $args['id'] ] ) )
            $checked = true;

        echo '<input name="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"" id="egt_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
        echo '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
    endforeach;

    echo '<p class="description">' . $args['desc'] . '</p>';
}



/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @return void
 */
function egt_text_callback( $args ) {
    global $egt_settings;

    if ( isset( $egt_settings[ $args['id'] ] ) )
        $value = $egt_settings[ $args['id'] ];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="text" class="' . $size . '-text" id="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" name="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}


/**
 * EGT Hidden Text Field Callback
 *
 * Renders text fields (Hidden, for necessary values in egt_settings in the wp_options table)
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @return void
 * @todo refactor it is not needed entirely
 */
function egt_hidden_callback( $args ) {
    global $egt_settings;

    $hidden = isset($args['hidden']) ? $args['hidden'] : false;

    if ( isset( $egt_settings[ $args['id'] ] ) )
        $value = $egt_settings[ $args['id'] ];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="hidden" class="' . $size . '-text" id="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" name="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['std'] . '</label>';

    echo $html;
}




/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @return void
 */
function egt_textarea_callback( $args ) {
    global $egt_settings;

    if ( isset( $egt_settings[ $args['id'] ] ) )
        $value = $egt_settings[ $args['id'] ];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<textarea class="large-text" cols="50" rows="5" id="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" name="egt_settings_' . $args['section'] . '[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
    $html .= '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @return void
 */
function egt_password_callback( $args ) {
    global $egt_settings;

    if ( isset( $egt_settings[ $args['id'] ] ) )
        $value = $egt_settings[ $args['id'] ];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="password" class="' . $size . '-text" id="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" name="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
    $html .= '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @return void
 */
function egt_missing_callback($args) {
    printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'egt' ), $args['id'] );
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @return void
 */
function egt_select_callback($args) {
    global $egt_settings;

    if ( isset( $egt_settings[ $args['id'] ] ) )
        $value = $egt_settings[ $args['id'] ];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $html = '<select id="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" name="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"/>';

    foreach ( $args['options'] as $option => $name ) :
        $selected = selected( $option, $value, false );
        $html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
    endforeach;

    $html .= '</select>';
    $html .= '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @return void
 */
function egt_color_select_callback( $args ) {
    global $egt_settings;

    if ( isset( $egt_settings[ $args['id'] ] ) )
        $value = $egt_settings[ $args['id'] ];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $html = '<select id="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" name="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"/>';

    foreach ( $args['options'] as $option => $color ) :
        $selected = selected( $option, $value, false );
        $html .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
    endforeach;

    $html .= '</select>';
    $html .= '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @global $wp_version WordPress Version
 */
function egt_rich_editor_callback( $args ) {
    global $egt_settings, $wp_version;

    if ( isset( $egt_settings[ $args['id'] ] ) )
        $value = $egt_settings[ $args['id'] ];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
        $html = wp_editor( stripslashes( $value ), 'egt_settings_' . $args['section'] . '[' . $args['id'] . ']', array( 'textarea_name' => 'egt_settings_' . $args['section'] . '[' . $args['id'] . ']' ) );
    } else {
        $html = '<textarea class="large-text" rows="10" id="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" name="egt_settings_' . $args['section'] . '[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
    }

    $html .= '<br/><label for="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @return void
 */
function egt_upload_callback( $args ) {
    global $egt_settings;

    if ( isset( $egt_settings[ $args['id'] ] ) )
        $value = $egt_settings[$args['id']];
    else
        $value = isset($args['std']) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="text" class="' . $size . '-text egt_upload_field" id="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" name="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
    $html .= '<span>&nbsp;<input type="button" class="egt_settings_upload_button button-secondary" value="' . __( 'Upload File', 'egt' ) . '"/></span>';
    $html .= '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}


/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @global $egt_settings Array of all the EGT Options
 * @return void
 */
function egt_color_callback( $args ) {
    global $egt_settings;

    if ( isset( $egt_settings[ $args['id'] ] ) )
        $value = $egt_settings[ $args['id'] ];
    else
        $value = isset( $args['std'] ) ? $args['std'] : '';

    $default = isset( $args['std'] ) ? $args['std'] : '';

    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
    $html = '<input type="text" class="egt-color-picker" id="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" name="egt_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
    $html .= '<label for="egt_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

    echo $html;
}



/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $args Arguments passed by the setting
 * @return void
 */
function egt_hook_callback( $args ) {
    do_action( 'egt_' . $args['id'] );


    
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function egt_settings_sanitize( $input = array() ) {

    global $egt_settings;

    parse_str( $_POST['_wp_http_referer'], $referrer );

    $output    = array();
    $settings  = egt_get_registered_settings();
    $tab       = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
    $post_data = isset( $_POST[ 'egt_settings_' . $tab ] ) ? $_POST[ 'egt_settings_' . $tab ] : array();

    $input = apply_filters( 'egt_settings_' . $tab . '_sanitize', $post_data );

    // Loop through each setting being saved and pass it through a sanitization filter
    foreach( $input as $key => $value ) {

        // Get the setting type (checkbox, select, etc)
        $type = isset( $settings[ $key ][ 'type' ] ) ? $settings[ $key ][ 'type' ] : false;

        if( $type ) {
            // Field type specific filter
            $output[ $key ] = apply_filters( 'egt_settings_sanitize_' . $type, $value, $key );
        }

        // General filter
        $output[ $key ] = apply_filters( 'egt_settings_sanitize', $value, $key );
    }


    // Loop through the whitelist and unset any that are empty for the tab being saved
    if( ! empty( $settings[ $tab ] ) ) {
        foreach( $settings[ $tab ] as $key => $value ) {

            // settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
            if( is_numeric( $key ) ) {
                $key = $value['id'];
            }

            if( empty( $_POST[ 'egt_settings_' . $tab ][ $key ] ) ) {
                unset( $egt_settings[ $key ] );
            }

        }
    }

    // Merge our new settings with the existing
    $output = array_merge( $egt_settings, $output );

    // @TODO: Get Notices Working in the backend.
    add_settings_error( 'egt-notices', '', __( 'Settings Updated', 'egt' ), 'updated' );

    return $output;

}

/**
 * Sanitize text fields
 *
 * @since  1.0
 * @author Bryan Monzon
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function egt_sanitize_text_field( $input ) {
    return trim( $input );
}
add_filter( 'egt_settings_sanitize_text', 'egt_sanitize_text_field' );

/**
 * Retrieve settings tabs
 * @since  1.0
 * @author Bryan Monzon
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function egt_get_settings_tabs() {

    $settings = egt_get_registered_settings();

    $tabs            = array();
    $tabs['general'] = __( 'General', 'egt' );

    return apply_filters( 'egt_settings_tabs', $tabs );
}
