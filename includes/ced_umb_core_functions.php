<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
* This function fetches metakeys of a product and render them in table form on profile page.
* @name fruggorenderMetaKeysTableOnProfilePage()
* @author CedCommerce <plugins@cedcommerce.com>
* @link  http://www.cedcommerce.com/
*/
function fruggorenderMetaKeysTableOnProfilePage($productId) {
	error_reporting(~0);
            ini_set('display_errors', 1);
	/* fetching previously selected metakeys */
	$cedUmbProfileSelectedMetaKeys = get_option('CedUmbProfileSelectedMetaKeys', false);
	if(!is_array($cedUmbProfileSelectedMetaKeys)) {
		$cedUmbProfileSelectedMetaKeys = array();
	}

	$getPostCustom = get_post_custom($productId);
	$_product = wc_get_product($productId);
	if( WC()->version < "3.0.0" ){

		if( $_product->product_type == "variation" ) {
			$parentId = $_product->parent->id;
			$getParentPostCustom = get_post_custom($parentId);
			$getPostCustom = array_merge($getPostCustom,$getParentPostCustom);
		}
	}else{
		/*if( $_product->get_type() == "variation" ) {
			$parentId = $_product->get_parent_id();
			$getParentPostCustom = get_post_custom($parentId);
			$getPostCustom = array_merge($getPostCustom,$getParentPostCustom);
		}*/
	}
	?>
	<table class="wp-list-table widefat fixed striped" id="ced_fruugo_metakeys_list">
		<thead>
			<tr>
				<th><?php _e('Meta Field Key','ced-fruugo');?></th>
				<th><?php _e('Meta Field Value','ced-fruugo');?></th>
			</tr>	
		</thead>
		<tbody>
			<?php
			foreach($getPostCustom as $customPostKey => $customPostValue) {
				$value = isset($customPostValue[0]) ? $customPostValue[0] : array();
				$searialize = false;
				$data = @unserialize($value);
				if ($data !== false) {
					$searialize=true;
				}
				if(is_array($value) || is_object($value) || $searialize){
					continue;
				}
				$checked = (in_array($customPostKey, $cedUmbProfileSelectedMetaKeys)) ? "checked=checked" : "" ;
				echo '<tr>';
					echo '<td>';
					echo '<input type="checkbox" class="ced_fruugo_add_del_meta_keys" name="unique_post[]" value="'.$customPostKey.'" id="'.$customPostKey.'" '.$checked.'><label for="'.$customPostKey.'">'.$customPostKey.'</label>';
					echo '</td>';
					echo '<td>';
					echo $value;
					echo '</td>';
				echo '</tr>';
			}
			?>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
	<?php
}

/**
 * getting product id from sku
 * @name _fruugo_umb_get_product_by_sku
 *
 */
function _fruugo_umb_get_product_by_sku( $sku ) {
	global $wpdb;
	$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
	if ( $product_id ) return wc_get_product( $product_id );
	return null;
}
/**
* This function renders metakeys selection dropdown on profile page.
* @name fruggorenderMetaSelectionDropdownOnProfilePage()
* @author CedCommerce <plugins@cedcommerce.com>
* @link  http://www.cedcommerce.com/
*/
function fruugorenderMetaSelectionDropdownOnProfilePage( $categroy_specific_variation = "" ) {
	/* select dropdown setup */

	$attributes		=	wc_get_attribute_taxonomies();

	$taxonomies = get_taxonomies(); 
	
	$attrOptions	=	array();
	$addedMetaKeys = get_option('CedUmbProfileSelectedMetaKeys', false);

	if($addedMetaKeys && count($addedMetaKeys) > 0) {
		foreach ($addedMetaKeys as $metaKey){
			$attrOptions[$metaKey]	=	$metaKey;
		}
	}
	if(!empty($attributes)){
		foreach($attributes as $attributesObject) {
			$attrOptions['umb_pattr_'.$attributesObject->attribute_name]	=	$attributesObject->attribute_label;
		}
	}
	if(!empty($taxonomies)){
		foreach($taxonomies as $taxonomies) { 
			$attrOptions['umb_customtaxonony_'.$taxonomies]	=	$taxonomies;
		}
	}


	/* select dropdown setup */
	ob_start();
	$fieldID = '{{*fieldID}}';
	$selectId = $fieldID.'_attibuteMeta';
	$selected = "";
	echo '<select id="'.$selectId.'" name="'.$selectId.'">';
	echo '<option value="null"> -- select -- </option>';
	if(is_array($attrOptions)) {
		foreach($attrOptions as $attrKey=>$attrName):
			$selected = "";
			if( strpos( strtolower($attrName), strtolower($categroy_specific_variation) ) ) $selected = "selected";
			echo '<option value="'.$attrKey.'" '.$selected.'>'.$attrName.'</option>';
		endforeach;
	}
	echo '</select>';
	$selectDropdownHTML = ob_get_clean();
	return $selectDropdownHTML;
}

