
jQuery(document).ready(function(){
	jQuery(document.body).on("click",".ced_fruugo_profile",function(){
		var prodId = jQuery(this).attr("data-proid");
		jQuery(".ced_fruugo_save_profile").attr("data-prodid",prodId);
		jQuery(".ced_fruugo_overlay").show();
	});


	jQuery(document.body).on("click",".ced_fruugo_overlay_cross",function(){
		jQuery(".ced_fruugo_overlay").hide();
	})
	jQuery(document.body).on("click",".umb_remove_profile",function(){

		var proId     = jQuery(this).attr("data-prodid");
		jQuery("#ced_fruugo_marketplace_loader").show();
		var profileId = 0;
		var data  = {
						"action"    : "ced_fruugo_save_profile",
						"proId"     : proId,
						"profileId" : profileId
					}
		jQuery.post(
					profile_action_handler.ajax_url,
					data,
					function(response)
					{
						jQuery("#ced_fruugo_marketplace_loader").hide();

						jQuery(".ced_fruugo_overlay").hide();
						if(response != "success")
						{
							alert("Failed");
						}
						else
						{
							window.location.reload();
						}	
					}
				)
			  .fail(function() {
				  jQuery("#ced_fruugo_marketplace_loader").hide();
				  alert( "Failed" );

			  })
	})
	
	jQuery(document.body).on("click",".ced_fruugo_save_profile",function(){

		var proId     = jQuery(this).attr("data-prodid");
		jQuery("#ced_fruugo_marketplace_loader").show();

		var profileId = jQuery(".ced_fruugo_profile_select option:selected").val();
		var data  = {
						"action"    : "ced_fruugo_save_profile",
						"proId"     : proId,
						"profileId" : profileId
					}
		jQuery.post(
					profile_action_handler.ajax_url,
					data,
					function(response) {
						jQuery("#ced_fruugo_marketplace_loader").hide();

						jQuery(".ced_fruugo_overlay").hide();
						if(response != "success") {
							alert("Failed");
						}
						else {
							window.location.reload();
						}	
					}
				)
			  .fail(function() {
				  jQuery("#ced_fruugo_marketplace_loader").hide();
				  alert( "Failed" );
			  });
	});


	/*
	* JS CODE TO ADD PRODUCT TO QUEUE TO UPLOAD
	*/
	jQuery(document.body).on( 'click', '.ced_fruugo_marketplace_add_to_upload_queue_123', function(){
		jQuery("#ced_fruugo_marketplace_loader").show();
		jQuery.ajax({
			url : profile_action_handler.ajax_url,
			type : 'post',
			data : {
				action : 'ced_fruugo_add_product_to_upload_queue_on_marketplace',
				marketplaceId : jQuery(this).attr('data-marketplace'),
				productId : jQuery(this).attr('data-id')
			},
			success : function( response ) 
			{
				jQuery("#ced_fruugo_marketplace_loader").hide();
				jQuery('.ced_fruugo_pages_wrapper').children('form').append('<div class="ced_fruugo_current_notice ced_fruugo_location_saved_notice notice notice-success"><p>Successfully Added to Uploading Queue.</p></div>');
				setTimeout( function(){ jQuery( '.ced_fruugo_location_saved_notice' ).remove(); }, 3000 );
			}
		});
	});
	/* JS CODE FOR ALLOWING SPLIT VARIATION VARIATION FOR VARIATION PRODUCTS */
	jQuery(document.body).on( 'click', '.ced_fruugo_marketplace_allow_split_variation', function(){
		jQuery("#ced_fruugo_marketplace_loader").show();
		jQuery.ajax({
			url : profile_action_handler.ajax_url,
			type : 'post',
			data : {
				action : 'ced_fruugo_marketplace_allow_split_variation',
				marketplaceId : jQuery(this).attr('data-marketplace'),
				productId : jQuery(this).attr('data-id')
			},
			success : function( response ) 
			{
				jQuery("#ced_fruugo_marketplace_loader").hide();
				jQuery('.ced_fruugo_pages_wrapper').children('form').append('<div class="ced_fruugo_current_notice ced_fruugo_location_saved_notice notice notice-success"><p>Successfully Added for Split Variation.</p></div>');
				setTimeout( function(){ jQuery( '.ced_fruugo_location_saved_notice' ).remove(); }, 3000 );
			}
		});
	});

});


