<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
global $ced_fruugo_helper;
$current_page = 'umb-fruugo';

if(isset($_GET['page'])){
	$current_page = $_GET['page'];
}
?>
<div id="ced_fruugo_marketplace_loader" class="loading-style-bg" style="display: none;">
	<img src="<?php echo plugin_dir_url(__dir__);?>/images/BigCircleBall.gif">
</div>

<?php 
if($current_page =="umb-fruugo-main"){
	
	$activated_marketplaces = ced_fruugo_available_marketplace();
	if(isset($_POST['ced_fruugo_save_credentials_button'])){
		if($_POST['ced_fruugo_username_string'] == '' && $_POST['ced_fruugo_password_string'] == ''){
			$validation_notice = array();
			$notice['message'] = __('Please fill details','ced-fruugo');
			$notice['classes'] = "notice notice-error";
			$validation_notice[] = $notice;
		}else{

			$validation_notice = array();
			$notice['message'] = __('Configuration Setting Saved','ced-fruugo');
			$notice['classes'] = "notice notice-success";
			$validation_notice[] = $notice;
		}

	}
	if(isset($_POST['ced_fruugo_authorize'])){
		// error_reporting(~0);
			// ini_set('display_errors', 1);
		if($_POST['ced_fruugo_username_string'] == '' && $_POST['ced_fruugo_password_string'] == ''){
			$validation_notice = array();
			$notice['message'] = __('Please fill details','ced-fruugo');
			$notice['classes'] = "notice notice-error";
			$validation_notice[] = $notice;
		}else{
			$validate_fruugo = get_option('ced_validate_fruugo', true);
			$ced_fruugo_details = get_option('ced_fruugo_details',true);
			if($validate_fruugo == 'yes' && $ced_fruugo_details['userString'] != '' && $ced_fruugo_details['passString'] != '' ){
				$validation_notice = array();
				$notice['message'] = __('Validation Done','ced-fruugo');
				$notice['classes'] = "notice notice-success";
				$validation_notice[] = $notice;
			}
		}
	}
	
	if(isset($validation_notice) && count($validation_notice)){

		$ced_fruugo_helper->umb_print_notices($validation_notice);
		unset($validation_notice);
	}	
}
?>