/**
* This function renders different marketplaces link on top in UMB section.
* @name fruggorenderMarketPlacesLinksOnTop()
* @author CedCommerce <plugins@cedcommerce.com>
* @link  http://www.cedcommerce.com/
*/
function fruugorenderMarketPlacesLinksOnTop($page='') {
	$availableMarketPlaces = fruugoget_enabled_marketplaces();
	if(!is_array($availableMarketPlaces) || empty($availableMarketPlaces)) {
		return;	
	}
	foreach($availableMarketPlaces as $val)
	{
		$section = $val;
		break;
	}	
	if(isset($_GET['section'])) {
		$section = esc_attr($_GET['section']);
	}
	echo '<ul class="subsubsub">';
	$marketPlaces = fruugoget_enabled_marketplaces();
	if(is_array($marketPlaces)) { 
		$counter=1;
		foreach ($marketPlaces as $marketPlace) {
			$class = '';
			if( $section == $marketPlace ) {
				$class = 'current';
			}
			$redirectURL = get_admin_url()."admin.php?page=".$page."&amp;section=".$marketPlace;
			echo '<li>';
			echo '<a href="'.$redirectURL.'" class="'.$class.'">'.strtoupper($marketPlace).'</a>'; 
			if($counter < count($marketPlaces) ){ 
				echo '|'; 
			}
			echo '</li>';
			$counter++;
		}
	}
	echo '</ul>';
}

/**
* This function returns whether marketplace is enable by the admin or not.
* @name fruggogetMarketPlaceStatus()
* @author CedCommerce <plugins@cedcommerce.com>
* @link  http://www.cedcommerce.com/
*/
function fruugogetMarketPlaceStatus( $marketplaceID ) {
	$activeMarketPlaces = fruggoget_enabled_marketplaces();
	if(in_array($marketplaceID, $activeMarketPlaces)) {
		return true;
	}
	else {
		return false;
	}
}

/**
* This function returns whether marketplace configuration is validated or not.
* @name fruggoisMarketPlaceConfigurationsValidated()
* @author CedCommerce <plugins@cedcommerce.com>
* @link  http://www.cedcommerce.com/
*/
function fruugoisMarketPlaceConfigurationsValidated( $marketplaceID ) {
	$ced_fruugo_validate_marketplace = get_option("ced_fruugo_validate_".$marketplaceID,true);
	if( empty($ced_fruugo_validate_marketplace) || $ced_fruugo_validate_marketplace == "no" ) {
		$ced_fruugo_validate_marketplace = false;
	}
	else {
		$ced_fruugo_validate_marketplace = true;
	}
	return $ced_fruugo_validate_marketplace;
}

/**
* This function returns all the marketplaces enabled by the admin.
* @name fruggoget_enabled_marketplaces()
* @author CedCommerce <plugins@cedcommerce.com>
* @link  http://www.cedcommerce.com/
*/
function fruugoget_enabled_marketplaces(){
	$activated_marketplaces = array('fruugo');
	return $activated_marketplaces;
}

