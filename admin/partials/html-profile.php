<div class="ced_fruugo_overlay">
	<div class = "ced_fruugo_hidden_profile_section ced_fruugo_wrap">
		<p class="ced_fruugo_button_right">
			<span class="ced_fruugo_overlay_cross ced_fruugo_white_txt">X</span>
		<p>
		<h2 class="ced_fruugo_setting_header"><?php _e("Select profile for this product","ced_fruggo");?></h2>
		<label class="ced_fruugo_white_txt"><?php _e('Available Profile','ced_fruggo');?></label>
			<?php 
			global $wpdb;
			$wpdb->prefix.CED_FRUUGO_PREFIX.'_fruugoprofiles';

			$table_name = $wpdb->prefix.CED_FRUUGO_PREFIX.'_fruugoprofiles';
			//echo $table_name;die;
			$query = "SELECT `id`, `name` FROM `$table_name` WHERE `active` = 1";
			$profiles = $wpdb->get_results($query,'ARRAY_A');
			if(count($profiles)){?>
			<select class="ced_fruugo_profile_select">
				<option value="0"> --<?php _e('select','ced-fruugo');?>-- </option>
			<?php 
				foreach($profiles as $profileInfo){
					$profileId = isset($profileInfo['id']) ? intval($profileInfo['id']) : 0;
					$profileName = isset($profileInfo['name']) ? $profileInfo['name'] : '';
					if($profileId){
						?>
						<option value = "<?php echo $profileId; ?>"><?php echo $profileName; ?></option>
						<?php 
					}
				}
				?>
				</select>
				<button type = "button" data-prodid = "" class="ced_fruugo_save_profile button button-ced_fruggo"><?php _e("Save profile")?></button>
				<?php 
			}else{
			?>
			<p class="ced_fruugo_white_txt"><?php _e('No any profile available to assign, please create a profile and came back to assign!','ced-fruugo');?></p>
		<?php }?>
	</div>
</div>