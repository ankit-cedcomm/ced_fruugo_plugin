<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

global $post;
$order_id = isset($post->ID) ? intval($post->ID) : '';
$umb_fruugo_order_status = get_post_meta($order_id,'_fruugo_umb_order_status',true);

$merchant_order_id = get_post_meta($order_id,  'merchant_order_id', true);
$purchaseOrderId = get_post_meta($order_id,  'purchaseOrderId', true);
$fulfillment_node = get_post_meta($order_id,  'fulfillment_node', true);
$order_detail = get_post_meta($order_id,  'order_detail', true);
$order_item = get_post_meta($order_id,  'order_items', true);
// print_r($order_detail['o:orderDate']);die;
// $number_items = 0;
//Get order status
$shipping_fruugo_data = get_post_meta($order_id,'ship_data_for_fruugo',true);
$tracking_num = get_post_meta($order_id,'trackingCode_for_fruugo',true);
$trackingurl = get_post_meta($order_id,'trackingurl_for_fruugo',true);
$messagetofruugo_shipping = get_post_meta($order_id,'messagetofruugo_shipping', true);
$messagetocust_shipping = get_post_meta($order_id,'messagetocust_shipping',true);
// print_r($messagetofruugo_shipping);die;

$umb_fruugo_order_status = get_post_meta($order_id,'_fruugo_umb_order_status',true);
$paritial_shipped_fruugo = get_post_meta($order_id, 'all_info_for_order');
//echo $umb_fruugo_order_status;die;
if($umb_fruugo_order_status == 'Fetched')
{
	$umb_fruugo_order_status	= 'Acknowledged';
// update_post_meta($order_id,'_fruugo_umb_order_status',"Acknowledged");
}
$umb_fruugo_order_status	= 'Acknowledged';
?>

