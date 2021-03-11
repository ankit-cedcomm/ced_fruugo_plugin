/**
 * 
 */
 jQuery(document).ready(function(){
 	jQuery(document.body).on('click', '#ced_fruugo_fetch_cat', function(){
 		var url = window.location.href;
 		jQuery('#ced_fruugo_marketplace_loader').show();
 		var data = {'action':'ced_fruugo_fetchCat',
 		'_nonce':'ced_fruugo_fetch'
 	};
 	jQuery.post(ajaxurl, data,function(response){
 		var response = jQuery.parseJSON(response);
 		if( response.status == '202' )
 		{
 			jQuery('#ced_fruugo_marketplace_loader').hide();
 			jQuery('<div class="ced_fruugo_current_notice notice notice-error"><p>'+response.response+'.</p></div>').insertAfter('.ced_fruugo_header_tabs');	
 		}
 		else
 		{
 			jQuery('#ced_fruugo_marketplace_loader').hide();
 			jQuery('<div class="ced_fruugo_current_notice notice notice-success"><p>Category Fetched successfully.</p></div>').insertAfter('.ced_fruugo_header_tabs');
 			window.location.href = url;
 		}
 	})
 })
 	jQuery(document.body).on('click', '.ced_fruugo_expand_fruugocat', function(){
 		jQuery(this).find('.ced_fruugo_category_loader').show();
 		var catID = jQuery(this).attr('data-catid');
 		var catLevel = jQuery(this).attr('data-catLevel');
 		var catName = jQuery(this).attr('data-catName');
 		var parentCatName = jQuery(this).attr('data-parentCatName');
 		var data = {'action':'ced_fruugo_fetchCat',
 		'_nonce':'ced_fruugo_fetch_next_level',
 		'catDetails': {'catID':catID,
 		'catLevel':catLevel,
 		'catName' : catName,
 		'parentCatName' : parentCatName
 	}
 };
 var midVal = parseInt(catLevel)+parseInt(1);
 for(i=1;i<=7;i++){
 	if(midVal < i){
 		jQuery('.ced_fruugo_'+i+'lvl').empty();
 	}
 }
 console.log( jQuery('.ced_umb_fruugo_fruugo_'+catLevel+'lvl').find('label.fruugo_cat_active') );
 jQuery('.ced_fruugo_'+catLevel+'lvl').children('li').css('background','linear-gradient(to bottom right, #72777c , rgb(152, 152, 152))');
 jQuery('.ced_fruugo_'+catLevel+'lvl').children('li').children('label').removeClass( 'fruugo_cat_active' );
 jQuery('.ced_fruugo_'+catLevel+'lvl').children('li').children('label').css('color','#ffffff');
 jQuery(this).parent().css('background','linear-gradient(to right bottom, #0073aa, #5898b7)');
 jQuery(this).addClass( 'fruugo_cat_active' );
 jQuery(this).css('color','#ffffff');
 var parentCatName = "";
 jQuery(document).find( '.fruugo_cat_active' ).each(function(){
 	parentCatName += jQuery(this).text();
 });
 jQuery.post(ajaxurl, data,function(response){
 	jQuery('.ced_fruugo_category_loader').hide();
 	var response = jQuery.parseJSON(response);
 	if(response.status == '200')
 	{
 		var savedCat = response.selectedCat;
 		var nextLevelCat = response.nextLevelCat;
 		var nextList = "<h1>Level "+midVal+" Categories</h1>";
 		var alreadysavedcat = response.savedCategories;
				// console.log( savedCat );
				jQuery.each( nextLevelCat, function( key, value )
				{
					// console.log( value );
					// console.log( value.length );
					// console.log( savedCat[value] );
					if( savedCat[value] )
					{
						var checkbox = "";
						var span = '<label class="ced_fruugo_expand_fruugocat " data-parentCatName="'+catName+'" data-catName="'+value+'" data-catId="'+midVal+'" data-catLevel = "'+midVal+'"> '+value+'> <img class="ced_fruugo_category_loader" src="'+ced_fruugo_cat.plugins_url+'admin/images/loading.gif" width="20px" height="20px"> </label>'
					}	
					else
					{
						var checked = "";
						jQuery.each(alreadysavedcat, function( indexsaved, valuesaved ) {
							if(valuesaved == parentCatName+value){
								checked = 'checked';
							}
						});
						checkbox = '<input type="checkbox" data-parentCatName="'+parentCatName+'" class="ced_fruugo_cat_select" id="'+value+'" name="'+value+'" value="'+value+'" '+checked+'  >';
						span = '<label for = "'+value+'" class="ced_fruugo_lab">'+value+'</label>';
					}
					nextList += '<li>'+checkbox+span+'</li>';	
				} );	

				jQuery('.ced_fruugo_'+midVal+'lvl').html(nextList);
			}
		})
})
 	jQuery(document.body).on('click', '.ced_fruugo_cat_select', function(){
 		var selectedCatName = '';
 		jQuery('#ced_fruugo_marketplace_loader').show();
 		if(jQuery(this).is(':checked'))
 		{
 			var selectedCatId = jQuery(this).val();
 			selectedCatName = jQuery(this).next('label').text();
 			selectedCatName = jQuery( this ).attr( 'data-parentCatName' ) + selectedCatName;
			// alert( selectedCatName );
			var data = {'action':'ced_fruugo_process_fruugo_cat',
			'_nonce':'ced_fruugo_save',
			'cat' : {'catID':selectedCatId,
			'catName':selectedCatName
		}
	}
}else{
	var selectedCatId = jQuery(this).val();
	var selectedCatName = jQuery(this).next('label').text();
 			selectedCatName = jQuery( this ).attr( 'data-parentCatName' ) + selectedCatName;
	var data = {'action':'ced_fruugo_process_fruugo_cat',
	'_nonce':'ced_fruugo_remove',
	'cat' : {'catID':selectedCatId,
	'catName':selectedCatName
}
}
}
jQuery.post(ajaxurl, data,function(response){
	jQuery(".ced_fruugo_current_notice").hide();
	var response = jQuery.parseJSON(response);
	if(response.status == '200'){
		console.log(response);
		jQuery('<div class="ced_fruugo_current_notice notice notice-success"><p>Category Saved successfully.</p></div>').insertAfter('.ced_fruugo_toggle');
	}
	else if(response.status == '500'){
		jQuery('<div class="ced_fruugo_current_notice notice notice-success"><p>Category Removed successfully.</p></div>').insertAfter('.ced_fruugo_toggle');

	 }
		else{
		jQuery('<div class="ced_fruugo_current_notice notice notice-error"><p>Category is already saved or some problem is there,please check and try again.</p></div>').insertAfter('.ced_fruugo_toggle');
	}
	jQuery('#ced_fruugo_marketplace_loader').hide();
});

});

 });