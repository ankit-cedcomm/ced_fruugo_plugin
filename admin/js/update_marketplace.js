
jQuery(document).ready(function(){
	jQuery(document.body).on('click','span.ced_fruugo_update_marketplace', function(){
		var marketplaceId = jQuery(this).attr('marketplaceId');
		jQuery("#ced_fruugo_marketplace_loader").show();
		jQuery.ajax({
			url : ced_fruugo_update_marketplace_script_AJAX.ajax_url,
			type : 'post',
			data : {
				action : 'do_marketplace_folder_update',
				marketplaceId : marketplaceId
			},
			success : function( response ) 
			{
				jQuery("#ced_fruugo_marketplace_loader").hide();
				if(response == "200"){
					$html = '<div class="ced_fruugo_current_notice notice notice-success"><p>Package updated successfully</p></div>';
				}
				else if(response == "100"){
					$html = '<div class="ced_fruugo_current_notice notice notice-warning"><p>Update not available</p></div>';
				}
				else{
					$html = '<div class="ced_fruugo_current_notice notice notice-error"><p>Can not be updated</p></div>';
				}
				jQuery('.wrap').append($html);
				setTimeout( function(){ jQuery( '.ced_fruugo_current_notice' ).remove(); }, 4000 );
			}
		});
	});
});