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

if( !class_exists( 'CED_FRUUGO_order_manager' ) ) :

/**
 * Order related functionalities.
*
*
* @since      1.0.0
* @package    Woocommerce fruugo Integration
* @subpackage Woocommerce fruugo Integration/admin/helper
* @author     CedCommerce <cedcommerce.com>
*/
class CED_FRUUGO_order_manager{

	/**
	 * The Instace of CED_FRUUGO_feed_manager.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      $_instance   The Instance of CED_FRUUGO_order_manager class.
	 */
	private static $_instance;

	/**
	 * CED_FRUUGO_feed_manager Instance.
	 *
	 * Ensures only one instance of CED_FRUUGO_order_manager is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return CED_FRUUGO_order_manager instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Constructor.
	 * 
	 * @since 1.0.0
	 */
	public function __construct(){
		//process order.
		add_action('wp_ajax_process_order',array($this,'process_order_request'));
		add_action(	'wp_ajax_umb_fruugo_ship_order', array($this,'umb_fruugo_ship_order'));
	}


	/**
		 * function complete order(provide shipment detail)
		 * @name umb_fruugo_ship_order
		 */
	public function umb_fruugo_ship_order(){
		// error_reporting(E_ALL);
// ini_set("display_errors", 1);
		$wo_order_id  = isset($_POST['woo_order_id']) ? $_POST['woo_order_id'] : false;
		// print_r($wo_order_id);die('df');
		$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : false;
		
		$trackNumber = isset($_POST['trackNumber']) ? $_POST['trackNumber'] : false;
		$trackingUrl = isset($_POST['Tracking_url']) ? $_POST['Tracking_url'] : false;
		$messagetocust = isset($_POST['Message_to_customer']) ? $_POST['Message_to_customer'] : false;
		$messagetofruugo = isset($_POST['Message_to_fruugo']) ? $_POST['Message_to_fruugo'] : false;
		$all_data_array = isset($_POST['all_data_array']) ? $_POST['all_data_array'] : false;
		if($order_id){

			if(isset($all_data_array) && is_array($all_data_array)){

				foreach ($all_data_array as $key => $item) {
					// print_r($key);
					$data_cancel_to_fruugo = '';
					$find = explode('/',$key);
					$check = $find[0];
					$unq_id = $find[1];
					$product_ids = explode('A', $unq_id);
					$product_id = $product_ids[0];
					if($check == 'sku')
					{
						$all_info[$product_id]['sku'] = $item;
					}
					if($check == 'qty_shipped')
					{
						$all_info[$product_id]['qty_shipped'] = $item;
					}
					if($check == 'qty_order')
					{
						$all_info[$product_id]['qty_order'] = $item;
					}
					if($check == 'qty_cancel')
					{

						$all_info[$product_id]['qty_cancel'] = $item;
					}
					if($check == 'pro_id')
					{
						$all_info[$product_id]['pro_id'] = $item;
					}
				}
				// print_r($all_info);die;
				foreach ($all_info as $all_info_key => $all_info_valdata) 
				{
					// print_r($all_info_valdata);
					if($all_info_valdata['qty_shipped'] > $all_info_valdata['qty_cancel'] && $all_info_valdata['qty_cancel'] == 0 ){

						$data_ship_to_fruugo = 'orderId='.$order_id;
						$data_ship_to_fruugo.= '&value='.$all_info_valdata['pro_id'].','.$all_info_valdata['sku'].','.$all_info_valdata['qty_shipped'];
					}
					if($all_info_valdata['qty_cancel'] != 0)
					{
						$data_cancel_to_fruugo = 'orderId='.$order_id;
                        $data_cancel_to_fruugo.= '&value='.$all_info_valdata['pro_id'].','.$all_info_valdata['sku'].','.$all_info_valdata['qty_cancel'];
					}
				}
				if($trackingUrl!='' && $data_ship_to_fruugo!='')
				{

					$data_ship_to_fruugo.='&trackingUrl='.$trackingUrl;
				}
				if($trackNumber!='' && $data_ship_to_fruugo!='')
				{
					$data_ship_to_fruugo.='&trackingCode='.$trackNumber;
				}
				if($messagetocust!='' && $data_ship_to_fruugo!='')
				{
					$data_ship_to_fruugo.='&messageToCustomer='.$messagetocust;
				}
				if($messagetofruugo!='' && $data_ship_to_fruugo!='')
				{
					$data_ship_to_fruugo.='&messageToFruugo='.$messagetofruugo;
				}
				if($data_cancel_to_fruugo!='')
				{
					$data_cancel_to_fruugo.='&cancellationReason=out_of_stock';
				}
				if($data_cancel_to_fruugo!='' && $messagetocust!='')
				{
					$data_cancel_to_fruugo.='&messageToCustomer='.$messagetocust;
				}
				if($data_cancel_to_fruugo!='' && $messagetofruugo!='')
				{
					$data_cancel_to_fruugo.='&messageToFruugo='.$messagetofruugo;
				}		
			}
			require_once CED_FRUUGO_DIRPATH.'marketplaces/fruugo/lib/class-fruugo-request.php';
			$OrderShip = new FruugoRequest();
			if(isset($data_ship_to_fruugo) && $data_ship_to_fruugo != ''){
				$order_ship_data = $OrderShip->CPostRequest('orders/ship',$data_ship_to_fruugo);
				//print_r($order_ship_data);die('data');
			}
			if(isset($data_cancel_to_fruugo) && $data_cancel_to_fruugo != ''){
				// print_r($data_ship_to_fruugo);die('d');
				$OrderShip->CPostRequest('orders/cancel',$data_cancel_to_fruugo);
			}
			// die('fd');
			update_post_meta($wo_order_id,'ship_data_for_fruugo',$all_info);
			$all_info_for_order = array();
			$all_info_for_order['trackingCode_for_fruugo'] = $trackNumber;
			$all_info_for_order['trackingurl_for_fruugo'] = $trackingUrl;
			$all_info_for_order['messagetofruugo_shipping'] = $messagetofruugo;
			$all_info_for_order['messagetocust'] = $messagetocust;
			$all_info_for_order['ship_data_for_fruugo'] = $all_info;
			$paritial_shipped_fruugo = get_post_meta($wo_order_id, 'all_info_for_order',true);
			// print_r($paritial_shipped_fruugo);die('df');
			$new_paritial_array = array();
			if(!empty($paritial_shipped_fruugo)){
				// print_r($all_info_for_order);die;
				if(isset($paritial_shipped_fruugo[0])){
					foreach ($paritial_shipped_fruugo as $key => $value) {
						// print_r($value);
						$new_paritial_array[] = $value;
						// print_r($new_paritial_array);die('qwerty');
					}
					// die;

				}
				$new_paritial_array[] = $all_info_for_order;
				// $all_info_for_order = json_encode($all_info_for_order);
			update_post_meta($wo_order_id, 'all_info_for_order', $new_paritial_array);
			}else{
				// $all_info_for_order = json_encode($all_info_for_order);
				// print_r($all_info_for_order);die;
				$all_info_for_orders = array();
				$all_info_for_orders[] = $all_info_for_order;
				update_post_meta($wo_order_id, 'all_info_for_order', $all_info_for_orders);
			}
			// update_post_meta($wo_order_id,'trackingCode_for_fruugo',$trackNumber);
			// update_post_meta($wo_order_id,'trackingurl_for_fruugo',$trackingUrl);
			// update_post_meta($wo_order_id,'messagetofruugo_shipping',$messagetofruugo);
			update_post_meta($wo_order_id,'messagetocust_shipping',$messagetocust);
			update_post_meta($wo_order_id,'_fruugo_umb_order_status','Shipped');

		}
		else{
			echo json_encode(array('status'=>'Please fill in all the details'));die;
		}
	}


