<?php
if(!class_exists('CedfruggoOrders')){
	class CedfruugoOrders{

		private static $_instance;
		private static $client_obj;
		/**
		 * get_instance Instance.
		 *
		 * Ensures only one instance of CedfruggoOrders is loaded or can be loaded.
		 *
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 * @static
		 * @return get_instance instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		/**
		 * function to fetch orders
		 * 
		 * @name getOrders
		 */
		public function getOrders()
		{
			$saved_fruugo_details = get_option( 'ced_fruugo_details', array() );
			include_once CED_FRUUGO_DIRPATH.'marketplaces/fruugo/lib/xmltoarray.php';
			
			try
			{
				require_once CED_FRUUGO_DIRPATH.'marketplaces/fruugo/lib/class-fruugo-request.php';
				
				$OrderDownload =  new FruugoRequest();
				update_option('last_fetch_date', date("Y-m-d"));
				$last_fetch_date = get_option('last_fetch_date');
				$last_fetch_date = '2018-08-20';
				$fruugo_order = $OrderDownload->CGetRequest('orders/download?from='.$last_fetch_date);
				// $fruugo_order = file_get_contents("fruugo_order.xml",true);
				if(!empty($fruugo_order)){
					// update_option('last_fetch_date', date("Y-m-d"));
				$result = XML2Array::createArray($fruugo_order);
				}
				// $arr = simplexml_load_string($fruugo_order);
				// $fruugo_order = json_decode($fruugo_order);
				$count = 0;
				if(empty($result))
				{
					return;
				}
				
				foreach($result as $key => $values)
				{
					if(!isset($values['o:order'][0])){
					$count =1;
					}
				}
				if($count==1){
				$resultsingle['o:orders']['o:order'][] = $result['o:orders']['o:order'];
				$result = $resultsingle;
				}
				// echo "<pre>";
			}
			catch ( Exception $e )
			{
				$result = $e->getMessage();
			}

			if( isset( $result['o:orders'] ) && !empty( $result['o:orders'] ) )
			{
				return $result['o:orders'];
			}
			
		}


		public function createLocalOrder($orders)
		{
			// global $client_obj;
			// $api = new fruugo\fruggoApi( $client_obj );
			// echo "<pre>";
			//print_r($orders);die;
			if(is_array($orders['o:order']) && !empty($orders['o:order']))
			{
				$OrderItemsInfo = array();
				foreach ($orders['o:order'] as $key_order => $order)
				{
					 //echo "<pre>";
					 //print_r($order);die;
					$customerOrderId = isset( $order['o:customerOrderId'] ) ? $order['o:customerOrderId'] : '';
					if( $customerOrderId != '' )
					{
						// $transactions_per_reciept = $api->findAllShop_Receipt2Transactions( array( 'params' => array( "receipt_id" => (int)$receipt_id ) ) );
						//print_r($order ['o:orderLines']['o:orderLine']['o:totalVat']);die;
						$ShipToFirstName = isset($order['o:shippingAddress']['o:firstName']) ? $order['o:shippingAddress']['o:firstName'] : ""; 
						$ShipToLastName = isset($order['o:shippingAddress']['o:lastName']) ? $order['o:shippingAddress']['o:lastName'] : ""; 

						$ShipToAddress1  = isset($order['o:shippingAddress']['o:streetAddress']) ? $order['o:shippingAddress']['o:streetAddress'] : "";
						// $ShipToAddress2  = isset($order['second_line']) ? $order['second_line'] : "";o:countryCode
						$ShipToCityName  = isset($order['o:shippingAddress']['o:city']) ? $order['o:shippingAddress']['o:city'] : "";;
						$ShipToStateCode = isset($order['o:shippingAddress']['o:province']) ? $order['o:shippingAddress']['o:province'] : "";
						$ShipToZipCode   = isset($order['o:shippingAddress']['o:postalCode']) ? $order['o:shippingAddress']['o:postalCode'] : "";
						$CustomerPhoneNumber   = isset($order['o:shippingAddress']['o:phoneNumber']) ? $order['o:shippingAddress']['o:phoneNumber'] : "";
						$countryCode   = isset($order['o:shippingAddress']['o:countryCode']) ? $order['o:shippingAddress']['o:countryCode'] : "";
						$finalTax=isset($order ['o:orderLines']['o:orderLine']['o:totalVat'])?$order ['o:orderLines']['o:orderLine']['o:totalVat']:0;
						$country_name = WC()->countries->countries[$countryCode];
						$ShippingAddress = array(
							'first_name' => $ShipToFirstName,
							'last_name' => $ShipToLastName,
							'address_1' => $ShipToAddress1,
							'city' => $ShipToCityName,
							'state' => $ShipToStateCode,
							'postcode'	=> $ShipToZipCode,
							'country_code' => $countryCode,
							'country' =>$country_name 
							);
						// print_r($ShippingAddress);die('ds');
						$email = '';

						if($email=='' && $email ==NULL){
							$email ='customer@fruugo.com';
						}
						$BillToFirstName = $ShipToFirstName;
						$BillEmailAddress = $email;
						$BillPhoneNumber = $CustomerPhoneNumber;

						$BillingAddress = array(
							'first_name' => $BillToFirstName,
							'last_name' => $ShipToLastName,
							'email'	=> $BillEmailAddress,
							'phone' => $BillPhoneNumber,
							'city' => $ShipToCityName,
							'state' => $ShipToStateCode,
							'postcode'	=> $ShipToZipCode,
							'country_code' => $countryCode,
							'country' =>$country_name 
							);
						$address = array(
							'shipping' => $ShippingAddress,
							'billing'  => $BillingAddress
							);
						
						$OrderNumber = $order['o:orderId'];

						if( isset( $order['o:orderLines']['o:orderLine'] ) && !empty( $order['o:orderLines']['o:orderLine'] ) )
						{
							// $transactions_per_reciept = $transactions_per_reciept['results'];
							$order_items_array=array();
							if(!isset($order['o:orderLines']['o:orderLine']['0'])){
								$order_items_array['0']=$order['o:orderLines']['o:orderLine'];
							}else{
								$order_items_array=$order['o:orderLines']['o:orderLine'];
							}
							$ItemArray = array();
							$confirmed_items_arr =array();
							$confirmed_items_arr[] = array('orderId' => $OrderNumber);
							foreach ($order_items_array as $transaction) 
							{
								$item=array();
								$ID = false;
								$productSku = isset( $transaction['o:skuId'] ) ? $transaction['o:skuId'] : false ;
								$OrderedQty = isset( $transaction['o:totalNumberOfItems'] ) ? $transaction['o:totalNumberOfItems'] : 1 ;
								$basePrice = isset( $transaction['o:itemPriceInclVat'] ) ? $transaction['o:itemPriceInclVat'] : '';
								$CancelQty = 0 ;
								if( $productSku )
								{
									global $wpdb;
									$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $productSku ) );
									if(!empty( $product_id ) )
									{
										$ID = $product_id;
									}
								}
								$item = array(
									'OrderedQty' => $OrderedQty,
									'CancelQty' => $CancelQty,
									'UnitPrice' => $basePrice,
									'ID' => $ID,
									'shippingCost'=>isset($order['o:shippingCostInclVAT']) ? $order['o:shippingCostInclVAT'] : '',
									);
								$ItemArray[] = $item;

								$item_confirmed = $transaction['o:fruugoProductId'].','.$transaction['o:fruugoSkuId'].','.$transaction['o:totalNumberOfItems'];
								

							}
						}
					}
					
					
					$OrderItemsInfo = array(
						'OrderNumber' => isset($OrderNumber)?$OrderNumber:'',
						'ItemsArray' => isset($ItemArray)?$ItemArray:array(),
						'tax' => isset($finalTax)?$finalTax:''
						);
					$orderItems = $OrderItemsInfo;

					$merchantOrderId= $customerOrderId;
					$purchaseOrderId= isset($OrderNumber)?$OrderNumber:'';
					$fulfillmentNode = "";
					$orderDetail = isset($order) ? $order : array();
					$fruugoOrderMeta = array(
						'merchant_order_id' => $OrderNumber,
						'purchaseOrderId' => $purchaseOrderId,
						'fulfillment_node' => $fulfillmentNode,
						'order_detail' => $orderDetail,
						'order_items' => $orderItems
						);
					if(!class_exists('CED_FRUUGO_order_manager')){
						require_once CED_FRUUGO_DIRPATH.'admin/helper/class-order-manager.php';
					}
					$OrderInstance = CED_FRUUGO_order_manager::get_instance();
					$creation_date = $order['o:orderDate'];

					$order_id = $OrderInstance->create_order( $address, $OrderItemsInfo, 'fruugo',$fruugoOrderMeta, $creation_date );
					$fruugo_status = get_post_meta($order_id, '_fruugo_umb_order_status', true);
					// print_r($fruugo_status);die;
					if(!empty($order_id) && $fruugo_status == 'Fetched'){
						require CED_FRUUGO_DIRPATH.'marketplaces/fruugo/lib/class-fruugo-request.php';
						$OrderInstanceConfirm = new FruugoRequest();
						$fruugo_order_confirm = $OrderInstanceConfirm->CPostRequest('orders/confirm',http_build_query($confirmed_items_arr[0]));
						//print_r($fruugo_order_confirm);
						//print_r($confirmed_items_arr);
						//die('OK');
						if($fruugo_order_confirm){
							update_post_meta($order_id , '_fruugo_umb_order_status', 'Acknowledged');
						}

					}
					//echo $order_id;die('ok');
				}
			}
		}

		/**
		 * function loadDepenedency
		 *
		 * @name loadDepenedency
		 */
		public function renderDependency($file){
			if(is_file($file)){
				if(require_once $file){
					return true;
				}
				return false;
			}
		}
	}
}