/**
* This function returns all the marketplaces enabled by the admin.
* @name fruggoget_enabled_marketplaces()
* @author CedCommerce <plugins@cedcommerce.com>
* @link  http://www.cedcommerce.com/
*/
function ced_fruugo_available_marketplace($api_key=''){
	$dir = CED_FRUUGO_DIRPATH.'marketplaces';
	$folders = scandir($dir, 1);
	$availableMarketPlaces = array();
	foreach ($folders as $folder) {
	    if ($folder === '.' || $folder === '..' || $folder === '.DS_Store') {
	    	continue;
	    }
	    $availableMarketPlaces[] = $folder;
	}
	return $availableMarketPlaces;
}


/**
 * Check WooCommmerce active or not.
 *
 * @since 1.0.0
 * @return bool true|false
 */
function ced_fruugo_check_woocommerce_active(){

	if ( function_exists('is_multisite') && is_multisite() ){

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ){

			return true;
		}
		return false;
	}else{
			
		if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ){

			return true;
		}
		return false;
	}
}


/**
 * formatting the json_data by removing some characters
 * found that prevents the json_decode() to work and 
 * showing syntax error problem.
 * 
 * @refrenced from stackoverflow a post by kris khairallah.
 * 
 * @since 1.0.0
 * @param  json  raw json data
 * @return json  formated json data
 */
