<?php 
/**
 * Plugin Name: Easy Golf Tournaments
 * Plugin URI: http://wordpress.org/plugins/easy-golf-tournaments
 * Description: Easily plan your golf tournament
 * Version: 1.0
 * Author: WP Setup
 * Author URI: http://wpsetup.co
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'EGT' ) ) :


/**
 * Main EGT Class
 *
 * @since 1.0 */
final class EGT {

  /**
   * @var EGT Instance
   * @since 1.0
   */
  private static $instance;


  /**
   * EGT Instance / Constructor
   *
   * Insures only one instance of EGT exists in memory at any one
   * time & prevents needing to define globals all over the place. 
   * Inspired by and credit to EGT.
   *
   * @since 1.0
   * @static
   * @uses EGT::setup_globals() Setup the globals needed
   * @uses EGT::includes() Include the required files
   * @uses EGT::setup_actions() Setup the hooks and actions
   * @see EGT()
   * @return void
   */
  public static function instance() {
    if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EGT ) ) {
      self::$instance = new EGT;
      self::$instance->setup_constants();
      self::$instance->includes();
      // self::$instance->load_textdomain();
      // use @examples from public vars defined above upon implementation
    }
    return self::$instance;
  }



  /**
   * Setup plugin constants
   * @access private
   * @since 1.0 
   * @return void
   */
  private function setup_constants() {
    // Plugin version
    if ( ! defined( 'EGT_VERSION' ) )
      define( 'EGT_VERSION', '1.0.3' );

    // Plugin Folder Path
    if ( ! defined( 'EGT_PLUGIN_DIR' ) )
      define( 'EGT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

    // Plugin Folder URL
    if ( ! defined( 'EGT_PLUGIN_URL' ) )
      define( 'EGT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

    // Plugin Root File
    if ( ! defined( 'EGT_PLUGIN_FILE' ) )
      define( 'EGT_PLUGIN_FILE', __FILE__ );

    if ( ! defined( 'EGT_DEBUG' ) )
      define ( 'EGT_DEBUG', true );
  }



  /**
   * Include required files
   * @access private
   * @since 1.0
   * @return void
   */
  private function includes() {
    global $egt_settings, $wp_version;

    require_once EGT_PLUGIN_DIR . '/includes/admin/settings/register-settings.php';
    $egt_settings = egt_get_settings();

    // Required Plugin Files
    require_once EGT_PLUGIN_DIR . '/includes/functions.php';
    require_once EGT_PLUGIN_DIR . '/includes/posttypes.php';
    require_once EGT_PLUGIN_DIR . '/includes/scripts.php';
    require_once EGT_PLUGIN_DIR . '/includes/shortcodes.php';

    if( is_admin() ){
        //Admin Required Plugin Files
        
        // require_once EGT_PLUGIN_DIR . '/includes/admin/campaigns/metabox.php';
        require_once EGT_PLUGIN_DIR . '/includes/admin/admin-pages.php';
        require_once EGT_PLUGIN_DIR . '/includes/admin/admin-notices.php';
        require_once EGT_PLUGIN_DIR . '/includes/admin/settings/display-settings.php';

    }

    require_once EGT_PLUGIN_DIR . '/includes/install.php';


  }

} /* end EGT class */
endif; // End if class_exists check


/**
 * Main function for returning EGT Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $sqcash = EGT(); ?>
 *
 * @since 1.0
 * @return object The one true EGT Instance
 */
function EGT() {
  return EGT::instance();
}


/**
 * Initiate
 * Run the EGT() function, which runs the instance of the EGT class.
 */
EGT();



/**
 * Debugging
 * @since 1.0
 */
if ( EGT_DEBUG ) {
  ini_set('display_errors','On');
  error_reporting(E_ALL);
}


