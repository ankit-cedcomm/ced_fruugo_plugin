
jQuery(document).ready(function(){
	
	jQuery(document.body).on('click','h3#ced_fruugo_plugin_csv_module_instruction_heading',function() {
		jQuery(this).toggleClass("open");
		jQuery('div#ced_fruugo_plugin_csv_module_instruction').slideToggle();
		if(jQuery('h3#ced_fruugo_plugin_csv_module_instruction_heading span').html() == '+')
		{
			jQuery('h3#ced_fruugo_plugin_csv_module_instruction_heading span').html('-');
		}
		else
		{
			jQuery('h3#ced_fruugo_plugin_csv_module_instruction_heading span').html('+');
		}	
	});	

	jQuery(document.body).on('click','button#ced_fruugo_plugin_close_csv_import_report',function(event) {
		event.stopPropagation(); // Stop stuff happening
        event.preventDefault(); // Totally stop stuff happening
		jQuery("div#ced_fruugo_plugin_csv_processing_div").show().delay(500).fadeOut(function(){
			jQuery("div#ced_fruugo_plugin_csv_processing_div").html('<img src="'+ced_fruugo_csv_upload_script_js_ajax.loading_image+'">');
			jQuery("div#ced_fruugo_plugin_csv_processing_div").removeClass("ced_fruugo_plugin_success_id");
			jQuery("button#ced_fruugo_plugin_csv_submit_button").attr('disabled',false);
		});
	});	

	jQuery(document.body).on('click','button#ced_fruugo_plugin_download_error_log',function(event) {
		event.stopPropagation(); // Stop stuff happening
        event.preventDefault(); // Totally stop stuff happening
		
		var fileName = jQuery(this).attr("data-link");
		jQuery.ajax({
			url : ced_fruugo_csv_upload_script_js_ajax.ajax_url,
			type : 'post',
			data : {
				action : 'ced_fruugo_plugin_csv_import_export_module_download_error_log',
				fileName : fileName
			},
			success : function( reportHTML ) 
			{
				alert(reportHTML);
				jQuery("div#ced_fruugo_plugin_report_module_processed_data").html(reportHTML);
			}
		});	
	});

});	

jQuery(function($)
{
	// Variable to store your files
	var files;

	// Add events
	$('input#ced_fruugo_plugin_csvToUpload[type=file]').on('change', ced_fruugo_plugin_prepareUpload);
	$('button#ced_fruugo_plugin_csv_product_submit_button').on('click', ced_fruugo_plugin_uploadFiles);

	// Grab the files and set them to our variable
	function ced_fruugo_plugin_prepareUpload(event)
	{
		$("div#ced_fruugo_plugin_csv_processing_div").hide();
		$("div#ced_fruugo_plugin_csv_processing_div").html('<img src="'+ced_fruugo_csv_upload_script_js_ajax.loading_image+'">');
		$("div#ced_fruugo_plugin_csv_processing_div").removeClass();
		$("button#ced_fruugo_plugin_csv_submit_button").attr('disabled',false);

		files = event.target.files;
		
		var fileName = files[0].name;
        var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
        
        $("label#ced_fruugo_plugin_csv_file_name").html(fileName);

        if(fileNameExt != "csv")
        {
        	var htmlToRender = "<h3 id='ced_fruugo_plugin_csv_failure'>Please upload a .csv file only</h3>";
			$("div#ced_fruugo_plugin_csv_processing_div").html(htmlToRender);
			$("div#ced_fruugo_plugin_csv_processing_div").addClass("ced_fruugo_plugin_failure_id");
			$("div#ced_fruugo_plugin_csv_processing_div").show().delay(2000).fadeOut(function(){
				$("div#ced_fruugo_plugin_csv_processing_div").html('<img src="'+ced_fruugo_csv_upload_script_js_ajax.loading_image+'">');
				$("#ced_fruugo_plugin_csvToUpload").val("");
				$("label#ced_fruugo_plugin_csv_file_name").html('No File Selected');
				$("div#ced_fruugo_plugin_csv_processing_div").removeClass("ced_fruugo_plugin_failure_id");
			});
        }	

		return;
	}

	// Catch the form submit and upload the files
	function ced_fruugo_plugin_uploadFiles(event)
	{
		$("#ced_fruugo_plugin_csv_success").hide();
		event.stopPropagation(); // Stop stuff happening
        event.preventDefault(); // Totally stop stuff happening
        var count=0;
        $("#upload_csv_btn").hide();
         filepath = $(this).attr("data-path");
         offset=1;
         limit=100;
         width=1;
         perform_bulk_action(filepath,offset,limit,width);

    }
    function perform_bulk_action(filepath,offset,limit,width){
    		$("#progress-div").css('display','block');
	    	$.ajax({
	        	url : ced_fruugo_csv_upload_script_js_ajax.ajax_url,
				type : 'post',
				data :{ 
				action:'ced_fruugo_csv_import_export_module_read_csv',
				filepath:filepath,
				offset:offset,
				limit:limit,
				},
		        success : function( data ) 
				{
					console.log(data);
					var data = data.split(",");
					var offset=Number(data[2]);
					var limit=Number(data[2])+100;
					var totalsize=Number(data[3]);
					var leftsize=Number(data[4]);
					 var element = document.getElementById("myprogressBar");  
					    if (width >= 100) { 
					    } else { 
					      element.style.width = width + '%';
					      element.innerHTML= width + '%';
					     $("#myprogressBar").css('color', 'red');
						width++;
				
					    } 

					if(totalsize>=leftsize){
						perform_bulk_action(filepath,offset,limit,width);
					}else{
						 element.style.width ='100%';
					     element.innerHTML= '100% Product Uploaded Successfully';
					     $("#h2-progress").html('Product Uploaded Successfully');
					}

					
				}
	        
	    })

	}
})