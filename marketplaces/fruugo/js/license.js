jQuery(document).ready(function(){
	jQuery("#ced_fruugo_save_license").click(function(){
		
		jQuery(".license_loading_image").show();
		jQuery(".licennse_notification").hide();
		
		var license_key = jQuery("#ced_fruugo_license_key").val();
		
		if(license_key == '' || license_key == null)
		{
			jQuery("#ced_fruugo_license_key").attr('style','border:1px solid red');
			jQuery(".license_loading_image").hide();
			return false;
		}
		else
		{
			jQuery("#ced_fruugo_license_key").removeAttr('style');
		}	
		
		jQuery("#ced_fruugo_marketplace_loader").show();

		var data = {	'action':'ced_fruugo_validate_licensce',
						'license_key':license_key,
					};
		
		
		jQuery.post(ajaxurl, data,function(data){
			
			jQuery(".license_loading_image").hide();
			jQuery("#ced_fruugo_marketplace_loader").hide();
			
			if(data.hasOwnProperty('response'))
        	{
				if(data['response'] == 'success')
				{
					jQuery('.licennse_notification').text('Validated');
					jQuery('.licennse_notification').attr('style','color:green');
					location.reload();
				}
				else
				{
					jQuery('.licennse_notification').text('Invalid License');
					jQuery('.licennse_notification').attr('style','color:red');
				}	
        	}
		},'json');
	});
});