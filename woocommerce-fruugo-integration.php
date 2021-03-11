<?php
/**
 * CedCommerce NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @since             1.0.0
 * @package           woocommerce-fruugo-integration
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Fruugo Integration
 * Description:       Configure Your Woocommerce Store to the fruugo store and sell your products easily.
 * Version:           1.0.1
 * Author:            CedCommerce <cedcommerce.com>
 * Author URI:        cedcommerce.com
 * Text Domain:       ced-fruugo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ced-umb-activator.php
 * @name activate_ced_fruggo
 * @author CedCommerce
 * @since 1.0.0
 */

	function activate_woocommerce_fruugo_integration() {

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-ced-umb-activator.php';
		CED_FRUUGO_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-ced-umb-deactivator.php
	 * @name deactivate_ced_fruggo
	 * @author CedCommerce
	 * @since 1.0.0
	 */
	function deactivate_woocommerce_fruugo_integration() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-ced-umb-deactivator.php';
		CED_FRUUGO_Deactivator::deactivate();
	}
	register_activation_hook( __FILE__, 'activate_woocommerce_fruugo_integration' );
	register_deactivation_hook( __FILE__, 'deactivate_woocommerce_fruugo_integration' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-ced-umb.php';

	/**
	* This file includes core functions to be used globally in plugin.
	* @author CedCommerce <plugins@cedcommerce.com>
	* @link  http://www.cedcommerce.com/
	*/
	require_once plugin_dir_path(__FILE__).'includes/ced_umb_core_functions.php';


	add_action('ced_fruugo_scheduled_mail', 'ced_woocommerce_fruugo_integration_scheduled_process');
	/**
	 * function to handle scheduled process
	 * @name ced_fruugo_scheduled_process
	 * @author CedCommerce
	 * 
	 */
	function ced_woocommerce_fruugo_integration_scheduled_process()
	{
		do_action('ced_fruugo_track_schedule');
	}


	/**
	 * Check WooCommerce is Installed and Active.
	 *
	 * since Woocommerce fruugo Integration is extension for WooCommerce it's necessary,
	 * to check that WooCommerce is installed and activated or not,
	 * if yes allow extension to execute functionalities and if not
	 * let deactivate the extension and show the notice to admin.
	 * 
	 * @author CedCommerce
	 */
	if(ced_fruugo_check_woocommerce_active()){

		run_ced_umb_fruugo();
	}else{

		add_action( 'admin_init', 'deactivate_ced_fruugo_woo_missing' );
	}

?>