<?php
/**
 * Uninstall ACF login.
 *
 * @since 0.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'acf_login_field' );
