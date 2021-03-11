
// Common toggle code

jQuery(document).ready(function(){

	setTimeout( function(){ jQuery('.ced_fruugo_current_notice').remove(); }, 5000 );

	jQuery( '#ced_fruugo_save_payment_details' ).on( 'click', function(e){
		e.preventDefault();
		var f = 0;
		jQuery( '.ced_fruugo_payment_method input' ).each(function(){
			var val = jQuery(this).val();
			if( val == "PayPal" && jQuery(this).is(':checked') ){
				f = 1;
				var email_add = jQuery("input[name='paymentdetails[paypalEmail]']").val();
				if( email_add == '' || email_add == null )
				{
					var errorHtml = '<div class="notice notice-error umb_paypal_error_email"><p>Please fill in the Paypal email address.</p></div>';
					jQuery('.ced_fruugo_return_address').children('form').append(errorHtml);
					setTimeout( function(){ jQuery( '.umb_paypal_error_email' ).remove() } , 3000 );
				}
				else
				{
					jQuery( '.ced_fruugo_save_payment_data' ).submit();
				}
			}
		});
		if( f == 0 )
		{
			jQuery( '.ced_fruugo_save_payment_data' ).submit();
		}
	} );

	jQuery(document).on('click','.ced_fruugo_toggle',function(){
		jQuery(this).next('.ced_fruugo_toggle_div').slideToggle('slow');
	});
	

	jQuery( document ).on( 'click', '.ced_fruugo_add_more_shop_section', function(){
		var repeatable = jQuery(this).parents('tr').clone();
		jQuery( repeatable ).insertAfter( jQuery( this ).parents('tr') );
		jQuery(this).parent( 'td' ).remove();
	} );

	jQuery( document ).on( 'click', '.ced_fruugo_add_more_shops', function(){
		// var count = jQuery(  );
		var repeatable = jQuery('.ced_fruugo_config_table:first').clone();
		jQuery( repeatable ).insertAfter( jQuery( '.ced_fruugo_config_table:last' ) );

		repeatable.find( '#ced_fruugo_keystring' ).val("");
		repeatable.find( '#ced_fruugo_shared_string' ).val("");
		repeatable.find( '#ced_fruugo_shop_name' ).val("");
		repeatable.find( '#ced_fruugo_user_name' ).val("");
		repeatable.find( '#ced_fruugo_upload_product_type' ).val("");
		// jrepeatable.wrap( '<div class="ced_fruugo_wrap"></div>' );
	} );

});
// Market Place JQuery End

