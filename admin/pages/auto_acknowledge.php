<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

//header file.
// require_once CED_FRUUGO_DIRPATH.'admin/pages/header.php';

/* saving and getting values */
if(isset($_POST['save_chunk'])){
	$chunk_size=isset($_POST['chunk_size'])?$_POST['chunk_size']:'';
	update_option('_ced_frugo_chunk',$chunk_size);
}
if(isset($_POST['saveData'])) {
	$ced_fruugo_allow_access_to_dev = isset($_POST['ced_fruugo_allow_access_to_dev'])?'yes':'no';
	$ced_fruugo_cron_failure_msg = isset($_POST['ced_fruugo_cron_failure_msg'])?$_POST['ced_fruugo_cron_failure_msg']:'';
	
	$cronRelatedData = array(
		'ced_fruugo_allow_access_to_dev' => $ced_fruugo_allow_access_to_dev,
		'ced_fruugo_cron_failure_msg' => $ced_fruugo_cron_failure_msg
	);

	update_option('ced_fruugo_cronRelatedData', $cronRelatedData);

	global $ced_fruugo_helper;
	if( $ced_fruugo_cron_failure_msg=='' ) {
		$notice['message'] = __('Few Fields Are Missing. Please Fill Them Too.','ced-fruugo');
		$notice['classes'] = "notice notice-error";
		$validation_notice[] = $notice;
		$ced_fruugo_helper->umb_print_notices($validation_notice);
		unset($validation_notice);	
	}
	else {
		$notice['message'] = __('Data Saved Successfully.','ced-fruugo');
		$notice['classes'] = "notice notice-success";
		$validation_notice[] = $notice;
		$ced_fruugo_helper->umb_print_notices($validation_notice);
		unset($validation_notice);
	}
	$sync_now = isset($_POST['ced_fruugo_sync_inventory']) ? $_POST['ced_fruugo_sync_inventory'] : 0;
	
	update_option( 'ced_fruugo_sync_inventory' , $sync_now );
	// wp_clear_scheduled_hook('umb_advance_sync');
	// if(!empty($schedule_frequency) && $schedule_frequency){
	// 	wp_schedule_event(time(), $schedule_frequency, 'umb_advance_sync');
	// }
	// if($sync_now && $sync_now=="on"){
	// 	do_action('umb_advance_sync');
	// 	//use this action everywhere you want product detail sync on any framework..
	// }
}
$marketPlaces = fruugoget_enabled_marketplaces();
$cronRelatedData = get_option('ced_fruugo_cronRelatedData', false);

$ced_fruugo_allow_access_to_dev = ($cronRelatedData['ced_fruugo_allow_access_to_dev']=="yes")?'checked="checked"':'';
$ced_fruugo_cron_failure_msg = isset($cronRelatedData['ced_fruugo_cron_failure_msg'])?$cronRelatedData['ced_fruugo_cron_failure_msg']:'';
if( $ced_fruugo_cron_failure_msg == '') {
	$ced_fruugo_cron_failure_msg = __('This mail is to inform you that Woocommerce fruugo Integration plugin fails to fetch orders due to CRON failure at your server. Please contact your Developer or Service-Provider.','ced-fruugo');
}

$current_schedule = get_option('umb_auto_sync_frequency',true);

