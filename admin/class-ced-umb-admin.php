<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Woocomemrce Fruugo Integration
 * @subpackage Woocomemrce Fruugo Integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    Woocomemrce Fruugo Integration
 * @subpackage Woocomemrce Fruugo Integration/admin
 */
class CED_FRUUGO_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	/**
	 * helper for product management.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      CED_FRUUGO_product_manager    $product_manager    Maintains all single product related functionality.
	 */
	private $product_manager;
	
	/**
	 * helper for plugin admin pages.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      CED_FRUUGO_menu_page_manager    $menu_page_manager    Maintains all this plugin pages related functionality.
	 */
	private $menu_page_manager;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) 
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->load_admin_classes();
		$this->instantiate_admin_classes();
		add_action('wp_ajax_ced_fruugo_select_cat_prof', array($this,'ced_fruugo_select_cat_prof'));
		add_action('wp_ajax_ced_fruugo_select_cat_bulk_upload', array($this,'ced_fruugo_select_cat_bulk_upload'));
		
		add_action('wp_ajax_ced_fruugo_current_product_status', array($this,'ced_fruugo_current_product_status'));

		add_action('wp_ajax_ced_fruugo_import_to_store', array($this,'ced_fruugo_import_to_store'));
		add_action('wp_ajax_ced_fruugo_bulk_import_to_store', array($this,'ced_fruugo_bulk_import_to_store'));
	}

	

	/*
	* get current product status
	*/
	public function ced_fruugo_current_product_status()
	{
		$prodId = isset($_POST['prodId']) ? $_POST['prodId'] : false;
		$marketPlace = isset($_POST['marketplace']) ? $_POST['marketplace'] : false;
		if($prodId && $marketPlace){
		$filePath = CED_FRUUGO_DIRPATH.'marketplaces/'.$marketPlace.'/class-'.$marketPlace.'.php';
			if(file_exists($filePath))
				require_once $filePath;
			
			$class_name = "CED_FRUUGO_manager";
			
			$manager = $class_name :: get_instance();
			$productstatusresponse = $manager->getProductstatus($prodId);
			echo $productstatusresponse;die;
		}

		
	}

	/*
	* Add profile to categories
	*/
	public function ced_fruugo_select_cat_prof()
	{
		global $wpdb;
		
		$catId  = isset($_POST['catId']) ? $_POST['catId'] : "";
		$profId = isset($_POST['profId']) ? $_POST['profId'] : "";
		
		if($profId == "removeProfile")
		{
			$profId = "";
		}
		$getSavedvalues = get_option('ced_fruugo_category_profile', false);
		if(is_array($getSavedvalues) && array_key_exists($catId, $getSavedvalues))
		{
			if($profId == "removeProfile")
			{
				unset($getSavedvalues["$catId"]);
			}
			else{
				$getSavedvalues["$catId"] = $profId;
			}
		}
		else{
			if($profId != "removeProfile")
			{
				$getSavedvalues["$catId"] = $profId;
			}
		}
		
		update_option('ced_fruugo_category_profile', $getSavedvalues);
		
		$table_name = $wpdb->prefix.CED_FRUUGO_PREFIX.'_fruugoprofiles';
		$query = "SELECT `id`, `name` FROM `$table_name` WHERE 1";
		$profiles = $wpdb->get_results($query,'ARRAY_A');

		$profName = __('Profile not selected', 'ced-fruugo');
		
		if(is_array($profiles) && !empty($profiles))
		{
			foreach ($profiles as $profile)
			{
				if($profile['id'] == $profId)
				{
					$profName = $profile['name'];
				}
			}
		}
		
		$tax_query['taxonomy'] = 'product_cat';
		$tax_query['field'] = 'id';
		$tax_query['terms'] = $catId;
		$tax_queries[] = $tax_query;
		$args = array( 'post_type' => 'product', 'posts_per_page' => -1, 'tax_query' => $tax_queries, 'orderby' => 'rand' );
		
		$loop = new WP_Query( $args );
		while ( $loop->have_posts() ) {
			$loop->the_post();
			global $product;
			if(is_wp_error($product))
				return;
			if( WC()->version < '3.0.0' )
			{
				if($product->product_type == 'variable') {
					$variations = $product->get_available_variations();
					if(is_array($variations) && !empty($variations)){
						foreach ($variations as $variation) {
							$var_id = $variation['variation_id'];
							update_post_meta($var_id, "ced_fruugo_profile", $profId);
						}
					}
				}
			}else{
				if($product->get_type() == 'variable') {
					$variations = $product->get_available_variations();
					if(is_array($variations) && !empty($variations)){
						foreach ($variations as $variation) {
							$var_id = $variation['variation_id'];
							update_post_meta($var_id, "ced_fruugo_profile", $profId);
						}
					}
				}
			}
			$product_id = $loop->post->ID;
			$product_title = $loop->post->post_title;
			update_post_meta($product_id, "ced_fruugo_profile", $profId);
		}
		echo json_encode(array('status'=>'success','profile'=> $profName));
		wp_die();
	}
	
	/*
	* Add categories and product for bulk upload
	*/
	function ced_fruugo_select_cat_bulk_upload()
	{
		if(isset($_POST['catId']))
		{
			$products = array();
			$selected_cat = $_POST['catId'];
			$tax_query['taxonomy'] = 'product_cat';
			$tax_query['field'] = 'id';
			$tax_query['terms'] = $selected_cat;
			$tax_queries[] = $tax_query;
			$args = array( 'post_type' => 'product', 'posts_per_page' => -1, 'tax_query' => $tax_queries, 'orderby' => 'rand' );
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) : $loop->the_post(); global $product;
			
			$product_id = $loop->post->ID;
			$product_title = $loop->post->post_title;
			$products[$product_id] = $product_title;
			endwhile;
			
			$response['data'] = $products;
			$response['result'] = 'success';
			
			echo json_encode($response);
			die;
			
		}	
	}
	
	/**
	 * Including all admin related classes.
	 * 
	 * @since 1.0.0
	 */
	private function load_admin_classes(){
		
		$classes_names = array(
			'admin/helper/class-product-fields.php',
			'admin/helper/class-menu-page-manager.php',
			'admin/helper/class-order-manager.php',
			'admin/helper/class-ced-umb-extended-manager.php'
		);
		
		foreach( $classes_names as $class_name ){
			require_once CED_FRUUGO_DIRPATH . $class_name;
		}
		
		$activated_marketplaces = ced_fruugo_available_marketplace();
		if(is_array($activated_marketplaces)):
			foreach($activated_marketplaces as $marketplace_name){
				$file_path = CED_FRUUGO_DIRPATH.'marketplaces/'.$marketplace_name.'/class-'.$marketplace_name.'.php';
				if(file_exists($file_path))
					require_once $file_path;
			}
		endif;
	}
	
	/**
	 * storing instance of admin related functionality classes.
	 * 
	 * @since 1.0.0 
	 */
	private function instantiate_admin_classes(){
		
		if( class_exists( 'CED_FRUUGO_product_fields' ) )
			$this->product_fields = CED_FRUUGO_product_fields::get_instance();
		
		if( class_exists( 'CED_FRUUGO_menu_page_manager' ) )
			$this->menu_page_manager = CED_FRUUGO_menu_page_manager::get_instance();
		
		if( class_exists( 'CED_FRUUGO_order_manager' ) )
			$this->order_manager = CED_FRUUGO_order_manager::get_instance();
		
		// creating instances of activated marketplaces classes.

		$activated_marketplaces = ced_fruugo_available_marketplace();
		if(is_array($activated_marketplaces)):
			foreach($activated_marketplaces as $marketplace){
				$class_name = 'CED_FRUUGO_manager';
				if(class_exists($class_name))
					new $class_name();
			}
		endif;
	}
	
	/**
	 * Returns all the admin hooks.
	 * 
	 * @since 1.0.0
	 * @return array admin_hook_data.
	 */
	public function get_admin_hooks()
	{
		$admin_actions = array(
				array(
						'type'	=>	'action',
						'action' => 'woocommerce_product_data_tabs',
						'instance' => $this->product_fields,
						'priority' => '09',
						'function_name' => 'umb_required_fields_tab'
				),
				array(
						'type'	=>	'action',
						'action' => 'woocommerce_process_product_meta',
						'instance' => $this->product_fields,
						'function_name' => 'umb_required_fields_process_meta'
				),
				array(
						'type'	=>	'action',
						'action' => 'admin_menu',
						'instance' => $this->menu_page_manager,
						'function_name' => 'create_pages',
						'priority' => 11,
				),
				array(
						'type'	=>	'action',
						'action' => 'save_post',
						'instance' => $this->product_fields,
						'function_name' => 'quick_edit_save_data',
						'priority' => 10,
						'accepted_args' => 2
				),
				array(
						'type'	=>	'action',
						'action' => 'add_meta_boxes',
						'instance' => $this->order_manager,
						'function_name' => 'add_meta_boxes',
						'priority' => 55
				),
				array(
						'type'	=>	'action',
						'action' => 'admin_enqueue_scripts',
						'instance' => $this->order_manager,
						'function_name' => 'enqueue_scripts',
				),
				array(
						'type'	=>	'action',
						'action' => 'woocommerce_product_after_variable_attributes',
						'instance' => $this->product_fields,
						'function_name' => 'umb_render_product_fields_html_for_variations',
						'priority' => '10',
						'accepted_args' => 3
				),
				array(
						'type'	=>	'action',
						'action' => 'woocommerce_save_product_variation',
						'instance' => $this->product_fields,
						'function_name' => 'umb_required_fields_process_meta',
				),
				array(
						'type'	=>	'action',
						'action' => 'wp_ajax_ced_fruugo_save_profile',
						'instance' => $this,
						'function_name' => 'ced_fruugo_save_profile',
				),
				array(
						'type'	=>	'action',
						'action' => 'wp_ajax_ced_fruugo_end_auction',
						'instance' => $this,
						'function_name' => 'ced_fruugo_end_auction',
				)
		); 
		
		return apply_filters( 'ced_fruugo_admin_actions', $admin_actions );
	}

	
	/**
	 * save assigned profile to the product.
	 * 
	 * @since 1.0.0
	 */
	public function ced_fruugo_save_profile()
	{
		$prodId    = isset($_POST['proId']) ? $_POST['proId'] : "";
		$profileId = isset($_POST['profileId']) ? $_POST['profileId'] : "";
		$_product = wc_get_product( $prodId );
		if(is_wp_error($_product))
			return;
		if( WC()->version < '3.0.0' ){
			if($_product->product_type == 'variable') {
				
				$variations = $_product->get_available_variations();
				if(is_array($variations) && !empty($variations)){
					foreach ($variations as $variation) {
						$var_id = $variation['variation_id'];
						update_post_meta($var_id, "ced_fruugo_profile", $profileId);
					}
				}
			}
		}else{
			if($_product->get_type() == 'variable') {
				
				$variations = $_product->get_available_variations();
				if(is_array($variations) && !empty($variations)){
					foreach ($variations as $variation) {
						$var_id = $variation['variation_id'];
						update_post_meta($var_id, "ced_fruugo_profile", $profileId);
					}
				}
			}
		}
		update_post_meta($prodId, "ced_fruugo_profile", $profileId);
		$ced_fruugo_profile = get_post_meta($prodId, "ced_fruugo_profile", true);
		if($ced_fruugo_profile == $profileId) {
			echo "success";
		}
		else {
			echo "fail";
		}
		wp_die();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		if( $screen_id == 'toplevel_page_umb-main' || $screen_id == 'product' )
			wp_enqueue_style( $this->plugin_name.'config_style', plugin_dir_url( __FILE__ ) . 'css/ced_fruugo_config_style.css', array(), $this->version, 'all' );
		
		wp_enqueue_style( $this->plugin_name.'common_style', plugin_dir_url( __FILE__ ) . 'css/common_style.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'config_style', plugin_dir_url( __FILE__ ) . 'css/ced_fruugo_config_style.css', array(), $this->version, 'all' );
		
		
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		
		if($screen_id=="umb_page_umb-fruugo-fileStatus"){
			$activated_marketplaces = ced_fruugo_available_marketplace();
			if(is_array($activated_marketplaces)){
				foreach($activated_marketplaces as $marketplace){
					$handle = 'umb_'.$marketplace.'_fileStatus_script';
					wp_enqueue_script($handle, CED_FRUUGO_URL .'marketplaces/'.$marketplace. '/js/fileStatus.js', array( 'jquery' ), $this->version, false );
					wp_localize_script( $handle, $marketplace.'_action_handler', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
				}
			}
		}
		
		if( $screen_id == 'toplevel_page_umb-main' || $screen_id == 'product' )
		{	
			wp_enqueue_script( $this->plugin_name.'config_script', plugin_dir_url( __FILE__ ) . 'js/ced_fruugo_config.js', array( 'jquery' ), $this->version, false );
			$activated_marketplaces = ced_fruugo_available_marketplace();
			if(is_array($activated_marketplaces)){
				foreach($activated_marketplaces as $marketplace){
					$handle = 'umb_'.$marketplace.'_configuration_script';
					wp_localize_script( $handle, $marketplace.'_action_handler', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
				}
			}
		}	
		//echo  $screen_id;die;
		if( $screen_id == 'fruugo_page_umb-fruugo-pro-mgmt'){
			wp_enqueue_script( $this->plugin_name.'quick_edit', plugin_dir_url( __FILE__ ) . 'js/ced_fruugo_quick_edit.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( $this->plugin_name.'profile', plugin_dir_url( __FILE__ ) . 'js/ced_fruugo_profile.js', array( 'jquery' ), $this->version, false );
			wp_localize_script( $this->plugin_name.'profile', 'profile_action_handler', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
		if( $screen_id == 'toplevel_page_umb-fruugo-dashboard' )
		{ 
			wp_enqueue_script( 'ced_fruugo_gstatic_js', 'https://www.gstatic.com/charts/loader.js',array(),'1.0.0',true);
			wp_enqueue_script( 'ced_fruugo_dashboard_js', CED_FRUUGO_URL.'/admin/js/dashboard.js', array('jquery'), '1.0', false );
		}
		wp_enqueue_script( $this->plugin_name.'profile', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name.'common_script', plugin_dir_url( __FILE__ ) . 'js/common_script.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name.'common_script', 'common_action_handler', array( 'ajax_url' => admin_url( 'admin-ajax.php' ),'plugin_url'=> CED_FRUUGO_URL ) );
	}

}
