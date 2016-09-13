<?php if(!defined('ABSPATH')) die('Fatal Error');
/*
Plugin Name: Contact Express - Autofill Contact Forms
Plugin URI: http://github.com/ralphjesy12
Description: A Contact Form Web AutoFiller
Author: ralphjesy@gmail.com
Version: 1.0
Author URI: http://github.com/ralphjesy12
*/
define( 'CONTACTXP_VERSION', '1.0' );
define( 'CONTACTXP_MIN_WP_VERSION', '4.4' );
define( 'CONTACTXP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CONTACTXP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CONTACTXP_DEBUG' , true );
define( 'CONTACTXP_DEBUG_LOG' , true );

require_once( CONTACTXP_PLUGIN_DIR . 'vendor/autoload.php');
require_once( CONTACTXP_PLUGIN_DIR . 'lib/class.contactxpress.php');
// require_once( CONTACTXP_PLUGIN_DIR . 'lib/class.domainlist.php');
require_once( CONTACTXP_PLUGIN_DIR . 'lib/class.plugin.php');

if(class_exists('CONTACTXP')){

    register_activation_hook( __FILE__ , [ 'CONTACTXP' , 'activate' ] );
    register_deactivation_hook( __FILE__ , [ 'CONTACTXP' , 'deactivate' ] );
    $CONTACTXP = new CONTACTXP();

}
