

jQuery(document).ready(function() {
	jQuery( document ).find( '#_umb_fruugo_category' ).select2();

	jQuery( '.ced_fruugo_profile_metakeys_li' ).on( 'mouseover', function(){
		jQuery( '.ced_fruugo_sel_metakey' ).css( 'box-shadow', "0 0 1em #888888" );
	} );
	jQuery( '.ced_fruugo_profile_basic_detail_li' ).on( 'mouseover', function(){
		jQuery( '.ced_fruugo_basic_set' ).css( 'box-shadow', "0 0 1em #888888" );
	} );
	jQuery( '.ced_fruugo_profile_required_field_li' ).on( 'mouseover', function(){
		jQuery( '.ced_fruugo_req_field' ).css( 'box-shadow', "0 0 1em #888888" );
	} );
	jQuery( '.ced_fruugo_profile_category_li' ).on( 'mouseover', function(){
		jQuery( '.ced_fruugo_category_sel' ).css( 'box-shadow', "0 0 1em #888888" );
	} );

	jQuery( '.ced_fruugo_profile_metakeys_li' ).on( 'mouseout', function(){
		jQuery( '.ced_fruugo_sel_metakey' ).css( 'box-shadow', "" );
	} );
	jQuery( '.ced_fruugo_profile_basic_detail_li' ).on( 'mouseout', function(){
		jQuery( '.ced_fruugo_basic_set' ).css( 'box-shadow', "" );
	} );
	jQuery( '.ced_fruugo_profile_required_field_li' ).on( 'mouseout', function(){
		jQuery( '.ced_fruugo_req_field' ).css( 'box-shadow', "" );
	} );
	jQuery( '.ced_fruugo_profile_category_li' ).on( 'mouseout', function(){
		jQuery( '.ced_fruugo_category_sel' ).css( 'box-shadow', "" );
	} );

	jQuery( '.ced_fruugo_save_profile_button' ).on( 'click', function(e){
		e.preventDefault();
		var f = 0;
		var p_name = jQuery( '.ced_fruugo_profile_name' ).val();
		
		if( p_name == "")
		{
			jQuery( '.ced_fruugo_profile_required_field_li' ).css( 'background-color', "#00a9ff" );
		}
		else
		{
			jQuery( '.ced_fruugo_profile_save_form' ).submit();
		}

	} );



	var p_name = jQuery( '.ced_fruugo_profile_name' ).val();
	if( p_name == "" )
	{
		jQuery( '.ced_fruugo_profile_basic_detail_li' ).css( 'background-color', "#00a9ff" );
	}
	else
	{
		jQuery( '.ced_fruugo_profile_basic_detail_li' ).css( 'background-color', "rgb(46, 182, 131)" );
	}

	var who_made = jQuery('#_ced_fruugo_who_made').val();
	var supply = jQuery( '#_ced_fruugo_is_supply' ).val();
	var when_made = jQuery( '#_ced_fruugo_when_made' ).val();

	if( who_made == "" || supply == "" || when_made == "" )
	{
		jQuery( '.ced_fruugo_profile_required_field_li' ).css( 'background-color', "#00a9ff" );
	}
	else if( who_made != "" && supply != "" && when_made != "" )
	{
		jQuery( '.ced_fruugo_profile_required_field_li' ).css( 'background-color', "rgb(46, 182, 131)" );
	}

	var category = jQuery( '#_umb_fruugo_category' ).val();
	if( category == "" )
	{
		jQuery( '.ced_fruugo_profile_category_li' ).css( 'background-color', "#00a9ff" );
	}
	else
	{
		jQuery( '.ced_fruugo_profile_category_li' ).css( 'background-color', "rgb(46, 182, 131)" );
	}

	setInterval(function()
	{
		var p_name = jQuery( '.ced_fruugo_profile_name' ).val();
		if( p_name == "" )
		{
			jQuery( '.ced_fruugo_profile_basic_detail_li' ).css( 'background-color', "#00a9ff" );
		}
		else
		{
			jQuery( '.ced_fruugo_profile_basic_detail_li' ).css( 'background-color', "rgb(46, 182, 131)" );
		}

		var who_made = jQuery('#_ced_fruugo_who_made').val();
		var supply = jQuery( '#_ced_fruugo_is_supply' ).val();
		var when_made = jQuery( '#_ced_fruugo_when_made' ).val();

		if( who_made == "" || supply == "" || when_made == "" )
		{
			jQuery( '.ced_fruugo_profile_required_field_li' ).css( 'background-color', "#00a9ff" );
		}
		else if( who_made != "" && supply != "" && when_made != "" )
		{
			jQuery( '.ced_fruugo_profile_required_field_li' ).css( 'background-color', "rgb(46, 182, 131)" );
		}

		var category = jQuery( '#_umb_fruugo_category' ).val();
		if( category == "" )
		{
			jQuery( '.ced_fruugo_profile_category_li' ).css( 'background-color', "#00a9ff" );
		}
		else
		{
			jQuery( '.ced_fruugo_profile_category_li' ).css( 'background-color', "rgb(46, 182, 131)" );
		}

	}, 5000);
	

	jQuery( '.ced_fruugo_profile_timeline_ul li' ).on( 'click', function(){
		var target = jQuery( this ).attr( 'data-target' );

	    jQuery('html,body').animate({
	        scrollTop: jQuery("#"+target).offset().top},
	        'slow');

	    jQuery( '#'+target ).css( 'box-shadow', "0 0 1em #888888" );
	    setTimeout( function(){ jQuery( "#"+target ).css( 'box-shadow', "" ); }, 5000 );

	} );	

	jQuery(document.body).on( 'click', 'input:checkbox[class=ced_fruugo_add_del_meta_keys]', function() {
		if(jQuery(this).is(':checked')) {
			var metaKey = jQuery(this).attr('id');
			updateMetaKeysInDBForProfile( metaKey, 'append' );
		}
		else {
			var metaKey = jQuery(this).attr('id');
			updateMetaKeysInDBForProfile( metaKey, 'delete' );
		}
	});
});	

