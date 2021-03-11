<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * plugin admin pages related functionality helper class.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce fruugo Integration
 * @subpackage Woocommerce fruugo Integration/admin/helper
 */

if( !class_exists( 'CED_FRUUGO_menu_page_manager' ) ) :

/**
 * Admin pages related functionality.
 *
 * Manage all admin pages related functionality of this plugin.
 *
 * @since      1.0.0
 * @package    Woocommerce fruugo Integration
 * @subpackage Woocommerce fruugo Integration/admin/helper
 * @author     CedCommerce <cedcommerce.com>
 */
class CED_FRUUGO_menu_page_manager{
	
	/**
	 * The Instace of CED_FRUUGO_menu_page_manager.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      $_instance   The Instance of CED_FRUUGO_menu_page_manager class.
	 */
	private static $_instance;
	
	/**
	 * CED_FRUUGO_menu_page_manager Instance.
	 *
	 * Ensures only one instance of CED_FRUUGO_menu_page_manager is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return CED_FRUUGO_menu_page_manager instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Creating admin pages of Woocommerce fruugo Integration.
	 * 
	 * @since 1.0.0
	 */
	public function create_pages(){

		add_menu_page('fruugo', 'Fruugo', __('manage_woocommerce','ced-fruugo'), 'umb-fruugo-main', array( $this, 'ced_fruugo_marketplace_page' ),'', 60 );
		
		add_submenu_page('umb-fruugo-main', __('Configuration','ced-fruugo'), __('Configuration','ced-fruugo'), 'manage_woocommerce', 'umb-fruugo-main', array( $this, 'ced_fruugo_marketplace_page' ) );

		// add_submenu_page('umb-fruugo-main', __('Shop Settings','ced-fruugo'), __('Shop Settings','ced-fruugo'), 'manage_woocommerce', 'umb-fruugo-shop-settings', array( $this, 'ced_fruugo_shop_settings_page' ) );
		
		add_submenu_page('umb-fruugo-main', __('Category Mapping','ced-fruugo'), __('Category Mapping','ced-fruugo'), 'manage_woocommerce', 'umb-fruugo-cat-map', array( $this, 'ced_fruugo_category_map_page' ) );
		
		add_submenu_page('umb-fruugo-main', __('Profile','ced-fruugo'), __('Profile','ced-fruugo'), 'manage_woocommerce', 'umb-fruugo-profile', array( $this, 'ced_fruugo_profile_page' ) );
		
		add_submenu_page('umb-fruugo-main', __('Manage Products','ced-fruugo'), __('Manage Products','ced-fruugo'), 'manage_woocommerce', "umb-fruugo-pro-mgmt", array( $this, 'ced_fruugo_pro_mgmt_page' ) );
		
		add_submenu_page('umb-fruugo-main', __('Bulk Action','ced-fruugo'), __('Bulk Action','ced-fruugo'), 'manage_woocommerce', "umb-fruugo-bulk-action", array( $this, 'ced_fruugo_bulk_action' ) );
		
		add_submenu_page('umb-fruugo-main', __('Orders','ced-fruugo'), __('Orders','ced-fruugo'), 'manage_woocommerce', 'umb-fruugo-orders', array( $this, 'ced_fruugo_orders_page' ) );
		
		add_submenu_page('umb-fruugo-main', __('OTHERS','ced-fruugo'), __('OTHERS','ced-fruugo'), 'manage_woocommerce', 'umb-fruugo-auto_acknowledge', array( $this, 'ced_fruugo_auto_acknowledge_page' ) );
		
		// add_submenu_page('umb-fruugo-main', __('Prerequisite','ced-fruugo'), __('Prerequisite','ced-fruugo'), 'manage_woocommerce', 'umb-fruugo-prerequisites', array( $this, 'ced_fruugo_prerequisite_page' ) );
		
		
	}
	
