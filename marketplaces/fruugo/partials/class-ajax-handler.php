<?php
if(!class_exists('Ced_fruugo_ajax_handler')){
	class Ced_fruugo_ajax_handler{
		
		/**
		 * construct
		 * @version 1.0.0
		 */
		public function __construct()
		{			
			add_action( 'wp_ajax_umb_fruugo_acknowledge_order', array ( $this, 'umb_fruugo_acknowledge_order' ));
			add_action( 'wp_ajax_ced_fruugo_authorize', array ( $this, 'ced_fruugo_authorize' ));
			add_action( 'wp_ajax_ced_fruugo_fetchCat', array ( $this, 'ced_fruugo_fetchCat' ));
			add_action( 'wp_ajax_ced_fruugo_process_fruugo_cat', array ( $this, 'ced_fruugo_process_fruugo_cat' ));
			add_action('ced_fruugo_required_fields_process_meta_simple', array($this,'ced_fruugo_required_fields_process_meta_simple'), 11, 1 );
			add_action('ced_fruugo_required_fields_process_meta_variable', array($this,'ced_fruugo_required_fields_process_meta_variable'), 11, 1 );
			add_action(	'wp_ajax_umb_fruugo_cancel_order', array($this,'umb_fruugo_cancel_order'));
			add_filter( 'umb_save_additional_profile_info', array( $this, 'umb_save_additional_profile_info' ), 11, 1 );
			/**for Shistation automation**/
			add_action('woocommerce_shipstation_shipnotify','ced_fruugo_check_shipstation_data',999,2);
			
		}
		
	//add_action( 'init', 'custom_taxonomy_Item' );
			


		public function ced_fruugo_extra_action($actions){
			$actions['update'] = "Update";
			$actions['delete'] = "Remove from fruugo";
			$actions['deactivate'] = "Deactivate";
			return $actions;
		}
		
		/**
		 * Save Profile Information
		 *
		 * @name umb_save_additional_profile_info
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 */
		
		public function umb_save_additional_profile_info( $profile_data ) {
			if(isset($_POST['ced_fruugo_attributes_ids_array'])) {
				foreach ($_POST['ced_fruugo_attributes_ids_array'] as $key ) {
					if(isset($_POST[$key])) {
						$fieldid = isset($key) ? $key : '';
						$fieldvalue = isset($_POST[$key]) ? $_POST[$key][0] : null;
						$fieldattributemeta = isset($_POST[$key.'_attibuteMeta']) ? $_POST[$key.'_attibuteMeta'] : null;
						$profile_data[$fieldid] = array('default'=>$fieldvalue,'metakey'=>$fieldattributemeta);
					}
				}
			}
			return $profile_data;
		}
		
		/**
		 * Function to get category specifics on profile edit page
		 * @name fetch_fruugo_attribute_for_selected_category_for_profile_section
		 */
		public function fetch_fruugo_attribute_for_selected_category_for_profile_section() 
		{
			
			if(isset($_POST['profileID'])) {
				$profileid = $_POST['profileID'];
			}
			global $wpdb;
			$table_name = $wpdb->prefix.CED_FRUUGO_PREFIX.'_fruugoprofiles';
			$profile_data = array();
			if($profileid){
				$query = "SELECT * FROM `$table_name` WHERE `id`=$profileid";
				$profile_data = $wpdb->get_results($query,'ARRAY_A');
							//echo '<pre>';	 print_r($profile_data);die('fsdf');

				if(is_array($profile_data)) {
					$profile_data = isset($profile_data[0]) ? $profile_data[0] : $profile_data;
					$profile_data = isset($profile_data['profile_data']) ? json_decode($profile_data['profile_data'],true) : array();
				}
			}
			
			/* select dropdown setup */
			$attributes		=	wc_get_attribute_taxonomies();
			$attrOptions	=	array();
			$addedMetaKeys = get_option('CedUmbProfileSelectedMetaKeys', false);
			
			if($addedMetaKeys && count($addedMetaKeys) > 0){
				foreach ($addedMetaKeys as $metaKey){
					$attrOptions[$metaKey]	=	$metaKey;
				}
			}
			if(!empty($attributes)){
				foreach($attributes as $attributesObject){
					$attrOptions['umb_pattr_'.$attributesObject->attribute_name]	=	$attributesObject->attribute_label;
				}
			}
			/* select dropdown setup */
			
			$categoryID = isset($_POST['categoryID']) ? $_POST['categoryID'] : "";
			$productID = isset($_POST['productID']) ? $_POST['productID'] : "";
			global $client_obj;
			
			// $api = new fruugo\fruggoApi($client_obj);
			$variation_category_attribute=$api->findPropertySet(array('category_id' =>$categoryID) );
			
			$variation_category_attribute_property = $variation_category_attribute['results']['0']['properties'];
			
			$attributes		=	wc_get_attribute_taxonomies();
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

			$fruugoDetails = get_option('ced_fruugo_details');
			$token = $fruugoDetails['access_token'];
			$siteID = $fruugoDetails['siteID'];
			if(empty( $token ))
			{
				//echo json_encode(array('status'=>'401','reason'=>'Token unavailable'));die;
			}
			
            /* render framework specific fields */
            $pFieldInstance = CED_FRUUGO_product_fields::get_instance();
            $framework_specific =$pFieldInstance->get_custom_fields('framework_specific',false);

            ?>
            <div class="ced_fruugo_cmn active">
              <table class="wp-list-table widefat fixed" style="border: none !important;">
                 <tbody>
                 </tbody>
                 <tbody>
                    <?php
                    global $global_CED_FRUUGO_Render_Attributes;
                    $marketPlace = "ced_fruugo_required_common";
                    $productID = 0;
                    $categoryID = '';
                    $indexToUse = 0;
							//	echo "<pre>";			print_r($variation_category_attribute);die('fsdfsd');
                    $attributesList = array();
                    if(isset($variation_category_attribute_property)){
                       foreach ($variation_category_attribute_property as $variation_category_attribute_property_key => $variation_category_attribute_property_value) {

                          $attributesList[]=array(
                            'type' => '_text_input',
                            'id' => '_ced_fruugo_property_id_'.$variation_category_attribute_property_value['property_id'],
                            'fields' => array(
                              'id'                => '_ced_fruugo_property_id_'.$variation_category_attribute_property_value['property_id'],
                              'label'             =>  $variation_category_attribute_property_value['name'].'<span class="ced_fruugo_varition" style="color:green"> [ '.__( 'For Variation', 'ced-fruugo' ).' ]</span>',
                              'desc_tip'          => true,
                              'description'       => $variation_category_attribute_property_value['description'],
                              'type'              => 'text',
                              'class'				=> 'wc_input_price'
                              ));
                      }
                  }

                  foreach ($attributesList as $value) {
                   $isText = true;
                   $field_id = trim($value['fields']['id'],'_');
                   $default = isset($profile_data[$value['fields']['id']]) ? $profile_data[$value['fields']['id']] : '';
                   $default = $default['default'];
                   echo '<tr>';
                   echo '<td>';
                   if( $value['type'] == "_select" ) {
                      $valueForDropdown = $value['fields']['options'];
                      $tempValueForDropdown = array();
                      foreach ($valueForDropdown as $key => $_value) {
                         $tempValueForDropdown[$_value] = $_value;
                     }
                     $valueForDropdown = $tempValueForDropdown;
                     
                     $global_CED_FRUUGO_Render_Attributes->renderDropdownHTML($field_id,ucfirst($value['fields']['label']),$valueForDropdown,$categoryID,$productID,$marketPlace,$value['fields']['description'],$indexToUse,array('case'=>'profile','value'=>$default));
                     $isText = false;
                 }
                 else {
                  $global_CED_FRUUGO_Render_Attributes->renderInputTextHTML($field_id,ucfirst($value['fields']['label']),$categoryID,$productID,$marketPlace,$value['fields']['description'],$indexToUse,array('case'=>'profile','value'=>$default));
              }
              echo '</td>';
              echo '<td>';
              if($isText) {
                  $previousSelectedValue = 'null';
                  if( isset($profile_data[$value['fields']['id']]) && $profile_data[$value['fields']['id']] != 'null') {
                     $previousSelectedValue = $profile_data[$value['fields']['id']]['metakey'];
                 }

                 $selectDropdownHTML= fruggorenderMetaSelectionDropdownOnProfilePage( $value['fields']['description'] );
                 $updatedDropdownHTML = str_replace('{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML);
                 $updatedDropdownHTML = str_replace('value="'.$previousSelectedValue.'"', 'value="'.$previousSelectedValue.'" selected="selected"', $updatedDropdownHTML);
                 echo $updatedDropdownHTML;
             }
             echo '</td>';
             echo '</tr>';
         }	
         ?>
     </tbody>
     <tfoot>
     </tfoot>
 </table>
</div>
<?php



wp_die();
}

		/**
		 * @name ced_fruugo_autharization
		 * function to handle authorization reuqest
		 * 
		 * @version 1.0.0
		 */
		public function ced_fruugo_authorize()
		{
			$nonce = isset($_POST['_nonce']) ? $_POST['_nonce'] : "";
			if($nonce == 'do_fruugo_authorize')
			{
				$shop_already_saved = isset( $_POST['shop_already_saved'] ) ? $_POST['shop_already_saved'] : '';
				$keyString = isset( $_POST['keyString'] ) ? $_POST['keyString'] : '';
				$sharedString = isset( $_POST['sharedString'] ) ? $_POST['sharedString'] : '';

				$auth_obj = new fruugo\fruggoClient( $keyString, $sharedString );

				$response = $auth_obj->getRequestToken( array( 'callback'=>admin_url().'?page=umb-fruugo-main' ) );
				$saved_fruugo_details = get_option('ced_fruugo_details', array());
				$saved_shop_fruugo_details = $saved_fruugo_details[$shop_already_saved];
				if( is_array( $response ) && !empty( $response ) )
				{
					$_SESSION['fruggo_shop_name'] = $shop_already_saved;
					$login_url = isset( $response['login_url'] ) ? $response['login_url'] : ''; 
					$saved_shop_fruugo_details['oauth_secret'] = $response['oauth_token_secret'];

					$saved_fruugo_details[$shop_already_saved] = $saved_shop_fruugo_details;

					update_option( 'ced_fruugo_details', $saved_fruugo_details );
					echo json_encode( array( 'status'=>'200', 'response'=>$res, 'login_url'=>$login_url ) );
					die;
				}
				else
				{
					echo json_encode(array( 'status'=>'201', 'response' => 'Unable to Auhtorise' ));
					die;
				}
			}
			
		}
		
		/**
		 * @name ced_fruugo_fetchCat
		 * function to request for category fetching
		 *
		 * @version 1.0.0
		 */
		public function ced_fruugo_fetchCat()
		{
			$nonce = isset($_POST['_nonce']) ? $_POST['_nonce'] : "";
			$nextLevelCategories = array();
            if( $nonce == 'ced_fruugo_fetch_next_level' )
            {
                $catDetails = isset( $_POST['catDetails'] ) ? $_POST['catDetails'] : array();
                $catLevel = isset( $catDetails['catLevel'] ) ? $catDetails['catLevel'] : '';
                $catID = isset( $catDetails['catID'] ) ? $catDetails['catID'] : '' ;
                $catName = isset( $catDetails['catName'] ) ? $catDetails['catName'] : '' ;
                $parentCatName = isset( $catDetails['parentCatName'] ) ? $catDetails['parentCatName'] : '' ;
                if( $catID != '' )
                {
                   $folderName = CED_FRUUGO_DIRPATH.'marketplaces/fruugo/lib/json/';
                   $catFirstLevelFile = $folderName.'category.json';
					// print_r($catFirstLevelFile);die;
                   if(file_exists($catFirstLevelFile)){
                      $catFirstLevel = file_get_contents($catFirstLevelFile);
                      $catFirstLevel = json_decode($catFirstLevel,true);
                  }
					// print_r($catFirstLevel);die;
                  $catLevel_next = $catLevel + 1;
                  $lev_cat = 'level'.$catLevel_next;
                  $lev_par_cat  = 'level'.$catLevel;
                  foreach ($catFirstLevel as $key => $value) 
                  {
						// print_r($value[$lev_cat]);die;
                      if($value[$lev_par_cat] == $catName)
                      {
                         $nextLevelCategories[] = $value[$lev_cat];
                         $nextLevelCategories = array_unique($nextLevelCategories);
                         $cat_end = $catLevel_next+1;
							// print_r($value[$lev_cat]);
							// echo " --> ";
							// print_r($value['level'.$cat_end]);
							// echo "<br>";
                         $selectedCategories[$value[$lev_cat]] = $value['level'.$cat_end];
							// $selectedCategories = array_unique($selectedCategories);
                     }
                 }
					// print_r($selectedCategories);die;
                 $savedCategories = get_option('ced_fruugo_selected_categories');
                 if( is_array( $nextLevelCategories ) && !empty( $nextLevelCategories ) )
                 {
                  echo json_encode( array( 'status' => '200', 'nextLevelCat' => $nextLevelCategories, 'selectedCat' => $selectedCategories ,'savedCategories' =>$savedCategories ) );
                  wp_die();
              } 
          }
      }
      wp_die();
  }
		/**
		 * function to process selected categories
		 * @name ced_fruugo_process_fruugo_cat
		 * 
		 */
		public function ced_fruugo_process_fruugo_cat(){
			$nonce = isset($_POST['_nonce']) ? $_POST['_nonce'] : false;
			if($nonce == 'ced_fruugo_save'){
				$cat = isset($_POST['cat']) ? $_POST['cat'] : false;
				$catID = isset($cat['catID']) ? $cat['catID'] : false;
				$catName = isset($cat['catName']) ? $cat['catName'] : false;
				$catID = trim($catName);
				$catID = preg_replace('/\s+/', '', $catID);
				$catName = preg_replace('/\s+/', '', $catName);
				if($catID && $catName){
					$savedCategories = get_option('ced_fruugo_selected_categories');
					$savedCategories = isset($savedCategories) ? $savedCategories :array();
					$savedCategories[$catID]=$catName;
					if(update_option('ced_fruugo_selected_categories', array_unique($savedCategories))){
						echo json_encode(array('status'=>'200'));die;
					}
					echo json_encode(array('status'=>'400'));die;
				}
				echo json_encode(array('status'=>'401'));die;
			}
			if($nonce == 'ced_fruugo_remove'){
				$cat = isset($_POST['cat']) ? $_POST['cat'] : false;
				$catID = isset($cat['catName']) ? trim($cat['catName']) : false;
				$catID = preg_replace('/\s+/', '', $catID);
				if($catID){
					$savedCategories = get_option('ced_fruugo_selected_categories');
					$savedCategories = isset($savedCategories) ? $savedCategories :array();
					// print_r( $savedCategories );
					if(is_array($savedCategories) && !empty($savedCategories)){
						foreach ($savedCategories as $key=>$value){
							if(trim($key) == $catID){
								unset($savedCategories[$key]);
							}
						}
					}
					if(update_option('ced_fruugo_selected_categories', array_unique($savedCategories))){
						echo json_encode(array('status'=>'500'));die;
					}
					echo json_encode(array('status'=>'400'));die;
				}
				echo json_encode(array('status'=>'401'));die;
			}
		}
		
		/**
		 * function to render specifics
		 * 
		 * @name renderAttributes
		 * 
		 */
		public function renderAttributes($catID,$productID,$catSpecifics, $getCatFeatures){
			$productID = $productID;
			$categoryID = $catID;
			$recomendations = $catSpecifics['Recommendations']['NameRecommendation'];
			$tempRecommendation = array();
			if(!isset($recomendations[0])){
				$tempRecommendation[0] = $recomendations;
				$recomendations = $tempRecommendation;
			}
			$catFeatureSavingForvalidation = array();
			$catFeatureSavingForvalidation = get_option( 'ced_fruugo_req_feat', array() );
			global $global_CED_FRUUGO_Render_Attributes;
			$marketPlace = 'ced_fruugo_attributes_ids_array';
			$_product = wc_get_product($productID);
			$indexToUse = '0';
			if(isset($_POST['indexToUse'])) {
				$indexToUse = $_POST['indexToUse'];
			}
			echo '<div class="ced_fruugo_attribute_section">';
			echo '<div class="ced_fruugo_toggle_section">';
			echo '<div class="ced_fruugo_toggle">';
			echo '<span>fruugo Attributes</span>';
			echo '<span class="ced_ump_circle_loderimg"><img class="ced_fruugo_circle_img" src='.CED_FRUUGO_URL.'admin/images/circle.png></span>';
			echo '</div>';
			echo '<div class="ced_fruugo_toggle_div ced_attr_wrapper">';
			//print_r($recomendations);die;
			foreach ($recomendations as $key => $recomendation) {
				if($recomendation['ValidationRules']['SelectionMode'] == 'SelectionOnly') {
					$valueForDropdown = $recomendation['ValueRecommendation'];
					$tempValueForDropdown = array();
					foreach ($valueForDropdown as $key => $value) {
						$tempValueForDropdown[$value['Value']] = $value['Value'];
					}
					$valueForDropdown = $tempValueForDropdown;
					$name = ucfirst($recomendation['Name']);
					if(isset($recomendation['ValidationRules']['MinValues'])){
						$catFeatureSavingForvalidation[$categoryID][] =  $recomendation['Name'];
						$name .= '<span class="ced_fruugo_wal_required"> [ Required ]</span>';
					}
					if(!isset($recomendation['ValidationRules']['VariationSpecifics'])){
						$name .= '<span class="ced_fruugo_wal_conditionally_required"> [ Can Be Used For Variation ]</span>';
					}
					$global_CED_FRUUGO_Render_Attributes->renderDropdownHTML(urlencode($recomendation['Name']),$name,$valueForDropdown,$categoryID,$productID,$marketPlace,"",$indexToUse);
				}
				else {
					$name = $recomendation['Name'];
					if(isset($recomendation['ValidationRules']['MinValues'])){
						$catFeatureSavingForvalidation[$categoryID][] =  $recomendation['Name'];
						$name .= '<span class="ced_fruugo_wal_required"> [ Required ]</span>';
					}
					if(!isset($recomendation['ValidationRules']['VariationSpecifics'])){
						$name .= '<span class="ced_fruugo_wal_conditionally_required"> [  Can Be Used For Variation ]</span>';
					}
					$global_CED_FRUUGO_Render_Attributes->renderInputTextHTML(urlencode($recomendation['Name']),$name,$categoryID,$productID,$marketPlace,"",$indexToUse);
				}
			}
			if($getCatFeatures){
				if(isset($getCatFeatures['ConditionValues'])) {
					$valueForDropdown = $getCatFeatures['ConditionValues']['Condition'];
					$tempValueForDropdown = array();
					foreach ($valueForDropdown as $key => $value) {
						$tempValueForDropdown[$value['ID']] = $value['DisplayName'];
					}
					$valueForDropdown = $tempValueForDropdown;
					$name = "Condition";
					if($getCatFeatures['ConditionEnabled'] == 'Required'){
						$catFeatureSavingForvalidation[$categoryID][] = "Condition";
						$name .= '<span class="ced_fruugo_wal_required"> [ Required ]</span>';
					}
					$global_CED_FRUUGO_Render_Attributes->renderDropdownHTML("Condition",$name,$valueForDropdown,$categoryID,$productID,$marketPlace,"",$indexToUse);
				}
			}
			update_option('ced_fruugo_req_feat', $catFeatureSavingForvalidation);
			wp_die();
		}
		/**
		 * Process Meta data for Simple product
		 *
		 * @name ced_fruugo_required_fields_process_meta_simple
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 */
		
		function ced_fruugo_required_fields_process_meta_simple( $post_id ) {
			$marketPlace = 'ced_fruugo_attributes_ids_array';
			if(isset($_POST[$marketPlace])) {
				foreach ($_POST[$marketPlace] as $key => $field_name) {
					update_post_meta( $post_id, $field_name, sanitize_text_field( $_POST[$field_name][0] ) );
				}
			}
		}
		/**
		 * Process Meta data for variable product
		 *
		 * @name ced_fruugo_required_fields_process_meta_variable
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 */
		
		function ced_fruugo_required_fields_process_meta_variable( $postID ) {
			$marketPlace = 'ced_fruugo_attributes_ids_array';
			if(isset($_POST[$marketPlace])) {
				$attributesArray = array_unique($_POST[$marketPlace]);
				foreach ($attributesArray as $field_name) {
					foreach ($_POST['variable_post_id'] as $key => $post_id) {
						$field_name_md5  = md5( $field_name );
						if(isset($_POST[$field_name_md5][$key])) {
							update_post_meta( $post_id, $field_name, sanitize_text_field( $_POST[$field_name_md5][$key] ) );
						}
					}
				}
			}
		}
		/**
		 * @name umb_fruugo_acknowledge_order
		 */
		public function umb_fruugo_acknowledge_order(){
			$oID = isset($_POST['order_id']) ? $_POST['order_id'] : "";
			if($oID){
				$order_id = $oID;
				
				if($acknowledge){
					update_post_meta($order_id,'_fruugo_umb_order_status','Acknowledged');
					echo json_encode(array('status'=>'200'));die;
				}else{
					echo json_encode(array('status'=>'402'));die;
				}
			}
		}
		public function umb_fruugo_cancel_order(){
			$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : false;
			if($order_id){
				$file = CED_FRUUGO_DIRPATH.'marketplaces/fruugo/lib/fruugoCancelOrders.php';
				$renderDependency = $this->renderDependency($file);
				if($renderDependency){
					$merchant_order_id = get_post_meta($order_id,  'merchant_order_id', true);
					$fulfillment_node = get_post_meta($order_id,  'fulfillment_node', true);
					$order_detail = get_post_meta($order_id,  'order_detail', true);
					$purchaseOrderId = $order_detail['OrderID'];
					$purchaseOrderId = get_post_meta($order_id,  'purchaseOrderId', true);
					$order_items = get_post_meta($order_id,  'order_items', true);
					$fruggoDetails = get_option('ced_fruugo_details');
					$token = $fruggoDetails['token']['fruugoAuthToken'];
					$siteID = $fruggoDetails['siteID'];
					$fruugoOrderInstance = CancelfruugoOrders :: get_instance($siteID, $token);
					$cancelRequest = $fruugoOrderInstance->cancelOrder($purchaseOrderId,$oID,$order_items);
					if($cancelRequest){
						update_post_meta($order_id,'_fruugo_umb_order_status','Cancelled');
						echo json_encode(array('status'=>'200'));die; 
					}else{
						echo json_encode(array('status'=>'402'));die;
					}
				}
			}
		}
		
		
		/**
		 * function to include dependencies
		 * 
		 * @name renderDependency
		 * @return boolean
		 */
		public function renderDependency($file){
			if($file != null || $file != ""){
				require_once "$file";
				return true;
			}
			return false;
		}
		
		/**
		 * @name ced_fruugo_check_shipstation_data
		 * Function to check shipstaion data
		 * 
		 */
		public function ced_fruugo_check_shipstation_data($orders_details,$ship_details){
			if(file_exists(CED_FRUUGO_DIRPATH.'marketplaces/fruugo/partials/class-fruugo-orders.php')){
				require_once CED_FRUUGO_DIRPATH.'marketplaces/fruugo/partials/class-fruugo-orders.php';
				$fruugoOrders = CedfruugoOrders :: get_instance();
				$shipstationShipment = $fruugoOrders->shipShipstationFullfilledOrders($orders_details,$ship_details);
			}
          
		}
		
	}
}