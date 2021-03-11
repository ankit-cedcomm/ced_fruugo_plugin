<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$marketPlaces = array('fruugo');
$marketPlace = is_array($marketPlaces) && !empty($marketPlaces) ? $marketPlaces[0] : -1;
$marketplace = isset($_REQUEST['section']) ? $_REQUEST['section'] : $marketPlace;

//product listing class.
require_once CED_FRUUGO_DIRPATH.'admin/helper/class-ced-umb-product-listing.php';
//feed manager helper class for handling bulk actions.
require_once CED_FRUUGO_DIRPATH.'admin/helper/class-feed-manager.php';
//header file.
// require_once CED_FRUUGO_DIRPATH.'admin/pages/header.php';

$notices = array();

if(isset($_POST['doaction'])){

	check_admin_referer('bulk-ced_fruugo_mps');
	
	$action = isset($_POST['action']) ? $_POST['action'] : -1;

	$marketPlaces = fruugoget_enabled_marketplaces();
	/*$marketPlace = is_array($marketPlaces) && !empty($marketPlaces) ? $marketPlaces[0] : -1;
	$marketplace = isset($_REQUEST['section']) ? $_REQUEST['section'] : $marketPlace;*/
	$marketplace = "fruugo";
	$proIds = isset($_POST['post']) ? $_POST['post'] : array();
	$allset = true;
	
	if(empty($action) || $action== -1){
		$allset = false;
		$message = __('Please select the bulk actions to perform action!','ced-fruugo');
		$classes = "error is-dismissable";
		$notices[] = array('message'=>$message, 'classes'=>$classes);
	}
	//echo $marketplace;die;
	if(empty($marketplace) || $marketplace== -1){
		$allset = false;
		$message = __('Any marketplace is not activated!','ced-fruugo');
		$classes = "error is-dismissable";
		$notices[] = array('message'=>$message, 'classes'=>$classes);
	}
	
	if(!is_array($proIds)){
		$allset = false;
		$message = __('Please select products to perform bulk action!','ced-fruugo');
		$classes = "error is-dismissable";
		$notices[] = array('message'=>$message, 'classes'=>$classes);
	}
	if($allset){
		
		if( class_exists( 'CED_FRUUGO_feed_manager' ) ){

			$feed_manager = CED_FRUUGO_feed_manager::get_instance();
			$notice = $feed_manager->process_feed_request($action,$marketplace,$proIds);
			$notice_array = json_decode($notice,true);
			if(is_array($notice_array)){
				
				$message = isset($notice_array['message']) ? $notice_array['message'] : '' ;
				$classes = isset($notice_array['classes']) ? $notice_array['classes'] : 'error is-dismissable';
				$notices[] = array('message'=>$message, 'classes'=>$classes);
			}else{
				
				$message = __('Unexpected error encountered, please try again!','ced-fruugo');
				$classes = "error is-dismissable";
				$notices[] = array('message'=>$message, 'classes'=>$classes);
			}
		}
	}
}

if(count($notices))
{
	foreach($notices as $notice_array)
	{
		$message = isset($notice_array['message']) ? esc_html($notice_array['message']) : '';
		$classes = isset($notice_array['classes']) ? esc_attr($notice_array['classes']) : 'error is-dismissable';
		if(!empty($message))
		{?>
			 <div class="<?php echo $classes;?>">
			 	<?php echo $message;?>
			 </div>
		<?php 	
		}
	}
	unset($notices);
}

