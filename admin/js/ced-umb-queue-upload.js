
jQuery(document).ready(function(){

	jQuery("#ced_fruugo_marketplace_loader").show();
	jQuery.ajax({
		url : ced_fruugo_upload_queue_script_js_ajax.ajax_url,
		type : 'post',
		data : {
			action : 'ced_fruugo_render_queue_upload_main_section',
			marketplaceId : 'fruugo'
		},
		success : function( response ) 
		{
			jQuery("#ced_fruugo_marketplace_loader").hide();
			jQuery('div#ced_fruugo_queue_upload_main_section').html(response);
		}
	});
});