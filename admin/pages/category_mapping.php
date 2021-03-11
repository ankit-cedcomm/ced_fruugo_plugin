<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}


//header file.
// require_once CED_FRUUGO_DIRPATH.'admin/pages/header.php';

$activated_marketplaces	 = fruugoget_enabled_marketplaces();

?>
<div id="ced_fruugo_marketplace_loader" class="loading-style-bg" style="display: none;">
	<img src="<?php echo plugin_dir_url(__dir__);?>/images/BigCircleBall.gif">
</div>
<?php 
$activated_marketplaces[] = 'fruugo';
if(is_array($activated_marketplaces) && !empty($activated_marketplaces)){
	$count = 1;
	echo '<div class="ced_fruugo_wrap">';
	foreach($activated_marketplaces as $marketplace){
		
		$file_path = CED_FRUUGO_DIRPATH.'marketplaces/'.$marketplace.'/partials/ced-fruugo-cat-mapping.php';
		if(file_exists($file_path)){
			require_once $file_path;
		}else{
			if($count == count($marketplace) && !$mappingRequired){
				//_e('This process is not required for currently activated marketplaces.','ced-fruugo');
			}
		}
		$count++;
	}
	echo '</div>';
}else{
	_e('<h3>You need to enable the fruugo from the CONFIGURATION tab.</h3>','ced-fruugo');
} 