//jquery for file status.
jQuery(document).ready(function(){
	jQuery(document).on('click','.ced_fruugo_updateFileInfo',function(){
		var requestId = jQuery(this).attr('requestid');
		var marketplace = jQuery(this).attr('framework');
		var fileId = jQuery(this).attr('fileId');
		if(!requestId.length || !marketplace.length || !fileId.length){
			alert("An unexpected error occured, please try again later.");
			return;
		}
		
		jQuery.post(
				common_action_handler.ajax_url,
				{
					'action': 'umb_get_file_status',
					'requestId' : requestId,
					'fileId' : fileId,
					'marketplace' : marketplace
				},
				function(response){
					alert(response);
				}
		);
	});
	
	jQuery(document).on('click','#ced_fruugo_import',function(){
		
		jQuery('#ced_fruugo_marketplace_loader').show();
		jQuery.post(
				common_action_handler.ajax_url,
				{
					'action': 'ced_fruugo_import',
				},
				function(response)
				{
					alert(response);
				}
		);
	})
	jQuery(document).on('change','.ced_fruugo_select_cat_profile',function(){
		jQuery(".umb_current_cat_prof").remove();
		var currentThis = jQuery(this);
		var catId  = jQuery(this).parent('td').attr('data-catId');
		var profId = jQuery(this).find(':selected').val();

		if(catId == null || typeof catId === "undefined" || catId == null || profId == "" || typeof profId === "undefined" || profId == null || profId == "--Select Profile--")
		{
			return;
		}
		jQuery('#ced_fruugo_marketplace_loader').show();
		jQuery.post(
				common_action_handler.ajax_url,
				{
					'action': 'ced_fruugo_select_cat_prof',
					'catId' : catId,
					'profId' : profId,
				},
				function(response)
				{
					jQuery('#ced_fruugo_marketplace_loader').hide();
					response = jQuery.parseJSON(response);
					if(response.status == "success" && response.profile != 'Profile not selected')
					{
						currentThis.parent('td').next('td').text(response.profile);
						var successHtml = '<div class="notice notice-success umb_current_cat_prof ced_fruugo_current_notice"><p>Profile Assigned Successfully.</p></div>';
						jQuery('.ced_fruugo_wrap').find('.ced_fruugo_setting_header').append(successHtml);
						// setTimeout( function(){ jQuery( '.ced_fruugo_current_notice' ).remove(); }, 4000 );
					}
					else{
						currentThis.parent('td').next('td').text(response.profile);
						var errorHtml = '<div class="notice ced_fruugo_current_notice notice-error umb_current_cat_prof"><p>Profile Removed!</p></div>';
						jQuery('.ced_fruugo_wrap').find('.ced_fruugo_setting_header').append(errorHtml);
						// setTimeout( function(){ jQuery( '.ced_fruugo_current_notice' ).remove(); }, 4000 );
					}
				}
		);
	});	
	jQuery(document).on('click','.ced_fruugo_product_status',function(){
		jQuery('#ced_fruugo_marketplace_loader').show();
		var pId = jQuery(this).attr('data-id');
		var marketplace = jQuery(this).attr('data-marketplace');
		var ths = jQuery(this);
		jQuery.post(
				common_action_handler.ajax_url,
				{
					'action': 'ced_fruugo_current_product_status',
					'prodId' : pId,
					'marketplace' : marketplace
				},
				function(response)
				{
					jQuery('#ced_fruugo_marketplace_loader').hide();
					var html = "<p>"+response+"</p>";
					ths.replaceWith(html);
				}
		);
	})
	
	jQuery("#umb_bulk_act_category").change(function(){
		var catid = jQuery(this).val();
		jQuery.post(
				common_action_handler.ajax_url,
				{
					'action': 'ced_fruugo_select_cat_bulk_upload',
					'catId' : catid,
				},
				function(response)
				{
					if(response.result == 'success')
					{
						var product = response.data;
						var preselect = jQuery("#umb_bulk_act_product").val();
						var option = '';
						for(key in product)
						{
							select = '';
							if(preselect)
							{	
								if(preselect.indexOf(key) != -1)
								{
									select='selected="selected"';
								}	
							}
							option += '<option value="'+key+'" '+select+'>'+product[key]+'</option>';
						}	
						jQuery("#umb_bulk_act_product").html(option);
						jQuery("#umb_bulk_act_product").select2();

						jQuery("#umb_bulk_act_product_select").html(option);
						jQuery("#umb_bulk_act_product_select").select2();
					}	
				},
				'json'
			);	
	});
	
	jQuery( document ).on( 'click', '.ced_fruugo_import_to_store' ,function(){
		jQuery('#ced_fruugo_marketplace_loader').show();
		var itemId = jQuery( this ).attr( 'data-itemid' );
		var $this = jQuery( this );
		jQuery.ajax({
			url : common_action_handler.ajax_url,
			type: 'post',
			data:{
				action : 'ced_fruugo_import_to_store',
				itemId : itemId
			},
			success: function(response){
				console.log(response);
				jQuery('#ced_fruugo_marketplace_loader').hide();

				if(response == "Product Imported Successfully")
				{
					var successHtml = '<div class="notice notice-success umb_current_cat_prof"><p>'+response+'</p></div>';
					jQuery('.ced_fruugo_pages_wrapper').children('form').append(successHtml);
					jQuery( '.ced_fruugo_product_'+itemId ).remove();
				}
				else{
					var errorHtml = '<div class="notice notice-error umb_current_cat_prof"><p>'+response+'</p></div>';
					jQuery('.ced_fruugo_pages_wrapper').children('form').append(errorHtml);
				}
			}
		});
	} );

	jQuery( document ).on( 'click', '.ced_fruugo_import_submit', function(e){
		e.preventDefault();
		jQuery('#ced_fruugo_marketplace_loader').show();
		var itemId = [];
		jQuery('.ced_fruugo_select_product_for_import').each(function(key) {
		    if( jQuery( this ).is( ':checked' ) )
		    {
		    	itemId[key] = jQuery( this ).val();
		    }
		});

		jQuery.ajax({
			url : common_action_handler.ajax_url,
			type: 'post',
			data:{
				action : 'ced_fruugo_bulk_import_to_store',
				itemId : itemId
			},
			datatype: 'json',
			success: function(response){
				jQuery('#ced_fruugo_marketplace_loader').hide();
				response = jQuery.parseJSON( response );
				console.log(response);
				if( response.message == 'Success' )
				{
					if( response.imported_items.length > 0 ){
						var successHtml = '<div class="notice notice-success umb_current_cat_prof">';
						var i = 1;
						jQuery.each( response.imported_items, function( key, value ){
							successHtml += '<p>'+i+'- '+' Product '+value+' successfully imported </p>';
							i++;
							jQuery( '.ced_fruugo_product_'+value ).remove();
						} );
						successHtml == '</div>';
						jQuery('.ced_fruugo_pages_wrapper').children('form').append(successHtml);
					}

					if( response.not_imported_items.length > 0 ){
						var failureHtml = '<div class="notice notice-error umb_current_cat_prof">';
						i = 1;
						jQuery.each( response.not_imported_items, function( key, value ){
							failureHtml += '<p>'+i+'- '+' Product '+value+' not imported </p>';
							i++;
						} );
						failureHtml == '</div>';
						jQuery('.ced_fruugo_pages_wrapper').children('form').append(failureHtml);
					}
				}else{
					var errorHtml = '<div class="notice notice-error umb_current_cat_prof"><p>'+response.message+'</p></div>';
					jQuery('.ced_fruugo_pages_wrapper').children('form').append(errorHtml);
				}
			}
		});
	} );

	jQuery(document.body).on('click', '#ced_fruugo_fetch_description_templates', function(e){
		e.preventDefault();
		jQuery('.ced_fruugo_template_fetch_loader').show();
		var data = {'action':'ced_fruugo_fetch_templates',
					'_nonce':'ced_fruugo_fetch_templates'
					};
		jQuery.post(ajaxurl, data,function(response){

			jQuery('.ced_fruugo_template_fetch_loader').hide();
			if( response != '' )
			{
				var l = '<select id="umb_product_template" name="umb_product_template" class="select short" style="">';
				jQuery.each( response , function( key, value ){
					l += '<option value="'+value.ID+'">'+value.Name+'</option>';
				} );
				l += '</select><span class="ced_fruugo_template_fetch_loader"><img class="ced_fruugo_circle_img" src="'+common_action_handler.plugin_url+'admin/images/circle.png"></span><div class="ced-fruugo-template-preview"></div>';
				
			}
			jQuery( '.ced_umb_custom_template' ).html( l );
		},
		'json')
	});

	// jQuery( document ).on( 'click', '.ced_fruugo_display_feedback', function(e){
	// 	e.preventDefault();
	// 	jQuery( document ).find( '.ced_fruugo_feedback_message' ).remove();
	// 	var message = jQuery( this ).attr( 'data-messagecontent' );
	// 	var id = jQuery( this ).attr( 'data-id' );
	// 	var htm = '<tr class="ced_fruugo_feedback_message"><td colspan="5">'+message+'</td></tr>';
	// 	jQuery( this ).closest( 'tr' ).after( htm );
	// } );
	jQuery(".ced_scr_wrapper1").scroll(function(){
        jQuery(".ced_scr_wrapper2")
            .scrollLeft(jQuery(".ced_scr_wrapper1").scrollLeft());
    });
    jQuery(".ced_scr_wrapper2").scroll(function(){
        jQuery(".ced_scr_wrapper1")
            .scrollLeft(jQuery(".ced_scr_wrapper2").scrollLeft());
    });
});