$availableMarketPlaces =array("fruugo");
if(is_array($availableMarketPlaces) && !empty($availableMarketPlaces)) {
	$section = $availableMarketPlaces[0];
	if(isset($_GET['section'])) {
		$section = esc_attr($_GET['section']);
	}
	$product_lister = new CED_FRUUGO_product_lister();
	$product_lister->prepare_items();
	?>
	<div class="ced_fruugo_wrap">
		<?php do_action("ced_fruugo_manage_product_before_start");?>
		
		<h2 class="ced_fruugo_setting_header"><?php _e('Manage Products','ced-fruugo'); ?></h2>
		
		<?php do_action("ced_fruugo_manage_product_after_start");?>
		
		<form method="get" action="">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<?php 
			$name = "";
			$sku = "";
			if( isset( $_GET['ced_fruugo_search_by'] ) && $_GET['ced_fruugo_search_by'] == 'name' )
			{
				$name = 'selected';
			}
			else if( isset( $_GET['ced_fruugo_search_by'] ) && $_GET['ced_fruugo_search_by'] == 'sku' )
			{
				$sku = 'selected';
			}
			?>
			<select name="ced_fruugo_search_by">
				<option value="name" <?php echo $name; ?>><?php _e( 'Search by Product Name', 'ced-umb-fruugo' ); ?></option>
				<option value="sku" <?php echo $sku; ?>><?php _e( 'Search by Sku', 'ced-umb-fruugo' ); ?></option>
			</select>
			<?php $product_lister->search_box('Search Products', 'search_id');?>
		</form>
		<?php fruugorenderMarketPlacesLinksOnTop('umb-fruugo-pro-mgmt'); ?>

		<form method="get" action="">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<?php
			/** Sorting By Status  **/
			$status_actions = array(
				'published'    => __( 'Uploaded', 'ced-fruugo' ),
				'notUploaded'    => __( 'Not Uploaded', 'ced-fruugo' ),
			);
			$previous_selected_status = isset($_GET['status_sorting']) ? $_GET['status_sorting'] : '';
		 	
			
			$product_categories = get_terms( 'product_cat', array('hide_empty'=>false) );
		 	$temp_array = array();
		 	foreach ($product_categories as $key => $value) {
		 		$temp_array[$value->term_id] = $value->name;
		 	}
		 	$product_categories = $temp_array;
		 	$previous_selected_cat = isset($_GET['pro_cat_sorting']) ? $_GET['pro_cat_sorting'] : '';
		 	

		 	$product_types = get_terms( 'product_type', array('hide_empty'=>false) );
		 	$temp_array = array();
		 	foreach ($product_types as $key => $value) {
		 		if( $value->name == 'simple' || $value->name == 'variable' ) {
		 			$temp_array[$value->term_id] = ucfirst($value->name);
		 		}
		 	}
		 	$product_types = $temp_array;
		 	$previous_selected_type = isset($_GET['pro_type_sorting']) ? $_GET['pro_type_sorting'] : '';
		 	

			echo '<div class="ced_fruugo_top_wrapper">';
				echo '<select name="status_sorting">';
				echo '<option value="">' . __( 'Product Status', 'ced-fruugo' ) . "</option>";
				foreach ( $status_actions as $name => $title ) {
					$selectedStatus = ($previous_selected_status == $name) ? 'selected="selected"' : '';
					$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
					echo '<option '.$selectedStatus.' value="' . $name . '"' . $class . '>' . $title . "</option>";
				}
				echo "</select>";

				echo '<select name="pro_cat_sorting">';
				echo '<option value="">' . __( 'Product Category', 'ced-fruugo' ) . "</option>";
				foreach ( $product_categories as $name => $title ) {
					$selectedCat = ($previous_selected_cat == $name) ? 'selected="selected"' : '';
					$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
					echo '<option '.$selectedCat.' value="' . $name . '"' . $class . '>' . $title . "</option>";
				}
				echo "</select>";

				echo '<select name="pro_type_sorting">';
				echo '<option value="">' . __( 'Product Type', 'ced-fruugo' ) . "</option>";
				foreach ( $product_types as $name => $title ) {
					$selectedType = ($previous_selected_type == $name) ? 'selected="selected"' : '';
					$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
					echo '<option '.$selectedType.' value="' . $name . '"' . $class . '>' . $title . "</option>";
				}
				echo "</select>";

				submit_button( __( 'Filter', 'ced-fruugo' ), 'action', '', false, array() );
			echo '</div>';
			?>
		</form>

		<form id="ced_fruugo_products" method="post">
		<?php $product_lister->views(); ?> 	
		<?php ?>	
		
		<?php $product_lister->display() ?>
		</form>
		<?php if($product_lister->has_items()):?>
			<?php  $product_lister->inline_edit(); ?>
		<?php endif;?>
			<?php  $product_lister->profle_section(); ?>
	</div>
	<?php
}
else{
	_e('<h3>You need to enable the fruugo from the CONFIGURATION tab</h3>','ced-fruugo');
}
?>