<?php
session_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds extended functionality as needed in core plugin.
 *
 * @class    CED_FRUUGO_Extended_Manager
 * @version  1.0.0
 * @category Class
 * @author   CedCommerce
 */

class CED_FRUUGO_Extended_Manager {

	public function __construct() {
		$this->ced_fruugo_extended_manager_add_hooks_and_filters();
	}
	
	/**
	 * This function hooks into all filters and actions available in core plugin.
	 * @name ced_fruugo_extended_manager_add_hooks_and_filters()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_fruugo_extended_manager_add_hooks_and_filters() {
		add_action('admin_enqueue_scripts',array($this,'ced_fruugo_extended_manager_admin_enqueue_scripts'));
		add_action('wp_ajax_fetch_all_meta_keys_related_to_selected_product', array($this,'fetch_all_meta_keys_related_to_selected_product'));
		add_action('wp_ajax_ced_fruugo_searchProductAjaxify', array($this,'ced_fruugo_searchProductAjaxify'));

		/* CSV Functionality */
		add_action('init',array($this,'ced_fruugo_csv_import_export_module_export_csv_format'));
		add_action( 'wp_ajax_ced_fruugo_csv_import_export_module_read_csv', array($this,'ced_fruugo_csv_import_export_module_read_csv' ));

		add_action( 'wp_ajax_do_marketplace_folder_update', array($this,'do_marketplace_folder_update' ));

		
		add_action( 'wp_ajax_ced_fruugo_updateMetaKeysInDBForProfile', array($this,'ced_fruugo_updateMetaKeysInDBForProfile' ));

		//adding cron timing
		add_filter('cron_schedules',array($this,'my_cron_schedules'));

