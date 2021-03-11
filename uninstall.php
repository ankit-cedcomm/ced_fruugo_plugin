<?php

/**
 *
 * @link       http://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Woocommerce fruugo Integration
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
wp_clear_scheduled_hook( 'ced_fruugo_scheduled_mail' );
?>