?>
<div class="ced_fruugo_wrap ced_frugo_others_sec">
	<div class="meta-box-sortables ui-sortable">
		<div class="ced_fruugo_bottom_padding ced_fruugo_bottom_margin">
			<h2 class="ced_fruugo_setting_header"><?php _e('Adjust Chunks Size','ced-fruugo');?></h2>
			<span class="ced_fruugo_white_txt"><?php _e('You can adjust your chunk size in your product upload.','ced-fruugo');?></span>
		</div>
			<table class="ced_fruugo_return_address wp-list-table widefat fixed striped activityfeeds" >
				<form method="post">
					<tbody>
						<tr>
							<th><?php _e('Chunk Size','ced-fruugo');?></th>
							<td>
								<?php 
								$chunk_size =get_option('_ced_frugo_chunk')!=''?get_option('_ced_frugo_chunk'):'';
									
								?>
								<input type="number" name="chunk_size" value="<?php echo $chunk_size; ?>">
									
							</td><td><input type='submit' name='save_chunk' value='Save Chunk'></td>
						</tr>
					</tbody>
				</form>
				</table>
		<div class="ced_fruugo_bottom_padding ced_fruugo_bottom_margin">
			<h2 class="ced_fruugo_setting_header"><?php _e('Auto Order Fetching','ced-fruugo');?></h2>
			<span class="ced_fruugo_white_txt"><?php _e('This information will be used in case of auto-acknowledgement using cron.','ced-fruugo');?></span>
		</div>
		<div class="ced_fruugo_return_address">
			
				<table class="ced_fruugo_return_address wp-list-table widefat fixed striped activityfeeds" >
					<tbody>
						<tr>
							<th><?php _e('Location Of Cron File','ced-fruugo');?></th>
							<td>
								<?php 
								$ced_fruugo_cron_file_path = WP_PLUGIN_DIR."/woocommerce-fruugo-integration/includes/class-ced-fruugo-cron.php";
									
								?>
								<input type="text" name="" value="<?php echo $ced_fruugo_cron_file_path; ?>" readonly >
									
								<?php
								if(is_array($marketPlaces) && !empty($marketPlaces))
								{
									 foreach ($marketPlaces as $marketPlace)
									{
										$ced_fruugo_cron_file_path = WP_PLUGIN_DIR."/ultimate-market-placebundle/marketplaces/$marketPlace/api/class-$marketPlace-ack-cron.php";
									?>
									<?php 
									}
								}?>
							</td>
						</tr>
						<!-- <tr>
							<th><?php _e('Notification Mail Content That Will Be Send To Admin In Case Cron Fails','ced-fruugo');?></th>
							<td><textarea name="ced_fruugo_cron_failure_msg" rows="5"><?php echo $ced_fruugo_cron_failure_msg; ?></textarea></td>
						</tr> -->
					</tbody>
				</table>
				<!-- <p class="ced_fruugo_button_right">
					<input class="button button-ced_fruggo" value="<?php _e('Save','ced-fruugo');?>" name="saveData" type="submit">
				</p> -->

				<form method="post">
				<div class="ced_fruugo_bottom_padding ced_fruugo_bottom_margin">
					<h2 class="ced_fruugo_setting_header"><?php _e('Add Scheduler','ced-fruugo');?></h2>
					<span class="ced_fruugo_white_txt"><?php _e('Adding schedular for updating the CsV with wp schedular','ced-fruugo');?></span>
				</div> 
				<table class="ced_fruugo_return_address wp-list-table widefat fixed striped activityfeeds" >
					<tr>
						<th ><?php _e('Select Duration','ced-fruugo')?></th>
						<td class="ced_fruugo_auto_ack">
							
							<select id="_umb_fruggo_id_scheduler" name="_umb_fruggo_id_scheduler" class="select short" style="">
				            <option value="null" selected="">--select--</option>
				            <option value="wp_1_wc_updater_cron_interval" <?php if (isset($_POST['_umb_fruggo_id_scheduler']) && $_POST['_umb_fruggo_id_scheduler']=="wp_1_wc_updater_cron_interval" ) echo 'selected="selected"' ?>>5 Minute</option>
				            <option value="ced_fruugo_10min" <?php if (isset($_POST['_umb_fruggo_id_scheduler']) && $_POST['_umb_fruggo_id_scheduler']=="ced_fruugo_10min" ) echo 'selected="selected"' ?>>10 Minute</option>
				            <option value="ced_fruugo_15min" <?php if (isset($_POST['_umb_fruggo_id_scheduler']) && $_POST['_umb_fruggo_id_scheduler']=="ced_fruugo_15min" ) echo 'selected="selected"' ?>>15 Minute</option>
				            <option value="ced_fruugo_30min" <?php if (isset($_POST['_umb_fruggo_id_scheduler']) && $_POST['_umb_fruggo_id_scheduler']=="ced_fruugo_30min" ) echo 'selected="selected"' ?>>30 Minute</option>
				            <option value="hourly" <?php if (isset($_POST['_umb_fruggo_id_scheduler']) && $_POST['_umb_fruggo_id_scheduler']=="hourly" ) echo 'selected="selected"' ?>>Hourly</option>
				            <option value="twicedaily" <?php if (isset($_POST['_umb_fruggo_id_scheduler']) && $_POST['_umb_fruggo_id_scheduler']=="twicedaily" ) echo 'selected="selected"' ?>>Twicedaily</option>
				            <option value="daily" <?php if (isset($_POST['_umb_fruggo_id_scheduler']) && $_POST['_umb_fruggo_id_scheduler']=="daily" ) echo 'selected="selected"' ?>>Daily</option>
				            <option value="monthly" <?php if (isset($_POST['_umb_fruggo_id_scheduler']) && $_POST['_umb_fruggo_id_scheduler']=="monthly" ) echo 'selected="selected"' ?>>Monthly</option>
				            </select>&nbsp;<label for="umb-sync-now"><?php _e(' Select Time to schedule your automatic CSV update.','ced-fruugo');?></label>
				            <br><br>
				            <input type="checkbox" name="ced_api_check" value="checked" <?php if(isset($_POST['ced_api_check']) && $_POST['ced_api_check']=='checked') echo 'checked' ?>>
							<label for="umb-sync-now"><?php _e('Check it to ON api inventory update.','ced-fruugo');?></label>
							<br><br>
							<input type='submit' name='submit' value='Schedule'>
								<?php
								if(isset($_POST['submit'])){

									if(isset($_POST['_umb_fruggo_id_scheduler']))
									{
									wp_clear_scheduled_hook('ced_fruugo_cron_job');	
									$time=isset($_POST['_umb_fruggo_id_scheduler'])?$_POST['_umb_fruggo_id_scheduler']:'';
									wp_schedule_event(time(),$time, 'ced_fruugo_cron_job');
	       							do_action('ced_fruugo_cron_job');
	       							 if (!isset($_POST['ced_api_check']) && $_POST['_umb_fruggo_id_scheduler']!='null')
									{
	       							echo '<b>Scheduler is Set.</b>';
	       							}
	       							}
	       							
	       						}

	       							 ?>
						</td>
					</tr>
				</table>
				</form>	
		</div>
	</div>	
</div>