		/*
		* Queue Upload AJAX Request Handling
		*/
		add_action( 'wp_ajax_ced_fruugo_render_queue_upload_main_section', array($this,'ced_fruugo_render_queue_upload_main_section' ));
		add_action( 'wp_ajax_ced_fruugo_add_product_to_upload_queue_on_marketplace', array($this,'ced_fruugo_add_product_to_upload_queue_on_marketplace' ));
		add_action( 'wp_ajax_ced_fruugo_marketplace_allow_split_variation', array($this,'ced_fruugo_marketplace_allow_split_variation' ));

	}
	
	/*
	* Upload Your Queue html and processing
	*/
	function ced_fruugo_render_queue_upload_main_section() {
		$selectedMarketPlace = isset($_POST['marketplaceId'])?$_POST['marketplaceId']:'';
		if( $selectedMarketPlace ) {
			$items_in_queue = get_option( 'ced_fruugo_'.$selectedMarketPlace.'_upload_queue', array() );
			$items_count = count($items_in_queue);
			$ced_fruugo_delete_queue_after_upload = get_option( 'ced_fruugo_delete_queue_after_upload_'.$selectedMarketPlace, 'no' );
			if( $ced_fruugo_delete_queue_after_upload == 'yes' ) {
				$ced_fruugo_delete_queue_after_upload = 'checked="checked"';
			}
			else {
				$ced_fruugo_delete_queue_after_upload = '';
			}	
			?>
			<div class="ced_fruugo_queue_upload_main_section">
				<h3 class="ced_fruugo_white_txt"><?php echo __('There are ','ced-fruugo').$items_count.__(' items in your queue to upload.','ced-fruugo'); ?></h3>
				<h4 class="ced_fruugo_white_txt">
					<input type="checkbox" name="ced_fruugo_delete_queue_after_upload" id="ced_fruugo_delete_queue_after_upload" <?php echo $ced_fruugo_delete_queue_after_upload;?> >
					<label for="ced_fruugo_delete_queue_after_upload"><?php echo __('Delete Queue After Uplaod.','ced-fruugo'); ?></label>
				</h4>
				<p>
					<input type="submit" name="ced_fruugo_queue_upload_button" class="button button-ced_fruggo" value="<?php _e( 'Upload', 'ced-fruugo' ); ?>">
				</p>
			</div>
			<?php
		}
		wp_die();
	}

	/*
	* Adding Product to upload queue
	*/
	function ced_fruugo_add_product_to_upload_queue_on_marketplace() {
		$marketplaceId = isset($_POST['marketplaceId'])?$_POST['marketplaceId']:'';
		$items_in_queue = get_option( 'ced_fruugo_'.$marketplaceId.'_upload_queue', array() );
		$productId = isset($_POST['productId'])?$_POST['productId']:'';
		if( in_array($productId, $items_in_queue)) {
			unset($items_in_queue[$productId]);
		}
		else {
			$items_in_queue[$productId] = $productId;
		}
		update_option( 'ced_fruugo_'.$marketplaceId.'_upload_queue', $items_in_queue );
		wp_die();
	}

	function ced_fruugo_marketplace_allow_split_variation() {
		$marketplaceId = isset($_POST['marketplaceId'])?$_POST['marketplaceId']:'';
		$productId = isset($_POST['productId']) ? $_POST['productId'] : '';
		$already = get_post_meta( $productId , 'ced_fruugo_allow_split_variation' , true );
		if( $already == 'yes' ){
			update_post_meta( $productId, 'ced_fruugo_allow_split_variation' , 'no' );
		}else{
			update_post_meta( $productId, 'ced_fruugo_allow_split_variation' , 'yes' );
		}
		wp_die();
	}

	function ced_fruugo_updateMetaKeysInDBForProfile() {
		$metaKey 	=	 $_POST['metaKey'];
		$actionToDo 	=	 $_POST['actionToDo'];
		$allMetaKeys = get_option('CedUmbProfileSelectedMetaKeys', array());
		if($actionToDo == 'append') {
			if(!in_array($metaKey, $allMetaKeys)){
				$allMetaKeys[] = $metaKey;
			}
		}
		else{
			
			if(in_array($metaKey, $allMetaKeys)){
				if(($key = array_search($metaKey, $allMetaKeys)) !== false) {
					unset($allMetaKeys[$key]);
				}
			}
		}
		update_option('CedUmbProfileSelectedMetaKeys', $allMetaKeys);
		wp_die();
		
	}

	function my_cron_schedules($schedules){
		if(!isset($schedules["ced_fruugo_6min"])){
			$schedules["ced_fruugo_6min"] = array(
				'interval' => 10,
				'display' => __('Once every 6 minutes'));
		}
		if(!isset($schedules["ced_fruugo_10min"])) {
			$schedules["ced_fruugo_10min"] = array(
				'interval' => 10*60,
				'display' => __('Once every 10 minutes'));
		}
		if(!isset($schedules["ced_fruugo_15min"])){
			$schedules["ced_fruugo_15min"] = array(
				'interval' => 15*60,
				'display' => __('Once every 15 minutes'));
		}
		if(!isset($schedules["ced_fruugo_30min"])){
			$schedules["ced_fruugo_30min"] = array(
				'interval' => 30*60,
				'display' => __('Once every 30 minutes'));
		}
		return $schedules;
	}


	function do_marketplace_folder_update(){
		$marketplaceId = isset($_POST['marketplaceId']) ? $_POST['marketplaceId'] : '';
		if( $marketplaceId == ''){
			return;
		}
		//echo $marketplaceId;die;
		$default_headers = array(
			'MarketPlace' => 'MarketPlace',
			'Version' => 'Version',
			'Description' => 'Description',
				// Site Wide Only is deprecated in favor of Network.
		);
		$packageDir = WP_PLUGIN_DIR."/woocommerce-fruugo-integration/marketplaces/$marketplaceId/class-$marketplaceId.php";
		$allheader = ced_fruugo_get_package_header_data($packageDir, $default_headers);
		$referer = $_SERVER['HTTP_HOST'];
		$requestUrl = "http://demo.cedcommerce.com/woocommerce/update_notifications/marketplaces/".$marketplaceId."/update.php";
		//echo $requestUrl;die;
		$headers = [];
		$headers[] = "REFERER:$referer";
		$headers[] = "ACTION:update";
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $requestUrl );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_HEADER, 1 );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $allheader );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$server_output = curl_exec ( $ch );
		$header_size = curl_getinfo ( $ch, CURLINFO_HEADER_SIZE );
		$header = substr ( $server_output, 0, $header_size );
		$response = substr ( $server_output, $header_size );
		$response = json_decode($response);
		$error_number =  curl_errno($ch);
		curl_close ( $ch );
		if ($error_number > 0) {
			return curl_error ( $ch );
		}
		if($response->status == "200"){
			$file = $response->url;
			$newfile = WP_PLUGIN_DIR."/woocommerce-fruugo-integration/admin/temp/tmp_file.zip";
			if (!copy($file, $newfile)) {
				echo "103";die;
			}
			$rootPath =  WP_PLUGIN_DIR."/woocommerce-fruugo-integration/marketplaces/";
			$zip = new ZipArchive;
			if ($zip->open($newfile) === TRUE)
			{
				if($zip->extractTo($rootPath)){
					$zip->close();
					unlink($newfile);
					echo '200';die;
				}
			} else
			{
				echo '101';die;
			}
		}
		elseif($response->status == "100"){
			echo '100';die;
		}
		else{
			echo "102";die;
		}
		/** Do Marketplace Update Code Here **/
		wp_die();
	}

	/*
	* Search product on manage product page
	*/
	function ced_fruugo_searchProductAjaxify( $x='',$post_types = array( 'product' ) ) {
		global $wpdb;
		
		ob_start();
		
		$term = (string) wc_clean( stripslashes( $_POST['term'] ) );
		if ( empty( $term ) ) {
			die();
		}
		
		$like_term = '%' . $wpdb->esc_like( $term ) . '%';
		
		if ( is_numeric( $term ) ) {
			$query = $wpdb->prepare( "
				SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
				WHERE posts.post_status = 'publish'
				AND (
				posts.post_parent = %s
				OR posts.ID = %s
				OR posts.post_title LIKE %s
				OR (
				postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
				)
				)
				", $term, $term, $term, $like_term );
		} else {
			$query = $wpdb->prepare( "
				SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
				WHERE posts.post_status = 'publish'
				AND (
				posts.post_title LIKE %s
				or posts.post_content LIKE %s
				OR (
				postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
				)
				)
				", $like_term, $like_term, $like_term );
		}
		
		$query .= " AND posts.post_type IN ('" . implode( "','", array_map( 'esc_sql', $post_types ) ) . "')";
		
		$posts = array_unique( $wpdb->get_col( $query ) );
		$found_products = array();
		
		global $product;
		
		$proHTML = '';
		if ( ! empty( $posts ) ) {
			$proHTML .= '<table class="wp-list-table fixed striped" id="ced_fruugo_products_matched">';
			foreach ( $posts as $post ) {
				$product = wc_get_product( $post );
				if(WC()->version < "3.0.0")
				{
					if( $product->product_type == 'variable' ) {
						$variations = $product->get_available_variations();
						foreach ($variations as $variation) {
							$proHTML .= '<tr><td product-id="'.$variation['variation_id'].'">'.get_the_title( $variation['variation_id'] ).'</td></tr>';
						}
					}
					else{
						$proHTML .= '<tr><td product-id="'.$post.'">'.get_the_title( $post ).'</td></tr>';
					}
				}else{
					if( $product->get_type() == 'variable' ) {
						$variations = $product->get_available_variations();
						foreach ($variations as $variation) {
							$proHTML .= '<tr><td product-id="'.$variation['variation_id'].'">'.get_the_title( $variation['variation_id'] ).'</td></tr>';
						}
					}
					else{
						$proHTML .= '<tr><td product-id="'.$post.'">'.get_the_title( $post ).'</td></tr>';
					}
				}
			}
			$proHTML .= '</table>';
		}
		else {
			$proHTML .= '<ul class="woocommerce-error ccas_searched_product_ul"><li class="ccas_searched_pro_list"><strong>No Matches Found</strong><br/></li></ul>';
		}	
		echo $proHTML;
		wp_die();
	}


	/**
	 * This function exports the format of wholelsale-market-csv
	 * @name ced_fruugo_csv_import_export_module_export_csv_format()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function ced_fruugo_csv_import_export_module_export_csv_format() {
	
        	$csvHeaderArray = array(

        		"SkuId",
        		"ProductId",
        		"Title",
        		"StockQuantity",
        		"Description",
        		"NormalPriceWithoutVAT",
        		"EAN",
        		"Brand",
        		"Category",
        		"Imageurl1",
        		"VATRate",
        		"Language",
        		"AttributeSize",
        		"AttributeColor",
        		"Attribute1",
        		"Attribute2",
        		"Attribute3",
        		"Attribute4",
        		"Attribute5",
        		"Attribute6",
        		"Attribute7",
        		"Attribute8",
        		"Attribute9",
        		"Attribute10",
        		"Currency",
        		"LeadTime",
        		"PackageWeight",
        		"DiscountPriceWithoutVAT",
        		"StockStatus",
        		"Imageurl2",
        		"Imageurl3",
        		"Imageurl4",
        		"Imageurl5",
        		"Country"

        	);
        	update_option('ced_fruugo_latest_csv_header',$csvHeaderArray);
    }

	/**
	 * This function to read data from csv and prepare response
	 * @name ced_fruugo_csv_import_export_module_read_csv()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function ced_fruugo_csv_import_export_module_read_csv() {
		$productArr=[];
		$bufferArr=[];
		$header_array=[];
		$filestore=$_POST['filepath'];
		$offset=$_POST['offset'];
		$limit=$_POST['limit'];
		$offset = (int)$offset;
		$limit = (int)$limit;
		if(file_exists(CED_FRUUGO_DIRPATH . 'vendor/autoload.php')){
			require_once CED_FRUUGO_DIRPATH . 'vendor/autoload.php';
		}

		$readerEntityFactory = new  Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
		$excelReader = $readerEntityFactory->createReaderFromFile($filestore);
		$excelReader->setShouldPreserveEmptyRows(true);
		$excelReader->open($filestore);
		foreach($excelReader->getSheetIterator() as $sheet){
		foreach($sheet->getRowIterator() as $rowNumber => $row){
			$rowAsArray = $row->toArray();
			array_push($productArr, $rowAsArray);
			}
		}
		$total_size=sizeof($productArr);
		$temp_array = array();
		$bufferArr = array();
		for($i=0;$i<1;$i++){
			foreach($productArr[$i] as $key=>$value){
					$header_array[] = $value;
			}
		}
		for($i=$offset;$i<=$limit;$i++){
			foreach($productArr[$i] as $key=>$value){
				$temp_array[] = $value;
			}
			array_push($bufferArr,$temp_array);
			$temp_array =[];
		}
			foreach ($bufferArr as $key => $value) {
				$this->ced_cwsm_write_csv_content_to_DB($value,$header_array);
			}
			$left_size=$limit+100;
			echo "uploaded".",".$offset.",".$limit.",".$total_size.",".$left_size;
			$bufferArr =[];
			wp_die();
	}
	function ced_cwsm_write_csv_content_to_DB($data,$header_array) {
		// print_r($header_array);
		// die;
		do_action('ced_fruugo_import_data_from_csv_to_DB',$data,$header_array);
	}


	/**
	 * This function to get all meta keys related to a product
	 * @name fetch_all_meta_keys_related_to_selected_product()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function fetch_all_meta_keys_related_to_selected_product() {
		
	fruggorenderMetaKeysTableOnProfilePage(sanitize_text_field($_POST['selectedProductId']));
	wp_die();
	}


	/**
	 * This function includes custom js needed by module.
	 * @name ced_fruugo_extended_manager_admin_enqueue_scripts()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_fruugo_extended_manager_admin_enqueue_scripts() {
		$screen    = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		//echo $screen_id;die;
		if( $screen_id == 'fruugo_page_umb-fruugo-pro-mgmt' ){
			wp_enqueue_style('ced_fruugo_manage_products_css', CED_FRUUGO_URL.'/admin/css/manage_products.css');
		}

		if( $screen_id == 'fruugo_page_umb-fruugo-cat-map' ){
			wp_enqueue_style('ced_fruugo_category_mapping_css', CED_FRUUGO_URL.'/admin/css/category_mapping.css');
		}

		if( $screen_id == 'fruugo_page_umb-fruugo-shop-settings' )
		{
			wp_enqueue_style( 'ced_fruugo_shop_settings_page_css', CED_FRUUGO_URL.'/admin/css/profile_page_css.css');
		}

		if( $screen_id == 'fruugo_page_umb-fruugo-profile' && isset($_GET['action']))
		{	
			wp_enqueue_script( 'ced_fruugo_profile_edit_add_js', CED_FRUUGO_URL.'/admin/js/profile-edit-add.js', array('jquery'), '1.0', true );
			wp_localize_script( 'ced_fruugo_profile_edit_add_js', 'ced_fruugo_profile_edit_add_script_AJAX', array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			));
			wp_enqueue_script( 'ced_fruugo_profile_jquery_dataTables_js', CED_FRUUGO_URL.'/admin/js/jquery.dataTables.min.js', array('jquery'), '1.0', true );
			wp_enqueue_style( 'ced_fruugo_profile_jquery_dataTables_css', CED_FRUUGO_URL.'/admin/css/jquery.dataTables.min.css');
			wp_enqueue_style( 'ced_fruugo_profile_page_css', CED_FRUUGO_URL.'/admin/css/profile_page_css.css');
			
			/**
			** woocommerce scripts to show tooltip :: start
			*/
			
			/* woocommerce style */
			wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
			wp_enqueue_style( 'woocommerce_admin_menu_styles' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			
			/* woocommerce script */
			$suffix = '';
			wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );
			wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );

			$params = array(
				/* translators: %s: decimal */
				// 'i18n_decimal_error'                => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'woocommerce' ), $decimal ),
				/* translators: %s: price decimal separator */
				'i18n_mon_decimal_error'            => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'woocommerce' ), wc_get_price_decimal_separator() ),
				'i18n_country_iso_error'            => __( 'Please enter in country code with two capital letters.', 'woocommerce' ),
				'i18_sale_less_than_regular_error'  => __( 'Please enter in a value less than the regular price.', 'woocommerce' ),
				// 'decimal_point'                     => $decimal,
				'mon_decimal_point'                 => wc_get_price_decimal_separator(),
				'strings' => array(
					'import_products' => __( 'Import', 'woocommerce' ),
					'export_products' => __( 'Export', 'woocommerce' ),
				),
				'urls' => array(
					'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
					'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
				),
			);

			wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
			wp_enqueue_script( 'woocommerce_admin' );	
			
			/**
			** woocommerce scripts to show tooltip :: end
			*/	
		}
		
		if( $screen_id == 'toplevel_page_umb-main' ){	
			wp_enqueue_script( 'ced_fruugo_update_marketplace_js', CED_FRUUGO_URL.'/admin/js/update_marketplace.js', array('jquery'), '1.0', true );
			wp_localize_script( 'ced_fruugo_update_marketplace_js', 'ced_fruugo_update_marketplace_script_AJAX', array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			));
		}

		if( $screen_id == 'fruugo_page_umb-fruugo-bulk-action' ) {	
			wp_enqueue_script( 'ced_fruugo_select2_js', CED_FRUUGO_URL.'admin/js/select2.min.js', array('jquery'), '1.0', true );
			wp_enqueue_style( 'ced_fruugo_select2_css', CED_FRUUGO_URL.'admin/css/select2.min.css');
		}
		
		if( isset($_GET['page']) && $_GET['page']=='umb-fruugo-bulk-action' && isset($_GET['section']) && $_GET['section']=='csv_upload_section' ) {
			wp_enqueue_script( 'ced_fruugo_csv_upload_script_js', CED_FRUUGO_URL.'/admin/js/csv_upload.js', array('jquery'), '1.0', true );
			wp_localize_script( 'ced_fruugo_csv_upload_script_js', 'ced_fruugo_csv_upload_script_js_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'loading_image' => CED_FRUUGO_URL.'/admin/css/clock-loading.gif'
			));
			wp_enqueue_style('ced_fruugo_csv_upload_script_css', CED_FRUUGO_URL.'/admin/css/csv_upload.css');
		}
		if( $screen_id == 'fruugo_page_umb-fruugo-bulk-action' && isset($_GET['section']) && $_GET['section'] == 'bulk_product_upload_queue' ) {	
			wp_enqueue_script( 'ced_fruugo_upload_queue_script_js', CED_FRUUGO_URL.'/admin/js/ced-umb-queue-upload.js', array('jquery'), '1.0', true );
			wp_localize_script( 'ced_fruugo_upload_queue_script_js', 'ced_fruugo_upload_queue_script_js_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			));
		}
		

	}

}
new CED_FRUUGO_Extended_Manager();
?>