	/**
	 * process order on marketplace.
	 * 
	 * @since 1.0.0
	 */
	public function process_order_request(){
		
		$orderId = isset($_POST['oID']) ? intval($_POST['oID']) : '';
		if(!is_null($orderId)){
			
			$marketplace = isset($_POST['marketplace']) ? esc_attr($_POST['marketplace']) : $this->get_marketplace_info($orderId);
			if($marketplace && !is_null($marketplace)){
				$file = CED_FRUUGO_DIRPATH.'/marketplaces/'.$marketplace.'/class-'.$marketplace.'.php';
				if(file_exists($file)){
					require_once $file;
					$className = 'CED_FRUUGO_manager';
					if(class_exists($className)){
						$api = new $className();
						$perform = isset($_POST['perform']) ? $_POST['perform'] : 'cofirm';
						if($perform=='cancel'){
							$response = $api->cancel_order($orderId);
						}else{
							$response = $api->cofirm_order($orderId);
						}
						return $response;
					}else{
						return 'class not found exception';
					}
				}else{
					return 'file not found exception';
				}
			}
		}
	}
	
	/**
	 * Enqueue scripts.
	 * 
	 * @since 1.0.0
	 */
	public function enqueue_scripts(){
		
		global $post;
		$post_type = get_post_type($post);
		$order_id = isset($post->ID) ? intval($post->ID) : '';
		
		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		
		$order_types = wc_get_order_types();
		
		if(in_array($post_type, $order_types) && in_array($screen_id, $order_types)){
			
			$marketplace = $this->get_marketplace_info($order_id);
			$marketplace = 'fruugo';
			if($marketplace && !is_null($marketplace)){
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_script('ced_fruugo_timepicker-jquery', plugins_url('woocommerce-fruugo-integration').'/admin/js/jquery-ui-timepicker-addon.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'));
				wp_enqueue_style('ced_fruugo_order_manager', plugins_url('woocommerce-fruugo-integration').'/admin/css/jquery-ui-timepicker-addon.css');
				
				$file_url =  plugins_url('woocommerce-fruugo-integration').'/marketplaces/'.$marketplace.'/js/order_manager.js';
				wp_register_script( 'ced_fruugo_order_manager', $file_url, array( 'jquery' ));
				wp_localize_script('ced_fruugo_order_manager','ced_order_localize',array('ajaxUrl' => admin_url( 'admin-ajax.php' )));
				wp_enqueue_script('ced_fruugo_order_manager');
			}
		}
	}
	
	/**
	 * get order info.
	 * 
	 * @since 1.0.0
	 */
	public function get_marketplace_info($order_id=''){
		
		if(!is_null($order_id)){
			$order = wc_get_order($order_id);
			if(is_wp_error($order)){
				return false;
			}else if($order==''){
				return false;
			}else{
				
				$order_from = get_post_meta($order_id,'_umb_marketplace',true);
				$marketplace = strtolower($order_from);
				return $marketplace;
			}
		}
	}
	
	/**
	 * Meta boxes for managing the orders.
	 * 
	 * @since 1.0.
	 */
	public function add_meta_boxes(){
		global $post;
		
		$post_type = get_post_type($post);
		$order_types = wc_get_order_types();
		
		if(in_array($post_type, $order_types)){
			
			add_meta_box( 'umb-order-manager', __( 'Manage Fruugo Orders', 'ced-fruugo' ) . wc_help_tip( __( 'Please send shipping confirmation or order cancellation request.', 'ced-fruugo' ) ), array($this,'order_manager_box') );
		}
	}
	
	/**
	 * Order meta box.
	 * 
	 * @since 1.0.0
	 */
	public function order_manager_box(){
		global $post;
		$order_id = isset($post->ID) ? intval($post->ID) : '';
		// print_r($order_id);die;
		if(!is_null($order_id)){
			$order = wc_get_order($order_id);
			if(is_wp_error($order)){
				
			}else{
				// $order_from = get_post_meta($order_id,'_umb_marketplace',true);
				$order_from = 'fruugo';
				$marketplace = strtolower($order_from);

				$template_path = CED_FRUUGO_DIRPATH.'marketplaces/'.$marketplace.'/partials/order_template.php';
				
				if(file_exists($template_path))
					require_once $template_path;
			}
		}
	}
	
	/**
	 * Create order into woo.
	 * 
	 * @since 1.0.0
	 */
	
	public function create_order($address=array(),$OrderItemsInfo=array(),$frameworkName='fruugo',$orderMeta=array() , $creation_date = ''){

		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		global $ced_fruugo_helper;
		// print_r($orderMeta); die('hello');
		$order_id = '';
		$order_created = false;
		if(count($OrderItemsInfo)){
			
			$OrderNumber = isset($OrderItemsInfo['OrderNumber']) ? $OrderItemsInfo['OrderNumber'] : 0;
			$order_id  = $this->is_fruugo_order_exists($OrderNumber);
			if($order_id){
				return $order_id;
			}
			
			if(count($OrderItemsInfo)){
				$ItemsArray = isset($OrderItemsInfo['ItemsArray']) ? $OrderItemsInfo['ItemsArray'] : array();

				if(is_array($ItemsArray))
				{
					foreach($ItemsArray as $ItemInfo)
					{
						//print_r($ItemInfo);die;
						$ProID = isset($ItemInfo['ID']) ? intval($ItemInfo['ID']) : 0;
						$Sku = isset($ItemInfo['Sku']) ? $ItemInfo['Sku'] : '';
						//$Sku = 'woo-cap';
						$params = array('_sku'=>$Sku);
						if(!$ProID){
							$ProID = $ced_fruugo_helper->umb_get_product_by($params);
						} 
						if(!$ProID){
							$ProID = $Sku;
							//$ProID = 254;
						}

						$Qty = isset($ItemInfo['OrderedQty']) ? intval($ItemInfo['OrderedQty']) : 0;
						$UnitPrice = isset($ItemInfo['UnitPrice']) ? floatval($ItemInfo['UnitPrice']) : 0;
						$shippingCost = isset($ItemInfo['shippingCost']) ? floatval($ItemInfo['shippingCost']) : 0;

						$ExtendUnitPrice = isset($ItemInfo['ExtendUnitPrice']) ? floatval($ItemInfo['ExtendUnitPrice']) : 0;
						$ExtendShippingCharge = isset($ItemInfo['ExtendShippingCharge']) ? floatval($ItemInfo['ExtendShippingCharge']) : 0;
						
						$_product = wc_get_product($ProID);
						
						if(is_wp_error($_product)){
							continue;
						}else if(is_null($_product)){
							continue;
						}else if(!$_product){
							continue;
						}else{
							if(!$order_created){
								$order_data = array(
									'status'        => apply_filters( 'woocommerce_default_order_status', 'pending' ),
									'customer_note' => __('Order from ','ced-fruugo').$frameworkName,
									'created_via'   => $frameworkName,
									);
								
								/* ORDER CREATED IN WOOCOMMERCE */
								$order = wc_create_order( $order_data );
								
								/* ORDER CREATED IN WOOCOMMERCE */

								if ( is_wp_error( $order ) ) {
									continue;
								} elseif ( false === $order ) {
									continue;
								} else {
									if( WC()->version < '3.0.0' )
									{
										$order_id = $order->id;
									}
									else{
										$order_id = $order->get_id();
									}
									$order_created = true;
								}
							}
							$_product->set_price($UnitPrice);
							$order->add_product( $_product, $Qty );
							$order->update_status('processing');
							$order->calculate_totals(false);
						}
					}
				}
				
				if(!$order_created){
					return false;
				}
				
				$new_fee  = new WC_Order_Item_Fee();
                   $new_fee->set_name(esc_attr( "Tax" )) ;
                   $new_fee->set_total( $OrderItemsInfo['tax']);
                   $new_fee->set_tax_class('');
                   $new_fee->set_tax_status('none');
                   $new_fee->set_total_tax( $OrderItemsInfo['tax']);
                   $new_fee->save();
                   $item_id = $order->add_item($new_fee);
                   $fruugo_item_total = $OrderItemsInfo['tax'] + $order->get_total();
					$order->set_total($fruugo_item_total);
					$order->save();
					

				$OrderItemAmount = isset($OrderItemsInfo['OrderItemAmount']) ? $OrderItemsInfo['OrderItemAmount'] : 0;
				$ShippingAmount = isset($OrderItemsInfo['ShippingAmount']) ? $OrderItemsInfo['ShippingAmount'] : 0;
				$RefundAmount = isset($OrderItemsInfo['RefundAmount']) ? $OrderItemsInfo['RefundAmount'] : 0;
				$ShipService = isset($OrderItemsInfo['ShipService']) ? $OrderItemsInfo['ShipService'] : '';
				//$taxID = isset($OrderItemsInfo['tax']['taxRateid']) ? $OrderItemsInfo['tax']['taxRateid'] : 0;
				//$taxamount = isset($OrderItemsInfo['tax']['taxAmount']) ? $OrderItemsInfo['tax']['taxAmount'] : 0;
				//$taxShippingamount = isset($OrderItemsInfo['tax']['taxShipamount']) ? $OrderItemsInfo['tax']['taxShipamount'] : 0;
				
				if( !empty($shippingCost) ) {
					$Ship_params = array(
						'ShippingCost' => $shippingCost,
						'ShipService' => 'Standard',
						);
					$this->addShippingCharge($order,$Ship_params);
				}
				$fruugo_order_total = $order->get_shipping_total() + $order->get_total();
				$order->set_total($fruugo_order_total);
				$order->save();

				$ShippingAddress = isset($address['shipping']) ? $address['shipping'] : '';
				if(is_array($ShippingAddress) && !empty( $ShippingAddress )) 
				{
					if( WC()->version < '3.0.0' ){
						$order->set_address($ShippingAddress,'shipping');
					}else{
						$type = 'shipping';
						foreach ( $ShippingAddress as $key => $value ) {
							if( $value != '' && $value != null && !empty( $value ) )
							{
								update_post_meta( $order->get_id(), "_{$type}_" . $key, $value );
								if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
									$order->{"set_{$type}_{$key}"}( $value );
								}
							}
						}
					}
				}
				
				//  $tax_rate = array(
				// 		'tax_rate_country'  => $ShippingAddress['country'],
				// 		'tax_rate_state'    => $ShippingAddress['state'],
				// 		'tax_rate'          => $ShippingAddress[''],
				// 		'tax_rate_name'     => $ShippingAddress[''],
				// 		'tax_rate_priority' => $ShippingAddress[''],
				// 		'tax_rate_compound' => $ShippingAddress[''] ? 1 : 0,
				// 		'tax_rate_shipping' => $ShippingAddress[''] ? 1 : 0,
				// 		'tax_rate_order'    => $loop ++,
				// 		'tax_rate_class'    => $class
				// );
					
				// $tax_rate_id = WC_Tax::_insert_tax_rate( $tax_rate ); 
				//die($OrderItemsInfo['tax']);
				$new_fee            = new stdClass();
				$new_fee->name      = 'Tax';
				$new_fee->amount    = (float) esc_attr($OrderItemsInfo['tax'] );
				$new_fee->tax_class = '';
				$new_fee->taxable   = 0;
				$new_fee->tax       = '';
				$new_fee->tax_data  = array();
				if( WC()->version < '3.0.0' ){
					$item_id = $order->add_fee( $new_fee );
				}else{
					$item_id = $order->add_item( $new_fee );
				}

				$BillingAddress = isset($address['billing']) ? $address['billing'] : '';
				if(is_array($BillingAddress) && !empty( $BillingAddress ))
				{
					if( WC()->version < '3.0.0' ){
						$order->set_address($ShippingAddress,'billing');
					}else{
						$type = 'billing';
						foreach ( $BillingAddress as $key => $value ) {
							if( $value != '' && $value != null && !empty( $value ) )
							{
								update_post_meta( $order->get_id(), "_{$type}_" . $key, $value );
								if ( is_callable( array( $order, "set_{$type}_{$key}" ) ) ) {
									$order->{"set_{$type}_{$key}"}( $value );
								}
							}
						}
					}
				}
				
				$order->set_payment_method('check');
				
				if( WC()->version < '3.0.0' )
				{
					$order->set_total($OrderItemAmount,'cart_discount');
				}else{
					$order->set_total($OrderItemAmount);
				}
				
				// $order->calculate_totals();
				
				update_post_meta($order_id,'_ced_fruugo_order_id',$OrderNumber);
				update_post_meta($order_id,'_is_ced_fruugo_order',1);
				update_post_meta($order_id,'_fruugo_umb_order_status',"Fetched");
				update_post_meta($order_id,'_umb_marketplace',$frameworkName);
				update_option( 'ced_fruugo_last_order_created_time', $creation_date );
				
				if(count($orderMeta)){
					foreach($orderMeta as $oKey => $oValue){
						update_post_meta($order_id,$oKey,$oValue);
					}
				}
			}
			
			return $order_id;
		}
		return false;
	}
	
	/**
	 * Check if order already imported or not.
	 * 
	 * @since 1.0.0
	 */
	public function is_fruugo_order_exists($order_number = 0){
		global $wpdb;
		if($order_number){
			$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_ced_fruugo_order_id' AND meta_value='%s' LIMIT 1", $order_number ) );
			if($order_id){
				return $order_id;
			}
		}
		return false;
	}
	
	
	/**
	 * get product id by.
	 * 
	 * @since 1.0.0
	 */
	public function get_product_id_by($params=array()){
		global $wpdb;
		
		$where = '';
		if(count($params)){
			$Flag = false;
			foreach($params as $meta_key=>$meta_value){
				if(!empty($meta_value) && !empty($meta_key)){
					if(!$Flag){
						$where .= 'meta_key="'.sanitize_key($meta_key).'" AND meta_value="'.$meta_value.'"';
						$Flag = true;
					}else{
						$where .= ' OR meta_key="'.sanitize_key($meta_key).'" AND meta_value="'.$meta_value.'"';
					}
				}
			}
			if($Flag){
				$product_id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE $where LIMIT 1" );
				if($product_id)
					return $product_id;
			}
		}
		return false;
	}

	/**
	 * add shipping charge
	 * 
	 * @since 1.0.0
	 */
	public static function addShippingCharge($order,$ShipParams=array()){
		$ShipName = isset($ShipParams['ShipService']) ? esc_attr($ShipParams['ShipService']) : 'UMB Default Shipping';
		$ShipCost = isset($ShipParams['ShippingCost']) ? floatval($ShipParams['ShippingCost']) : 0;
		$ShipTax = isset($ShipParams['ShippingTax']) ? floatval($ShipParams['ShippingTax']) : 0;
		
		if( WC()->version < '3.0.0' )
		{
			$item_id = wc_add_order_item( $order->id, array(
				'order_item_name'       => $ShipName,
				'order_item_type'       => 'shipping'
				) );
		}
		else{
			$item_id = wc_add_order_item( $order->get_id(), array(
				'order_item_name'       => $ShipName,
				'order_item_type'       => 'shipping'
				) );	
		}
		
		if ( ! $item_id ) {
			return false;
		}
		// wc_add_order_item_meta( $item_id, 'method_id', $ShipName );
		wc_add_order_item_meta( $item_id, 'cost', wc_format_decimal( $ShipCost ) );

		if( WC()->version < '3.0.0' )
		{
			// Update total
			$order->set_total( $order->order_shipping + wc_format_decimal( $ShipCost ), 'shipping' );
		}else{
			// Update total
			$order_id = $order->get_id();
			$order_shipping = get_post_meta($order_id , '_order_shipping', true);
			$order->set_shipping_total( $order_shipping + wc_format_decimal( $ShipCost ) );
			$order->save();
		}

		return $item_id;
	}
}
endif;