<?php
// If this file is called directly, abort.

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if(isset($_GET['FileNotFound']) && $_GET['FileNotFound']=='true')
{	
	echo '<div class="notice notice-error"><p>No File Exists !!</p></div>';
}
else if(isset($_GET['export']) && $_GET['export']=="true")
{	
	$uploadDir = wp_upload_dir()['basedir'];
	$folder = $uploadDir.'/cedcommerce_fruugouploads/';
	$filename = $folder.'Merchant.csv';
	if(file_exists($filename))
	{	
		$uploadDir = wp_upload_dir()['baseurl'];
		$folder = $uploadDir.'/cedcommerce_fruugouploads/';
		$filename = $folder.'Merchant.csv';
		wp_redirect($filename);
	}
	else
	{	
		wp_redirect('admin.php?page=umb-fruugo-bulk-action&section=csv_upload_section&FileNotFound=true');
	}	
	
}
//header file.
// require_once CED_FRUUGO_DIRPATH.'admin/pages/header.php';
$arrayOfLinks = array( 
	'bulk_profile_assignment' => __('Bulk Profile Assignment', 'ced-fruugo'),
	'bulk_product_upload' => __('Bulk Product Upload', 'ced-fruugo'),
	'csv_upload_section'	=> __('CSV export/import', 'ced-fruugo'),
	// 'bulk_product_upload_queue'	=> __('Upload Your Queue', 'ced-fruugo')	
);
$counter = 1;
$page = 'umb-fruugo-bulk-action';
(isset($_GET['section'])) ? $section = esc_attr($_GET['section']) : $section = 'bulk_profile_assignment';

echo '<div class="ced_fruugo_wrap">';
echo '<ul class="subsubsub">';
foreach ($arrayOfLinks as $linkKey => $linkName) {
	($section == $linkKey) ? $class = 'current' : $class = '';
	$redirectURL = get_admin_url()."admin.php?page=".$page."&amp;section=".$linkKey;
	echo '<li>';
	echo '<a href="'.$redirectURL.'" class="'.$class.'">'.strtoupper($linkName).'</a>'; 
	if($counter < count($arrayOfLinks) ){ 
		echo '|'; 
	}
	echo '</li>';
	$counter++;
}
echo '</ul>';

global $wpdb;
$product_categories = get_terms( 'product_cat');
$table_name = $wpdb->prefix.CED_FRUUGO_PREFIX.'_fruugoprofiles';
$query = "SELECT `id`, `name` FROM `$table_name` WHERE `active` = 1";
$profiles = $wpdb->get_results($query,'ARRAY_A');
$getSavedvalues = get_option('ced_fruugo_category_profile', false);

?>
<?php
// Bulk Profile Assignment Section
if( $section == 'bulk_profile_assignment' ) {
	?>
	<div id="ced_fruugo_marketplace_loader" class="loading-style-bg" style="display: none;">
		<img src="<?php echo plugin_dir_url(__dir__);?>/images/BigCircleBall.gif">
	</div>
	<h2 class="ced_fruugo_setting_header"><?php _e('Assign Profile To Category','ced-fruugo');?></h2>
	<div class="ced_fruugo_category_profile_mapping wrap">
		<table class="wp-list-table widefat fixed striped ced_fruugo_mps">
			<tr>
				<th><b><?php _e('Category','ced-fruugo');?></b></th>
				<th><b><?php _e('Slug','ced-fruugo');?></b></th>
				<th><b><?php _e('Select Profile','ced-fruugo');?></b></th>
				<th><b><?php _e('Selected Profile','ced-fruugo');?></b></th>
			</tr>
			<?php 
			if(is_array($product_categories) && !empty($product_categories))
			{
				foreach ($product_categories as $key => $product_category)
				{ 
					?>
					<tr>
						<td><?php _e($product_category->name)?></td>
						<td><?php _e($product_category->slug)?></td>
						<td data-catId = '<?php echo $product_category->term_id?>'>
							<select class="ced_fruugo_select_cat_profile">
								<option value="removeProfile"><?php _e('--Select Profile--', 'ced-fruugo')?></option>
								<?php 
								if(is_array($profiles) && !empty($profiles))
									{ ?>

										<?php 
										$selected = '';
										foreach ($profiles as $profile)
										{
											if( $profile['id'] == $getSavedvalues[$product_category->term_id] )
											{
												$selected= "selected";
											}
											?>
											<option value="<?php echo $profile['id'];?>" <?php echo $selected; ?>><?php _e($profile['name'], 'ced-fruugo');?></option>
											<?php 
											$selected = '';
										}
									}
									?>
								</select>
							</td>
							<td>
								<?php 
								if( is_array( $getSavedvalues ) && !empty( $getSavedvalues ) ){
									$getSavedvalues = array_filter($getSavedvalues);
								}
								if(is_array($getSavedvalues) && !empty($getSavedvalues))
								{
									$f = 0;
									foreach ($getSavedvalues as $catID => $profID)
									{
										if($catID == $product_category->term_id)
										{
											$f = 1;
											if(is_array($profiles) && !empty($profiles))
											{
												foreach ($profiles as $profile)
												{
													if($profile['id'] == $profID)
													{
														echo $profile['name'];
													}
												}
											}
										}
									}
									if( $f == 0 ){
										echo __( "Profile Not selected", 'ced-fruugo' );
									}
								}
								else {
									echo __( "Profile Not selected", 'ced-fruugo' );
								}
								?>
							</td>
						</tr>
						<?php 
					}
				}?>
			</table>
		</div>
		<?php 
	}
