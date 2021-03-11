<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
//header file.
require_once CED_FRUUGO_DIRPATH.'admin/pages/header.php';

$fruggo_license = get_option('ced_fruugo_lincense',false);
$fruggo_license_key = get_option('ced_fruugo_lincense_key',false);
$fruggo_license_module = get_option('ced_fruugo_lincense_module',false);
$license_valid = apply_filters("ced_fruugo_license_check", false);
$saved_fruugo_details = get_option( 'ced_fruugo_details', array() );
$marketPlaceName = 'fruugo';
// if( $license_valid )
// {
	?>
	<div class="ced_fruugo_wrap">
		<h2 class="ced_fruugo_setting_header ced_fruugo_bottom_margin"><?php echo $marketPlaceName;?> Configuration</h2>
		<div>
			<form method="post" class="ced_fruugo_marketplace_configuration" >
				<input type="hidden" name="ced_fruugo_marketplace_configuration" value="1" >
				<?php 
				if( !empty( $saved_fruugo_details ) )
				{
					foreach ($saved_fruugo_details as $key1 => $value1) {
						$configSettings = apply_filters( 'ced_fruugo_render_marketplace_configuration_settings', array(), 'fruugo', $value1 ); 
						$configSettingsData = $configSettings;
						$configSettings = $configSettingsData['configSettings'];
						$showUpdateButton = false;
						}
						?>
						<!-- <div class="ced_fruugo_wrap"> -->
							<table class="wp-list-table widefat fixed striped ced_fruugo_config_table">
								<thead>
										
								</thead>
								<tbody>
								<?php
								foreach ($configSettings as $key => $value) {
									echo '<tr>';
										echo '<th class="manage-column">';
											echo $value['name'];
										echo '</th>';
										echo '<td class="manage-column">';
											if($value['type'] == 'text') {
												echo '<input id="'.$key.'" type="text" name="'.$key.'" value="'.$value['value'].'">';
											}
											do_action( 'ced_fruugo_render_different_input_type' , $value['type'], $value1);
										echo '</td>';
									echo '</tr>';
								}
								?>
								</tbody>
							</table>
						<!-- </div> -->
						<?php
					// }
				}
				else
				{
					$configSettings = apply_filters( 'ced_fruugo_render_marketplace_configuration_settings', array(), 'fruugo', array() ); 
						$configSettingsData = $configSettings;
						$configSettings = $configSettingsData['configSettings'];
						$showUpdateButton = false;
						
						?>
						<!-- <div class="ced_fruugo_wrap"> -->
							<table class="wp-list-table widefat fixed striped ced_fruugo_config_table">
								<thead>
										
								</thead>
								<tbody>
								<?php
								foreach ($configSettings as $key => $value) {
									echo '<tr>';
										echo '<th class="manage-column">';
											echo $value['name'];
										echo '</th>';
										echo '<td class="manage-column">';
											if($value['type'] == 'text') {
												echo '<input id="'.$key.'" type="text" name="'.$key.'" value="'.$value['value'].'">';
											}
											do_action( 'ced_fruugo_render_different_input_type' , $value['type'], array());
										echo '</td>';
									echo '</tr>';
								}
								?>
								</tbody>
							</table>
						<!-- </div> -->
						<?php
				}
			 	?>
				
			</form>
			
		</div>

		 <div class="fruugo_static_url">
				<span class="fruggo_static_url_span">Static Url need to provide to fruugo : </span>
				<span><?php echo home_url().'/wp-content/uploads/cedcommerce_fruugouploads/Merchant.csv';   ?></span>
				<span><a href="<?php echo home_url().'/wp-content/uploads/cedcommerce_fruugouploads/Merchant.csv';   ?>">Click Here To Download CSV file</a></span>
			</div>
			<?php 
				// $marketPlaceName = 'fruugo';
				// do_action("ced_".$marketPlaceName."_additional_configuration", $marketPlaceName);
			?>
	<div>
	<?php
	
// }else{
// 	do_action("ced_fruugo_license_panel");
// }