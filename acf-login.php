<?php
/**
 * @package AcfLogin
 */

/**
Plugin Name: ACF: Login
Plugin URI: https://samaphp.com/acf-login
Description: Login by a custom field using the (Advanced custom fields) plugin.
Version: 1.0.0
Author: Saud bin Mohammed
Author URI: https://samaphp.com
Text Domain: acf-login
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define( 'ACF_LOGIN__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ACF_LOGIN__PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Deactivate this plugin if the required plugin (AFC) is not activated yet.
if ( !function_exists( 'acf_login_require_acf_plugin' ) ) {
    add_action( 'admin_init', 'acf_login_require_acf_plugin' );
    function acf_login_require_acf_plugin() {
        if ( is_admin() && current_user_can( 'activate_plugins' ) && !is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
            add_action( 'admin_notices', 'acf_login_require_acf_plugin_notice' );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }
        }
    }
    function acf_login_require_acf_plugin_notice(){
        ?><div class="error"><p>Sorry, but in order to make (Advanced Custom Fields: Login) to work, you need to download and activate <a href="https://wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields</a> plugin.</p></div><?php
    }
}

if ( !class_exists( 'ACF_Login' ) ) {
    require_once(ACF_LOGIN__PLUGIN_DIR . 'inc/acf-login-helper-functions.php');
    require_once(ACF_LOGIN__PLUGIN_DIR . 'class.acf-login.php');

    add_action('init', array('ACF_Login', 'init'));

    if ( is_admin() ) {
        if ( !class_exists( 'ACF_Login_Admin' ) ) {
            require_once(ACF_LOGIN__PLUGIN_DIR . 'class.acf-login-admin.php');
            add_action('init', array('ACF_Login_Admin', 'init'));
        }
    }
}