	/**
	 * fruugo Dashboard
	 * 
	 * @since 1.0.0
	*/
	public function ced_fruugo_marketplace_dashboard()
	{
		$fruugo_license = get_option('ced_fruugo_lincense',false);
		$fruugo_license_key = get_option('ced_fruugo_lincense_key',false);
		$fruugo_license_module = get_option('ced_fruugo_lincense_module',false);
		$license_valid = apply_filters("ced_fruugo_license_check", false);
		if( $license_valid )
		{
			require_once CED_FRUUGO_DIRPATH.'admin/pages/header.php';
			require_once CED_FRUUGO_DIRPATH.'admin/pages/ced-fruugo-dashboard.php';
		}
		else{
			require_once CED_FRUUGO_DIRPATH.'admin/pages/header.php';
			do_action("ced_fruugo_license_panel");
		}
	}

	/**
	 * Upload product in Bulk
	 * 
	 * @since 1.0.0
	 */
	
	public function ced_fruugo_bulk_action(){
		require_once CED_FRUUGO_DIRPATH.'admin/pages/bulk-action.php';
	}

	/**
	 * Product Description Template
	 * 
	 * @since 1.0.0
	 */
	
	public function ced_fruugo_description_template(){
		require_once CED_FRUUGO_DIRPATH.'admin/pages/ced-fruugo-description-template.php';
	}

	
	/**
	 * Auto Acknowledge page.
	 *
	 * @since 1.0.0
	 */
	public function ced_fruugo_auto_acknowledge_page(){
		require_once CED_FRUUGO_DIRPATH.'admin/pages/auto_acknowledge.php';
	}
	
	/**
	 * file status page.
	 * 
	 * @since 1.0.0
	 */
	public function ced_fruugo_file_status_page(){
		
		require_once CED_FRUUGO_DIRPATH.'admin/pages/fileStatus.php';
	}
	
	/**
	 * Marketplaces page.
	 * 
	 * @since 1.0.0
	 */
	public function ced_fruugo_marketplace_page()
	{
		
		$fruugo_license = get_option('ced_fruugo_lincense',false);
		$fruugo_license_key = get_option('ced_fruugo_lincense_key',false);
		$fruugo_license_module = get_option('ced_fruugo_lincense_module',false);
		$license_valid = apply_filters("ced_fruugo_license_check", false);

		// if( $license_valid )
		// {
			require_once CED_FRUUGO_DIRPATH.'admin/pages/marketplaces.php';
		// }
		// else
		// {
		// 	require_once CED_FRUUGO_DIRPATH.'admin/pages/header.php';
		// 	do_action("ced_fruugo_license_panel");
		// }
	}

	/**
	 * Category mapping page panel.
	 * 
	 *  @since 1.0.0
	 */
	public function ced_fruugo_category_map_page(){
		
		require_once CED_FRUUGO_DIRPATH.'admin/pages/category_mapping.php';
	}
	
	/**
	 * Products management page panel.
	 *
	 *  @since 1.0.0
	 */
	public function ced_fruugo_pro_mgmt_page(){
	
		require_once CED_FRUUGO_DIRPATH.'admin/pages/manage_products.php';
	}
	
	/**
	 * Profile page for easy product uploading.
	 * 
	 * @since 1.0.0
	 */
	public function ced_fruugo_profile_page(){
		
		require_once CED_FRUUGO_DIRPATH.'admin/pages/profile.php';
	}
	
	/**
	 * Orders page.
	 * 
	 * @since 1.0.0
	 */
	public function ced_fruugo_orders_page(){
		
		require_once CED_FRUUGO_DIRPATH.'admin/pages/orders.php';
	}
	
	/**
	 * prerequisite page.
	 *
	 * @since 1.0.0
	 */
	public function ced_fruugo_prerequisite_page(){
		require_once CED_FRUUGO_DIRPATH.'admin/pages/prerequisite.php';
	}

}

endif;