<div id="umb_fruugo_order_settings" class="panel woocommerce_options_panel">
	<div id="ced_fruugo_marketplace_loader" class="loading-style-bg" style="display: none;">
		<img src="<?php echo plugin_dir_url(__dir__);?>../../admin/images/BigCircleBall.gif">
	</div>
	<?php if(!empty($umb_fruugo_order_status)){ ?>
	<div class="options_group">
		<p class="form-field">
			<h3><center><?php  _e('FRUUGO ORDER STATUS : ', 'ced-fruugo'); echo strtoupper($umb_fruugo_order_status); ?></center></h3>
		</p>
	</div>
	<?php }?>
	<div class="options_group umb_fruugo_options"> 
		<?php 
		if($umb_fruugo_order_status == 'Cancelled')
			{?>
		<h1 style="text-align:center;"><?php _e('ORDER CANCELLED ','ced-fruugo');?></h1>
		<?php 
	}
	if($umb_fruugo_order_status == "Created")
	{/*	
		?>
		<p class="form-field">
			<label><?php _e('Select Order Action:','ced-fruugo'); ?></label>
			<input type="button" class="button primary " value="Ship Order" data-order_id = "<?php echo $order_id;?>" id="umb_fruugo_ack_action"/>
			<input type="button" class="button primary " value="Cancel Order" data-order_id = "<?php echo $order_id;?>" id="umb_fruugo_cancel_action"/>
		</p>
		<?php 
	*/}
		elseif($umb_fruugo_order_status == "Acknowledged")
		{
			?>
			<input type="hidden" id="fruggo_orderid" value="<?php echo $merchant_order_id ?>" readonly>
			<input type="hidden" id="woocommerce_orderid" value="<?php echo $order_id ?>">
			<h2 class="title"><?php _e('Shipment Information','ced-fruugo');?>:                   
			</h2>

			<!-- Ship Complete Order -->

			<div id="ced_fruugo_complete_order_shipping">
				<table class="wp-list-table widefat fixed striped">
					<tbody>
						<tr>
							<td><b><?php _e('Reference Order Id on fruugo','ced-fruugo');?></b></td>
							<td><?php echo $merchant_order_id; ?></td>
						</tr>
						<tr>
							<td><b><?php _e('Order Placed on fruugo','ced-fruugo');?></b></td>
							<td><?php  echo date('l, F jS Y \a\t g:ia',strtotime($order_detail['o:orderDate'])); ?></td>
						</tr>
						<tr>
							<td><b><?php _e('TrackingUrl','ced-fruugo');?></b></td>
							<td>
								<input type="text" id="umb_fruugo_tracking_url" value="">
							</td>
						</tr>
						<tr>
							<td><b><?php _e('Tracking Number','ced-fruugo');?></b></td>
							<td><input type="text" id="umb_fruugo_tracking_number" value=""></td>
						</tr>
						<tr>
							<td><b><?php _e('MessageToCustomer','ced-fruugo');?></b></td>
							<td>
								<input type="text" id="umb_fruugo_messagetocustomer" value="">
							</td>
						</tr>
						<tr>
							<td><b><?php _e('MessageToFruugo','ced-fruugo');?></b></td>
							<td>
								<input type="text" id="umb_fruugo_messagetofruugo" value="">
							</td>
						</tr>

					</tbody>
				</table>
				<table cellspacing="0" cellpadding="0" class="woocommerce_order_items">
					<thead>
						<tr>
							<th class="line_cost sortable"><?php _e('Sku','ced-fruugo');?></th>
							<th class="line_cost sortable"><?php _e('Qty Order','ced-fruugo');?></th>
							<th class="line_cost sortable"><?php _e('Qty To Shipped','ced-fruugo');?></th>
							<th class="line_cost sortable"><?php _e('Qty To Cancelled','ced-fruugo');?></th>
						</tr>
					</thead>
					<tbody id="fruugo_order_line_items">
						<?php  

						$count = 0;
						foreach($order_item['ItemsArray'] as $valdata){
							$product = new WC_Product($valdata['ID']);
							$sku 		= $product->get_sku();
							$order_qty 	= $valdata['OrderedQty'];
							$cancel_qty = $valdata['CancelQty'];
							$unq_id		= ++$count;
							?>
							<tr id="<?php echo $unq_id;?>">

								<td class="line_cost sortable">
									<input type="text" size="50" name="sku<?php echo $unq_id?>" value="<?php echo $sku?>" data-p-id = <?php echo $valdata['ID']; ?> id="sku<?php echo $unq_id?>" class="item_sku" readonly/>
								</td>

								<td  class="line_cost sortable">
									<input type="text" size="50" name="qty_order<?php echo $unq_id?>" value="<?php echo $order_qty?>" id="qty_order<?php echo $unq_id?>" class="item_qty_order" readonly/>
								</td>
								<td  class="line_cost sortable">
									<input type="text"  size="50" name="qty_shipped<?php echo $unq_id?>" value="<?php echo $order_qty?>" id="qty_shipped<?php echo $unq_id?>" class="item_qty_shipped" />
								</td>
								<td  class="line_cost sortable">
									<input type="text"  size="50" name="qty_cancel<?php echo $unq_id?>" value="<?php echo $cancel_qty?>" id="qty_cancel<?php echo $unq_id?>" class="item_qty_cancel"/>
								</td>

							</tr>
							<?php } ?>
						</tbody>	
					</table>	
				</div>
				<input data-order_id ="<?php echo $order_id;?>" type="button" class="button" id="ced_fruugo_shipment_submit" value="Submit Shipment">
				<!-- Ship Order by LIne Item -->
				<?php 
			}
			elseif($umb_fruugo_order_status == "Shipped")
			{
				?>
				<input type="hidden" id="fruggo_orderid" value="<?php echo $merchant_order_id ?>" readonly>
				<input type="hidden" id="woocommerce_orderid" value="<?php echo $order_id ?>">
				<h2 class="title"><?php _e('Shipment Information','ced-fruugo');?>:                   
				</h2>

				<!-- Ship Complete Order -->

				<div id="ced_fruugo_complete_order_shipping">
					<table class="wp-list-table widefat fixed striped">
						<tbody>
							<tr>
								<td><b><?php _e('Reference Order Id on fruugo','ced-fruugo');?></b></td>
								<td><?php echo $merchant_order_id; ?></td>
							</tr>
							<tr>
								<td><b><?php _e('Order Placed on fruugo','ced-fruugo');?></b></td>
								<td><?php  echo date('l, F jS Y \a\t g:ia',strtotime($order_detail['o:orderDate'])); ?></td>
							</tr>
							<tr>
								<td><b><?php _e('TrackingUrl','ced-fruugo');?></b></td>
								<td>
									<input type="text" id="umb_fruugo_tracking_url" value=""/>
								</td>
							</tr>
							<tr>
								<td><b><?php _e('Tracking Number','ced-fruugo');?></b></td>
								<td><input type="text" id="umb_fruugo_tracking_number" value=""></td>
							</tr>
							<tr>
								<td><b><?php _e('MessageToCustomer','ced-fruugo');?></b></td>
								<td>
									<input type="text" id="umb_fruugo_messagetocustomer" value="">
								</td>
							</tr>
							<tr>
								<td><b><?php _e('MessageToFruugo','ced-fruugo');?></b></td>
								<td>
									<input type="text" id="umb_fruugo_messagetofruugo" value="">
								</td>
							</tr>
							
						</tbody>
					</table>
					<table cellspacing="0" cellpadding="0" class="woocommerce_order_items">
						<thead>
							<tr>
								<th class="line_cost sortable"><?php _e('Sku','ced-fruugo');?></th>
								<th class="line_cost sortable"><?php _e('Qty Order','ced-fruugo');?></th>
								<th class="line_cost sortable"><?php _e('Qty Shipped','ced-fruugo');?></th>
								<th class="line_cost sortable"><?php _e('Qty Cancelled','ced-fruugo');?></th>
								<th class="line_cost sortable"><?php _e('Qty Available','ced-fruugo');?></th>
							</tr>
						</thead>
						<tbody id="fruugo_order_line_items">
							<?php  

							$count = 0;

							foreach($shipping_fruugo_data as $keydata => $valdata){
								$qty_shipped = "";
								$qty_cancel = "";
								foreach ($paritial_shipped_fruugo[0] as $key => $value) {
									
									foreach ($value['ship_data_for_fruugo'] as $key_ship => $value_ship) {
										if($keydata == $key_ship){
										// print_r($value_ship['qty_shipped']);
											$qty_shipped = $qty_shipped + $value_ship['qty_shipped']  ;
										// print_r($qty_shipped);
											$qty_cancel = $qty_cancel + $value_ship['qty_cancel'] ;
										}
									}
								}
							// $product = new WC_Product($valdata['ID']);
								$sku 		= $valdata['sku'];
								$order_qty 	= $valdata['qty_order'];
								$shipped_qty 	= $qty_shipped;
								$cancel_qty = $qty_cancel;
								$available_qty = $order_qty -($shipped_qty + $cancel_qty);
								$unq_id		= ++$count;
								if($available_qty != 0){
									?>
									<tr id="<?php echo $unq_id;?>">

										<td class="line_cost sortable">
											<input type="text" size="50" name="sku<?php echo $unq_id?>" value="<?php echo $sku?>" data-p-id = <?php echo $valdata['pro_id']; ?> id="sku<?php echo $unq_id?>" class="item_sku" readonly/>
										</td>

										<td  class="line_cost sortable">
											<input type="text" size="50" name="qty_order<?php echo $unq_id?>" value="<?php echo $order_qty?>" id="qty_order<?php echo $unq_id?>" class="item_qty_order" readonly/>
										</td>
										<td  class="line_cost sortable">
											<input type="text"  size="50" name="qty_shipped<?php echo $unq_id?>" value="" id="qty_shipped<?php echo $unq_id?>" class="item_qty_shipped" availableData= "<?php echo $available_qty; ?>" />
										</td>
										<td  class="line_cost sortable">
											<input type="text"  size="50" name="qty_cancel<?php echo $unq_id?>" value="" id="qty_cancel<?php echo $unq_id?>" class="item_qty_cancel" availableData= "<?php echo $available_qty; ?>"/>
										</td>
										<td  class="line_cost sortable">
											<input type="text"  size="50" name="available_qty<?php echo $unq_id?>" value="<?php echo $available_qty?>" id="available_qty<?php echo $unq_id?>" class="available_qty" readonly/>
										</td>

									</tr>
									<?php }} ?>
								</tbody>	
							</table>	
						</div>
						<input data-order_id ="<?php echo $order_id;?>" type="button" class="button" id="ced_fruugo_shipment_submit" value="Submit Shipment">
						<!-- Ship Order by LIne Item -->

					</div>
					<div class="options_group">
						<p class="form-field">
							<h3><center><?php  _e('Fruugo Order History', 'ced-fruugo'); ?></center></h3>
						</p>
					</div>
					<?php if (isset($paritial_shipped_fruugo) && !empty($paritial_shipped_fruugo)){    
						foreach ($paritial_shipped_fruugo[0] as $key => $value) {
		 				// print_r($value);die('ds');
		 			// }
							?>
							<div id="ced_fruugo_complete_order_shipping">
								<table class="wp-list-table widefat fixed striped">
									<tbody>
										<tr>
											<td><b><?php _e('Reference Order Id on fruugo','ced-fruugo');?></b></td>
											<td><?php echo $merchant_order_id; ?></td>
										</tr>
										<tr>
											<td><b><?php _e('Order Placed on fruugo','ced-fruugo');?></b></td>
											<td><?php  echo date('l, F jS Y \a\t g:ia',strtotime($order_detail['o:orderDate'])); ?></td>
										</tr>
										<tr>
											<td><b><?php _e('TrackingUrl','ced-fruugo');?></b></td>
											<td>
												<span><?php echo $value['trackingurl_for_fruugo']; ?></span>
											</td>
										</tr>
										<tr>
											<td><b><?php _e('Tracking Number','ced-fruugo');?></b></td>
											<td><span><?php echo $value['trackingCode_for_fruugo']; ?></span></td>
										</tr>
										<tr>
											<td><b><?php _e('MessageToCustomer','ced-fruugo');?></b></td>
											<td>
												<span><?php echo $value['messagetocust']; ?></span>
											</td>
										</tr>
										<tr>
											<td><b><?php _e('MessageToFruugo','ced-fruugo');?></b></td>
											<td>
												<span><?php echo $value['messagetofruugo_shipping']; ?></span>
											</td>
										</tr>

									</tbody>
								</table>
								<table cellspacing="0" cellpadding="0" class="woocommerce_order_items">
									<thead>
										<tr>
											<th class="line_cost sortable"><?php _e('Sku','ced-fruugo');?></th>
											<th class="line_cost sortable"><?php _e('Qty Order','ced-fruugo');?></th>
											<th class="line_cost sortable"><?php _e('Qty shipped','ced-fruugo');?></th>
											<th class="line_cost sortable"><?php _e('qty Cancelled','ced-fruugo');?></th>
										</tr>
									</thead>
									<tbody id="fruugo_order_line_items">
										<?php  

										$count = 0;
										foreach($value['ship_data_for_fruugo'] as $valdata){
						print_r($valdata);die;
							// $product = new WC_Product($valdata['ID']);
											$sku 		= $valdata['sku'];
											$order_qty 	= $valdata['qty_order'];
											$shipped_qty 	= $valdata['qty_shipped'];
											$cancel_qty = $valdata['qty_cancel'];
											$unq_id		= ++$count;
											?>
											<tr id="<?php echo $unq_id;?>">

												<td class="line_cost sortable">
													<input type="text" size="50" value="<?php echo $sku?>"  readonly/>
												</td>

												<td  class="line_cost sortable">
													<input type="text" size="50" n value="<?php echo $order_qty?>" readonly/>
												</td>
												<td  class="line_cost sortable">
													<input type="text"  size="50" value="<?php echo $shipped_qty?>" readonly/>
												</td>
												<td  class="line_cost sortable">
													<input type="text"  size="50" value="<?php echo $cancel_qty?>"   readonly/>
												</td>

											</tr>
											<?php } ?>
										</tbody>	
									</table>	
								</div> <?php }
							}
						}?>   
					</div>