function ced_fruugo_format_json($json_data){

	for ($i = 0; $i <= 31; ++$i) {
		$json_data = str_replace(chr($i), "", $json_data);
	}
	$json_data = str_replace(chr(127), "", $json_data);

	// This is the most common part
	// Some file begins with 'efbbbf' to mark the beginning of the file. (binary level)
	// here we detect it and we remove it, basically it's the first 3 characters
	if (0 === strpos(bin2hex($json_data), 'efbbbf')) {
		$json_data = substr($json_data, 3);
	}

	return $json_data;
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ced_umb_fruugo() {

	$ced_fruugo = new CED_UMB_fruugo();
	$ced_fruugo->run();
}

/**
 * This code runs when WooCommerce is not activated,
 * deativates the extension and displays the notice to admin.
 *
 * @since 1.0.0
 */
function deactivate_ced_fruugo_woo_missing() {

	deactivate_plugins( plugin_basename( __FILE__ ) );
	add_action('admin_notices', 'ced_fruugo_woo_missing_notice' );
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

/**
 * callback function for sending notice if woocommerce is not activated.
 *
 * @since 1.0.0
 * @return string
 */
function ced_fruugo_woo_missing_notice(){

	echo '<div class="error"><p>' . sprintf(__('Woocommerce fruugo Integration requires WooCommerce to be installed and active. You can download %s here.', 'ced-fruugo'), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>') . '</p></div>';
}

/**
 * checking the profile condition.
 *
 * @since 1.0.0
 */
 function fruugo_profile_validation($proID,$condition=array()){
	 if(is_array($condition)){
			 $default = isset($condition['default']) ? $condition['default'] : null;
			 $bymeta = isset($condition['metakey']) ? $condition['metakey'] : null;
			 if(!is_null($default) && strlen($default) && $default != 'null'){
				 return $default;
			 }
			 
			 if(!is_null($bymeta) && strlen($bymeta) && $bymeta != 'null'){
				 $explodeForAttribute = explode('umb_pattr_', $bymeta);
					if(count($explodeForAttribute) > 1 && isset($explodeForAttribute[1])){
						$attrName = $explodeForAttribute[1];
						$product_terms = get_the_terms($proID, 'pa_'.$attrName);
							if(count($product_terms)){
								$first_term = isset($product_terms[0]) ? $product_terms[0] : array();
								$termName = isset($first_term->name) ? esc_attr($first_term->name) : '';
								if( !is_null($termName) && strlen($termName) ){
									return $termName;
								}
							}
					}else{
						return get_post_meta($proID,$bymeta,true);
				}
			 }
		 }
		 return 'null';
 }
 
	/**
	 * get profile conditions.
	 * 
	 * @since 1.0.0 
	 */
	function fruugo_get_profile_condition($pro_id){
		
		$isProfileAssigned = get_post_meta($pro_id,'ced_fruugo_profile',true);
		$conditions=array();
		if(isset($isProfileAssigned) && !empty($isProfileAssigned) && $isProfileAssigned){
			$profile_info = fruugogetProfileDetail($isProfileAssigned);
			$conditions = isset($profile_info['profile_data']) ? json_decode($profile_info['profile_data'],true) : array();
		}
		return $conditions;
	}
	
	/**
	 * get profile details.
	 *
	 * @since 1.0.0
	 */
	 function fruugogetProfileDetail($profileId){
		global $wpdb;
		$table_name = $wpdb->prefix.CED_FRUUGO_PREFIX.'_fruugoprofiles';
		$query = "SELECT * FROM `$table_name` WHERE id=$profileId";
		$profileDetail = $wpdb->get_row($query,'ARRAY_A');
		if(is_array($profileDetail)){
			return $profileDetail;
		}else{
			return false;
		}
	 }

/**
 * get marketplace price.
 * 
 * @since 1.0.0
 */
function fruugo_get_marketplace_price($proId,$marketplace=''){
	
	$conditions = fruugo_get_profile_condition($proId);
	if(!is_array($conditions))
		$conditions = array();
	
	if(!is_null($marketplace) && strlen($marketplace)){
		$key = '_umb_'.$marketplace.'_Price';
		$condition = isset($conditions[$key]) ? $conditions[$key] : false;
		
		if($condition && !is_null($condition) && count($condition) ) {
			$price = fruugo_profile_validation($proId,$condition);
			if($price && !is_null($price) && $price != 'null' && strlen($price)){
				return round($price,2);
			} 
			//return 0;
		}
		
		$umbPrice = get_post_meta($proId,'_umb_'.$marketplace.'_Price',true);
		if($umbPrice){
			$umbPrice = round($umbPrice,2);
			return $umbPrice;
		}
	}
	
	$MarketplacePrice = get_post_meta($proId,'_umb_fruugo_price',true);
	if($MarketplacePrice){
		return $MarketplacePrice;
	}else{
		$salePrice = get_post_meta($proId,'_sale_price',true);
		if($salePrice){
			return $salePrice;
		}else{
			$mainPrice = get_post_meta($proId,'_regular_price',true);
			if($mainPrice){
				return $mainPrice;
			}else{
				return 0;
			}
		}
	}
}

/**
 * get marketplace qty.
 *
 * @since 1.0.0
 */
function fruugo_get_marketplace_qty($proId,$marketplace='')
{
	$conditions = fruugo_get_profile_condition($proId);
	if(!is_array($conditions))
		$conditions = array();

	if($marketplace != null || $marketplace != ""){
		$key = '_umb_'.$marketplace.'_Inventory';
		$condition = isset($conditions[$key]) ? $conditions[$key] : false;
		if($condition && !is_null($condition) && count($condition) ) {
			$qty = fruugo_profile_validation($proId,$condition);
			if($qty && !is_null($qty) && $qty != 'null' && strlen($qty)){
				return intval($qty);
			} 
		}
		
		$QTY = get_post_meta($proId,'_umb_'.$marketplace.'_Inventory',true);
		if($QTY){
			return intval($QTY);
		}
	}
	
	$marketplaceStock = get_post_meta($proId,'_umb_fruugo_stock',true);
	if($marketplaceStock){
		return intval($marketplaceStock);
	}else{
		$centralStock = get_post_meta($proId,'_stock',true);
		if($centralStock){
			return intval($centralStock);
		}else{
			return 0;
		}
	}
}

/**
 * update marketplace qty.
 *
 * @since 1.0.0
 */
function fruugo_update_marketplace_qty($proId,$marketplace='', $qty,$increase=false) {
	
	$conditions = fruugo_get_profile_condition($proId);
	if(!is_array($conditions))
		$conditions = array();
	
	if($marketplace != null && $marketplace != "") {
			
		$existingQty = get_post_meta($proId,'_umb_'.$marketplace.'_Inventory',true);
		if($existingQty){
			if($increase){
				$updatedQty = intval($existingQty) + intval($qty);
			}else{
				$updatedQty = intval($existingQty) - intval($qty);
			}
			if($updatedQty<0)
				$updatedQty = 0;
			
			update_post_meta($proId,'_umb_'.$marketplace.'_Inventory',$updatedQty);
			return;
		}
	}
	
	$marketplaceStock = get_post_meta($proId,'_umb_fruugo_stock',true);
	if($marketplaceStock){
		if($increase){
			$updatedQty = intval($marketplaceStock) + intval($qty);
		}else{
			$updatedQty = intval($marketplaceStock) - intval($qty);
		}
		if($updatedQty<0)
			$updatedQty = 0;
			
		update_post_meta($proId,'_umb_fruugo_stock',$updatedQty);
		return;
	}else{
		$centralStock = get_post_meta($proId,'_stock',true);
		if($centralStock){
			
			if($increase){
				$updatedQty = intval($centralStock) + intval($qty);
			}else{
				$updatedQty = intval($centralStock) - intval($qty);
			}
			if($updatedQty<0)
				$updatedQty = 0;
			

				
			update_post_meta($proId,'_stock',$updatedQty);
			
			return;
		}else{
			return;
		}
	}
}

/*
* Function to set configuration Error message
*/
function fruugosetConfigurationErrorMessage($meesage,$type='error'){
	
	$errorMessages = get_option('ced_fruugo_errorNotifications',true);
	if(!is_array($errorMessages))
		$errorMessages = array();
	
	if(is_array($meesage)){
		$messageKey = isset($meesage['key']) ? $message['key'] : '';
		$error = isset($meesage['message']) ? $meesage['message'] : '';
		if(empty($error) && empty($messageKey)){
			$errorMessages[$messageKey] = $error;
		}
		update_option('ced_fruugo_errorNotifications',$errorMessages);
	}
}

/*
* Function to unset configuration Error message
*/
function fruugounsetConfigurationErrorMeassage($key){
	$errorMessages = get_option('ced_fruugo_errorNotifications',true);
	if (array_key_exists($key, $errorMessages)) {
		unset($errorMessages[$key]);
	}
	update_option('ced_fruugo_errorNotifications',$errorMessages);
}

/**
 * get marketplace order id.
 * 
 * @since 1.0.0
 */
function fruugo_umb_makertplace_orders($metakey, $value) {

	global $wpdb;
	$order_ids = $wpdb->get_results("SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE meta_key='".$metakey."' AND meta_value='".$value."'", ARRAY_A);
	return $order_ids;
}
/**
 * get file header data.
 *
 * @since 1.0.0
 */
function ced_fruugo_get_package_header_data( $file, $default_headers, $context = '' ) {
	// We don't need to write to the file, so just open for reading.
	$fp = fopen( $file, 'r' );

	// Pull only the first 8kiB of the file in.
	$file_data = fread( $fp, 8192 );

	// PHP will close file handle, but we are good citizens.
	fclose( $fp );

	// Make sure we catch CR-only line endings.
	$file_data = str_replace( "\r", "\n", $file_data );
	/**
	 * Filter extra file headers by context.
	 *
	 * The dynamic portion of the hook name, `$context`, refers to
	 * the context where extra headers might be loaded.
	 *
	 * @since 2.9.0
	 *
	 * @param array $extra_context_headers Empty array by default.
	*/
	if ( $context && $extra_headers = apply_filters( "extra_{$context}_headers", array() ) ) {
		$extra_headers = array_combine( $extra_headers, $extra_headers ); // keys equal values
		$all_headers = array_merge( $extra_headers, (array) $default_headers );
	} else {
		$all_headers = $default_headers;
	}

	foreach ( $all_headers as $field => $regex ) {

		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] )
			$all_headers[ $field ] = _cleanup_header_comment( $match[1] );
		else
			$all_headers[ $field ] = '';
	}
	return $all_headers;
}
?>