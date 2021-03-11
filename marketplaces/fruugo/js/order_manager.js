/**
 * 
 */
 jQuery(document).ready(function(){


	/**
	 * Acknowledge Order
	 */

	 jQuery("#umb_fruugo_ack_action").click(function(){
	 	var order_id = jQuery(this).data('order_id');
	 	jQuery("#ced_fruugo_marketplace_loader").show();
	 	jQuery.post(
	 		ced_order_localize.ajaxUrl,
	 		{
	 			'action' : 'umb_fruugo_acknowledge_order',
	 			'order_id' : order_id,
	 		},
	 		function(response){

	 			jQuery("#ced_fruugo_marketplace_loader").hide();
				//alert(response);
				var response = jQuery.parseJSON(response);
				if(response.status == "200"){
					window.location.reload();
				}else{
					alert('error');
				}
			}
			);
	 });
	 jQuery("#umb_fruugo_cancel_action").click(function(){
	 	var order_id = jQuery(this).data('order_id');
	 	jQuery("#ced_fruugo_marketplace_loader").show();
	 	jQuery.post(
	 		ced_order_localize.ajaxUrl,
	 		{
	 			'action' : 'umb_fruugo_cancel_order',
	 			'order_id' : order_id,
	 		},
	 		function(response){

	 			jQuery("#ced_fruugo_marketplace_loader").hide();
	 			var response = jQuery.parseJSON(response);
	 			if(response.status == "200"){
	 				window.location.reload();
	 			}else{
	 				alert('error');
	 			}
	 		}
	 		);
	 });
	 
	 jQuery(document).on('click', '#ced_fruugo_shipment_submit', function(){
	 	var all_data_array = {};
	 	var unique_ids = {};
	 	var i = 0;
	 	var Tracking_url		=	jQuery('#umb_fruugo_tracking_url').val();
	 	var Tracking_num	 	=	jQuery('#umb_fruugo_tracking_number').val();
	 	var Message_to_customer =	jQuery('#umb_fruugo_messagetocustomer').val();
	 	var Message_to_fruugo   =	jQuery('#umb_fruugo_messagetofruugo').val();
	 	var order_id = jQuery('#fruggo_orderid').val();
	 	var wo_order_id = jQuery('#post_ID').val();
	 	jQuery('#fruugo_order_line_items tr').each(function(){

	 		var tr = jQuery(this).attr('id');
	 		unique_ids[i] = tr;	
	 		var sku				= 	jQuery('#sku'+tr).val();
	 		var qty_order		= 	jQuery('#qty_order'+tr).val();
	 		var qty_shipped		=	jQuery('#qty_shipped'+tr).val();
	 		var qty_cancel		= 	jQuery('#qty_cancel'+tr).val();
	 		var pro_id			=	jQuery('#sku'+tr).attr('data-p-id');

	 		all_data_array['sku/'+tr] 			=  sku;
			all_data_array['qty_order/'+tr] 	=  qty_order;
			all_data_array['qty_shipped/'+tr]	=  qty_shipped;
			all_data_array['qty_cancel/'+tr]	=  qty_cancel;
			all_data_array['pro_id/'+tr]		= pro_id;
	 	});
	 	jQuery("#ced_fruugo_marketplace_loader").show();
	 	// console.log(ced_order_localize);
	 	jQuery.post(
	 		ced_order_localize.ajaxUrl,
	 		{
	 			'action' : 'umb_fruugo_ship_order',
	 			'order_id' : order_id,
	 			'woo_order_id': wo_order_id,
	 			'trackNumber' : Tracking_num,
	 			'Tracking_url' : Tracking_url,
	 			'Message_to_customer': Message_to_customer,
	 			'Message_to_fruugo' : Message_to_fruugo,
	 			'all_data_array' : all_data_array,
	 		},
	 		function(response){
				// alert( response );
					//window.location.reload();
				jQuery("#ced_fruugo_marketplace_loader").hide();
				var response = jQuery.parseJSON(response);
				if(response.status == "200"){
				}else{
					//alert(response.status);
				}
				//window.location.reload();
			});
	 });
	/**
	 * ship to date timepicker.
	 */
	 jQuery('#umb_fruugo_ship_date_order').datetimepicker({
	 	dateFormat : 'yy-mm-dd',
	 	timeFormat: "hh:mm:ss",
	 	minDate: 0,
	 });

	 jQuery(document).on('blur', '.item_qty_shipped', function(){
	 	// alert(jQuery(this).attr('availabledata'));
	 	var avaible_qty = jQuery(this).attr('availabledata');
	 	var shipped_data = this.value;
	 	if(avaible_qty < shipped_data)
	 	{
	 		alert("Available quantity:"+avaible_qty);
	 		this.value = "";
	 		return;
	 	}
	 });
	  jQuery(document).on('blur', '.item_qty_cancel', function(){
	 	// alert(jQuery(this).attr('availabledata'));
	 	var avaible_qty = jQuery(this).attr('availabledata');
	 	var shipped_data = this.value;
	 	if(avaible_qty < shipped_data)
	 	{
	 		alert("Available quantity:"+avaible_qty);
	 		this.value = "";
	 		return;
	 	}
	 });
	});