/**
* updating fruugo categories
* 
*/
function updateMetaKeysInDBForProfile( metaKey , actionToDo ) {
	jQuery("#ced_fruugo_marketplace_loader").show();
	jQuery.ajax({
		url : ced_fruugo_profile_edit_add_script_AJAX.ajax_url,
		type : 'post',
		data : {
			action : 'ced_fruugo_updateMetaKeysInDBForProfile',
			metaKey : metaKey,
			actionToDo : actionToDo
		},
		success : function( response ) 
		{
			jQuery("#ced_fruugo_marketplace_loader").hide();
		}
	});
}


jQuery(document).ready(function() {
    
    jQuery('#ced_fruugo_metakeys_list').DataTable({
    	"pageLength": 10,
    	"aaSorting": [ ],
    	"columnDefs": [ {
			"targets": 1,
			"orderable": false
			} ]
    });

});

jQuery(document).ready(function() {
    
    jQuery(document.body).on('click','div.ced_fruugo_tabbed_head_wrapper ul li', function(){
    	
    	jQuery(this).siblings('li.active').removeClass();
    	jQuery(this).addClass('active');
    	var currentIndex = jQuery( this ).index();
    	var nextDivRef = jQuery(this).parents('div.ced_fruugo_tabbed_head_wrapper').next();
    	var v = jQuery("div",jQuery(nextDivRef)).eq( currentIndex ).html();
    	jQuery("div",jQuery(nextDivRef)).eq( currentIndex ).siblings('div.active').removeClass('active');
    	jQuery("div",jQuery(nextDivRef)).eq( currentIndex ).addClass('active');
    
    });

});

jQuery(document).ready(function() {

	jQuery(document.body).on('click','table#ced_fruugo_products_matched td', function(){
    	var selectedProductId = jQuery(this).attr('product-id');
    	jQuery("#selected_product_id").val(selectedProductId);
    	var productName = jQuery(this).text();
    	jQuery("#ced_fruugo_pro_search_box").val(productName);
    	jQuery("#ced_fruugo_suggesstion_box").hide();
		jQuery("#ced_fruugo_suggesstion_box").html('');
    	renderMarketplaceAttributesSectionHTML( jQuery(this), selectedProductId, jQuery('input#profileID').val() );
    });

	function renderMarketplaceAttributesSectionHTML( thisRef, selectedProductId, profileID ) {
		jQuery.ajax({
			url : ced_fruugo_profile_edit_add_script_AJAX.ajax_url,
			type : 'post',
			data : {
				action : 'fetch_all_meta_keys_related_to_selected_product',
				selectedProductId : selectedProductId,
				profileID : profileID
			},
			success : function( response ) {
				
				jQuery('div#ced_fruugo_metakeys_list_wrapper').replaceWith(response);
				jQuery('#ced_fruugo_metakeys_list').DataTable({
			    	"pageLength": 10,
			    	"aaSorting": [ ],
			    	"columnDefs": [ {
						"targets": 1,
						"orderable": false
						} ]
			    });

			}
		});
	}

});

/*** product search ***/

var ced_fruugo_currentRequest = null;

jQuery(document.body).on('keyup',"#ced_fruugo_pro_search_box",function(){

	if(jQuery("#ced_fruugo_pro_search_box").val() == "") {
		jQuery("#ced_fruugo_suggesstion_box").hide();
		jQuery("#ced_fruugo_suggesstion_box").html('');
		return false;
	}

	jQuery(".ced_fruugo_ajax_pro_search_loader").show();

	ced_fruugo_currentRequest = jQuery.ajax({
		url : ced_fruugo_profile_edit_add_script_AJAX.ajax_url,
		type : 'post',
		data : {
			action : 'ced_fruugo_searchProductAjaxify',
			term : jQuery(this).val()
		},
		beforeSend : function() {           
			if(ced_fruugo_currentRequest != null) {
				ced_fruugo_currentRequest.abort();
			}
		},		
		success : function( data ) {	
			jQuery(".ced_fruugo_ajax_pro_search_loader").hide();

			jQuery("#ced_fruugo_suggesstion_box").show();
			jQuery("#ced_fruugo_suggesstion_box").html(data);
		}

	});

	if(jQuery(this).val() == '') {
		jQuery("#ced_fruugo_suggesstion_box").hide();
		jQuery("#ced_fruugo_suggesstion_box").html('');
	}

});

jQuery(document.body).on('click','span.ccas_pro_cross_class',function() {
	jQuery("#ced_fruugo_suggesstion_box").hide();
	jQuery("#ced_fruugo_suggesstion_box").html('');
	jQuery("#ced_fruugo_pro_search_box").val("");
});