// Bulk Product Upload Section
	if( $section == 'bulk_product_upload' ) {
		$notices = array();
		if( isset($_POST['ced_fruugo_bulk_upload_submit']) || isset($_POST['save_bulk_action']) ) {
			$bulk = array();
			if(isset($_POST['umb_bulk_act_category'])) {
				if( in_array("all", $_POST['umb_bulk_act_category'])) {
					$all_cat = get_terms('product_cat',array('hide_empty'=>0));
					$cat_ids_array = array();
					foreach ($all_cat as $key => $cat) {
						$cat_ids_array[] = $cat->term_id;
					}
					$bulk['cat'] = $cat_ids_array;
				}
				else {
					$bulk['cat'] = $_POST['umb_bulk_act_category'];
				}
			}
			if(isset($_POST['umb_bulk_act_product'])) {
				$bulk['ex_product'] = $_POST['umb_bulk_act_product'];
			}
			if(isset($_POST['umb_bulk_act_product_select'])) {
				$bulk['select_product'] = $_POST['umb_bulk_act_product_select'];
			}
			update_option('ced_fruugo_cat_bulk', $bulk);
		// echo "<div class='notice notice-success is-dismissable'>".__( "Saved", 'ced-fruugo' )."</div>";
			if(isset($_POST['save_bulk_action'])){
				$notices['message'] = 'Setting Saved.';
				$notices['classes'] = 'notice notice-success is-dismissable';
			}

			if(isset($notices) && count($notices))
			{
				$message = isset($notices['message']) ? esc_html($notices['message']) : '';
				$classes = isset($notices['classes']) ? esc_attr($notices['classes']) : 'error is-dismissable';
				if(!empty($message))
					{?>
						<div class="<?php echo $classes;?>">
							<?php echo $message;?>
						</div>
						<?php 	
					}

					unset($notices);
				}
			}	

			if(isset($_POST['ced_fruugo_bulk_upload_submit'])) {

				$assigndCatprofiles = get_option('ced_fruugo_category_profile', false);

				$marketPlace = $_POST['ced_fruugo_bulk_upload_marketplace'];
				if( !empty( $marketPlace ) ) {
					$prodetail   = 	get_option('ced_fruugo_cat_bulk', false);
					$selectedcategories = $prodetail['cat'];
					$proArraytoUpload =array();

					$select_product = isset($prodetail['select_product']) && is_array($prodetail['select_product']) ? $prodetail['select_product'] : array();
					if( !empty($select_product) ) {
						$proArraytoUpload = $select_product;
					}
					else {
						// $tax_query['taxonomy'] = 'product_cat';
						// $tax_query['field'] = 'id';
						// $tax_query['terms'] = $selectedcategories;
						// $tax_queries[] = $tax_query;
						$args = array( 'post_type' => 'product','fields' =>'ids', 'posts_per_page' => -1, 'orderby' => 'rand' );

						$loop = new WP_Query( $args );
						// while ( $loop->have_posts() ) { 
						// 	$loop->the_post(); 
						// 	global $product;
						// 	$product_id = $loop->post->ID;
						// 	$excludedArray = isset($prodetail['ex_product']) && is_array($prodetail['ex_product']) ? $prodetail['ex_product'] : array();

						// 	if(!in_array($product_id, $excludedArray))
						// 	{
						// 		$product_title = $loop->post->post_title;
						// 		$products[$product_id] = $product_title;
						// 		$proArraytoUpload[] = $product_id;
						// 		$terms = get_the_terms( $product_id, 'product_cat' );
						// 		if(isset($terms) && !empty($terms))
						// 		{	
						// 			foreach ($terms as $term)
						// 			{
						// 				$termId = $term->term_id;
						// 				if(is_array($assigndCatprofiles)){

						// 					foreach ($assigndCatprofiles as $key => $value)
						// 					{
						// 						if($termId == $key)
						// 						{
						// 							update_post_meta($product_id, "ced_fruugo_profile", $assigndCatprofiles[$key]);
						// 						}
						// 					}
						// 				}
						// 			}
						// 		}
						// 	}
						// }
					}

					if( $marketPlace && $marketPlace != "null" ) {
						require_once CED_FRUUGO_DIRPATH."/marketplaces/$marketPlace/class-fruugo.php";
						$classname = "CED_FRUUGO_manager";
						$marketPlacemanager = new  $classname;
						$response = $marketPlacemanager->upload($loop);
						$notice = $response;
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

			$bulk = get_option('ced_fruugo_cat_bulk', false);
			$selected_cat = array();
			$selected_pro = array();
			if(isset($bulk['cat'])) {	
				$selected_cat = $bulk['cat'];
			}
			if(isset($bulk['ex_product'])) {
				$selected_pro = $bulk['ex_product'];
			}
			if(isset($bulk['select_product'])) {
				$select_product = $bulk['select_product'];
			}	

			$products = array();
			// if(isset($selected_cat) && !empty($selected_cat)) {
			// 	$tax_query['taxonomy'] = 'product_cat';
			// 	$tax_query['field'] = 'id';
			// 	$tax_query['terms'] = $selected_cat;
			// 	$tax_queries[] = $tax_query;
			// 	$args = array( 'post_type' => 'product', 'posts_per_page' => -1, 'tax_query' => $tax_queries, 'orderby' => 'rand' );
			// 	$loop = new WP_Query( $args );
			// 	while ( $loop->have_posts() ) : 
			// 		$loop->the_post(); 
			// 		$product_id = $loop->post->ID;
			// 		$product_title = $loop->post->post_title;
			// 		$products[$product_id] = $product_title;
			// 	endwhile;
			// }

			/* get all profiles */
			global $wpdb;
			$prefix = $wpdb->prefix . CED_FRUUGO_PREFIX;
			$tableName = $prefix.'_fruugoprofiles';
			$sql = "SELECT `id`,`name`,`active`,`marketplace` FROM `$tableName` ORDER BY `id` DESC";
			$result = $wpdb->get_results($sql,'ARRAY_A');
			$profiles_array = array();
			foreach ($result as $key => $value) {
				$profiles_array[$value['id']] = $value['name'];
			}

			$selected_profiles = array();
			if(isset($notices) && count($notices))
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

				?>

				<h2 class="ced_fruugo_setting_header ced_fruugo_bottom_margin"><?php _e('Bulk Upload', 'ced-fruugo')?></h2>
				<form method="post">
					<table class="wp-list-table widefat fixed striped ced_fruugo_bulk_action" >
						<tbody>

							<tr class="ced_fruugo_select_categories">
								<th><?php _e('Select Categories', 'ced-fruugo');?></th>
								<td>
									<?php 
									/* get woocommerce categories */
									$cat_name = ARRAY();
									$all_cat = get_terms('product_cat',array('hide_empty'=>0));
									if($all_cat)
									{
										$cat_name['all'] = __("Select All", 'ced-fruugo');
										foreach ($all_cat as $cat)
										{
											$cat_name[$cat->term_id] = $cat->name;
										}
									}
									else 
									{
										$cat_name = ARRAY();
										$cat_name[] = __('No categories', 'ced-fruugo');
									}
									?>
									<select name="umb_bulk_act_category[]" id="umb_bulk_act_category" multiple>
										<?php 
										foreach($cat_name as $k=>$val)
										{	
											$select = "";
											if(in_array($k, $selected_cat))
											{
												$select = 'selected="selected"';
											}
											?>
											<option value="<?php echo $k?>" <?php echo $select;?>><?php echo $val;?></option>
											<?php 
										}
										?>	
									</select>
								</td>
							</tr>

							<tr class="ced_fruugo_select_products">
								<th><?php _e('Select Products', 'ced-fruugo');?></th>
								<td>
									<select name="umb_bulk_act_product_select[]" id="umb_bulk_act_product_select" multiple>
										<?php
										if(isset($products) && !empty($products))
										{ 
											foreach($products as $k=>$val)
											{	
												$select = "";
												if(in_array($k, $select_product))
												{
													$select = 'selected="selected"';
												}
												?>
												<option value="<?php echo $k?>" <?php echo $select;?>><?php echo $val;?></option>
												<?php 
											}
										}
										?>	
									</select>
								</td>
							</tr>

							<tr class="ced_fruugo_exclude_products">
								<th><?php _e('Exclude Products', 'ced-fruugo');?></th>
								<td>
									<select name="umb_bulk_act_product[]" id="umb_bulk_act_product" multiple>
										<?php
										if(isset($products) && !empty($products))
										{ 
											foreach($products as $k=>$val)
											{	
												$select = "";
												if(in_array($k, $selected_pro))
												{
													$select = 'selected="selected"';
												}
												?>
												<option value="<?php echo $k?>" <?php echo $select;?>><?php echo $val;?></option>
												<?php 
											}
										}
										?>	
									</select>
								</td>
							</tr>

						</tbody>
					</table>
					<p class="ced_fruugo_button_right">
						<?php 
						$activeMarketplaces= fruugoget_enabled_marketplaces();
						?>	
						<select name = "ced_fruugo_bulk_upload_marketplace" class="ced_fruugo_bulk_upload_marketplace">
							<option value = ""><?php _e('-- Select --','ced-fruugo');?></option>
							<?php 
							foreach ($activeMarketplaces as $activeMarketplace)
								{ ?>
									<option value = "<?php echo $activeMarketplace?>" selected>
										<?php echo $activeMarketplace;?>
									</option>
									<?php 	
								}
								?>
							</select>
							<input type = "submit" name = "ced_fruugo_bulk_upload_submit" class="button button-ced_fruggo ced_fruugo_bulk_upload_submit" value ="<?php _e('Upload','ced-fruugo');?>">
							<input type="submit" value="<?php _e('Save changes','ced-fruugo');?>" class="button button-ced_fruggo" name="save_bulk_action">
						</p>		
					</form>

				<?php 
				}
				// CSV Upload
				$filestore='';
				$display='none';
				$progress='none';
				$upload_csv_btn='block';
				$productArr = [];
				if(isset($_POST['ced_fruugo_plugin_csv_submit_button'])){
				 $filename   = isset( $_FILES['ced_fruugo_plugin_csvToUpload']['name'] ) ? sanitize_text_field( $_FILES['ced_fruugo_plugin_csvToUpload']['name'] ) : false;
				 $filename=trim($filename," ");
				 $extention  = pathinfo( $filename, PATHINFO_EXTENSION );
				 $filetype   = isset( $_FILES['ced_fruugo_plugin_csvToUpload']['type'] ) ? sanitize_text_field( $_FILES['ced_fruugo_plugin_csvToUpload']['type'] ) : false;
				$filesize   = isset( $_FILES['ced_fruugo_plugin_csvToUpload']['size'] ) ? sanitize_text_field( $_FILES['ced_fruugo_plugin_csvToUpload']['size'] ) : false;
				$filetemp   = isset( $_FILES['ced_fruugo_plugin_csvToUpload']['tmp_name'] ) ? sanitize_text_field( $_FILES['ced_fruugo_plugin_csvToUpload']['tmp_name'] ) : false;
				$upload     = wp_upload_dir();
				
				$upload_dir = $upload['basedir'];
				$filestore  = $upload_dir .'/'. $filename . '';
				
				if ( 'csv' == $extention ) {
	
					move_uploaded_file( $filetemp, $filestore );
				}

				if(file_exists($filestore)){
					$display="block";
					$upload_csv_btn="none";
					echo "<h3 id='ced_fruugo_plugin_csv_success'>CSV Uploaded click to Upload_Products Button to continue .</h3>";
					}
				}
				if( $section == 'csv_upload_section' ) {

					?>
					<style>
						 #Progress_Status { 
							  width: 60%; 
							  background-color: #ddd;
							  } 
							  
							  #myprogressBar {
							  	padding: 2%;
							    width: 0%; 
							    text-align: center;
							    height: 20px; 
							    background: linear-gradient(45deg, #d8dee3, #1d77b8);

							  } #progress-div{
							  	margin-top: 14%;
							  	 margin-left: 20%;
							  }	
					</style>
					<form method="post" enctype="multipart/form-data">
					<div class="meta-box-sortables ui-sortable">
						<h3 id="ced_fruugo_plugin_csv_module_instruction_heading">
							<span>+</span>
							<?php _e('Instructions To Use CSV Module','ced-fruugo');?>
						</h3>

						<div id="ced_fruugo_plugin_csv_module_instruction">
							<p><?php _e('1. Export the format of CSV by clicking the <b>Export CSV Format</b> below.','ced-fruugo'); ?></p>
							<p><?php _e('2. Use the exported CSV to fill values.','ced-fruugo'); ?></p>
							<p><?php _e('4. Finally, click the upload button and let the magic begin.','ced-fruugo');?></p>
						</div>

						<div id="ced_fruugo_plugin_csv_module_main">
							<p>
								<label class="ced_fruugo_plugin_label_class">
									<?php _e('Get CSV Format Here','ced-fruugo');?>
								</label> 
								<a class="button button-ced_fruggo" href="admin.php?page=umb-fruugo-bulk-action&section=csv_upload_section&export=true">Export CSV Format</a>
							</p>

							<p>
								<label class="ced_fruugo_plugin_label_class">
									<?php _e('Select CSV To Upload','ced-fruugo'); ?>
								</label>
								<input type="file" name="ced_fruugo_plugin_csvToUpload" id="ced_fruugo_plugin_csvToUpload">
								<label class="browse_label" for="ced_fruugo_plugin_csvToUpload"><?php _e('Browse','ced-fruugo'); ?></label>
								<label id="ced_fruugo_plugin_csv_file_name"><?php _e('No File Selected','ced-fruugo'); ?></label>
							</p>
							<button class="button button-ced_fruggo" name="ced_fruugo_plugin_csv_submit_button" style="display:<?php echo $upload_csv_btn?>"><?php _e('Upload_CSV','ced-fruugo'); ?></button>
						</div>
						<div id="ced_fruugo_plugin_csv_processing_div">
							<img src="<?php echo CED_FRUUGO_URL.'/admin/css/clock-loading.gif';?>">
						</div>
					</div>	
					</form>
			<!--  -->
					<button id="ced_fruugo_plugin_csv_product_submit_button" class="button button-ced_fruggo" style="display:<?php echo $display ?>;" data-path="<?php echo $filestore ?>"><?php _e('Upload_Products','ced-fruugo'); ?></button>
					<?php
					echo '</div>';	?>
					<div id="progress-div" style="display:<?php echo $progress ?>">
						<h2 id="h2-progress">CSV Product's Uploading...</h2>
						<div id="Progress_Status"> 
						  <div id="myprogressBar"></div> 
						</div>
					</div> <?php
				}

				if( $section == 'bulk_product_upload_queue' ) {
					$notices = array();

					if( isset($_POST['ced_fruugo_queue_upload_button']) ) {
						$selectedMarketPlace = isset($_POST['ced_fruugo_marketplace_for_queue_upload']) ? $_POST['ced_fruugo_marketplace_for_queue_upload'] : 'fruugo';
			// print_r( $selectedMarketPlace );die;
						if( $selectedMarketPlace ) {
							$ced_fruugo_delete_queue_after_upload  = isset($_POST['ced_fruugo_delete_queue_after_upload']) ? 'yes' : 'no';
							update_option( 'ced_fruugo_delete_queue_after_upload_'.$selectedMarketPlace, $ced_fruugo_delete_queue_after_upload );

							$items_in_queue = get_option( 'ced_fruugo_'.$selectedMarketPlace.'_upload_queue', array() );
							if( $ced_fruugo_delete_queue_after_upload == 'yes' ) {
								delete_option( 'ced_fruugo_'.$selectedMarketPlace.'_upload_queue' );
							}
							if( $selectedMarketPlace ) {
								require_once CED_FRUUGO_DIRPATH."/marketplaces/$selectedMarketPlace/class-fruugo.php";
								$classname = "CED_FRUUGO_manager";
								$marketPlacemanager = new  $classname;
								$response = $marketPlacemanager->upload($items_in_queue);
								$notice = $response;
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

					$activeMarketplaces= fruugoget_enabled_marketplaces();
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
						?>
						<form method="POST" id="ced_fruugo_queue_upload_main_section_form">
							<div id="ced_fruugo_queue_upload_main_section">
							</div>
						</form>

						<?php
					}
					?>	
