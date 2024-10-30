<?php
/*
Plugin Name:    Content Magnets
Version:    0.0.3.1
Plugin URI: https://contentmagnets.com/
Tags: email capture, lead capture, content upgrade, lead magnets, lead magnet plugin wordpress, wordpress lead magnet plugin, content upgrade plugin, wordpress content upgrade plugin, email list, mailchimp, aweber, campaign monitor, constant contact, madmimi, infusionsoft, getresponse, hubspot, marketo, activecampaign, pardot, totalsend, emma, icontact, mailerlite, mailpoet, optin forms, subscribers, optin form, wordpress optin form, sidebar optin form, sidebar optin, sidebar form, after post optin form, wordpress after post optin form, after post optin form plugin, mobile optin forms, mobile optins, wordpress mobile optin forms, lead gen, lead generation, wordpress lead generation, lead generation wordpress, wordpress lead gen
Description:    Create a lead magnet right form within your WordPress editor without ever having to touch code. It's never been easier to create a library of subscriber-attracting lead magnets and content upgrades from within WordPress.
Author: Content Magnets
Author URI: https://contentmagnets.com/
Requires at least: 4.0
Tested up to: 5.4
Requires PHP: 5.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

// Our prefix is CMG / cmg
function cmg_is_plugin_active( $plugin ) {
    return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
}
if ( cmg_is_plugin_active( 'content-magnets-pro/content-magnets-pro.php' ) )
    return;

define( 'CMGVersion', '0.0.3.0' );
define( 'CMG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CMG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CMG_PROTECTION_H', plugin_basename(__FILE__) );
define( 'CMG_NAME', 'content-magnet' );

define( 'CMG_PAGE_LINK', 'content-magnet' );

if ( !function_exists('cmg_print') ){
    function cmg_print( $array ) {
        echo '<pre>'; print_r( $array ); echo '</pre>';
    }
}
if ( !function_exists('isset_return') ) {
    function isset_return( $array, $key,  $default = '' ){
        if(isset( $array[$key]) )
            return $array[$key];

        return $default;
    }
}
if ( !function_exists('cmg_asort') ) {
    function cmg_asort(&$array) {
        foreach ($array as &$value) {
            if (is_array($value)) cmg_asort($value);
        }
        asort($array);
        return $array;
    }
}

require_once( CMG_PLUGIN_DIR . 'lib/custom_post_types.php' );
require_once( CMG_PLUGIN_DIR . 'lib/class.cmg-routes.php' ); // API Endpoints
require_once( CMG_PLUGIN_DIR . 'lib/class.cmg-cfield.php' );
require_once( CMG_PLUGIN_DIR . 'lib/class.cmg.php' );
require_once( CMG_PLUGIN_DIR . 'lib/class.cmg-shortcodes.php' );

register_activation_hook( __FILE__, array( 'CMG', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'CMG', 'plugin_deactivation' ) );

add_action( 'init', array( 'CMG', 'init' ) );

if ( is_admin() ) {
    require_once( CMG_PLUGIN_DIR . 'lib/class.cmg-admin.php' );
    add_action( 'init', array( 'CMG_Admin', 'init' ) );
}

