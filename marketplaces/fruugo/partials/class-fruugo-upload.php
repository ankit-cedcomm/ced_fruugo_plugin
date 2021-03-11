<?php
 
if(!class_exists('CedfruugoUpload')){
    class CedfruugoUpload{

        private static $_instance;
        private $uploadResponse;

        /**
         * get_instance Instance.
         *
         * Ensures only one instance of CedfruugoUpload is loaded or can be loaded.
         *
         * @author CedCommerce <plugins@cedcommerce.com>
         * @since 1.0.0
         * @static
         * @return get_instance instance.
         */
        public static function get_instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }


        public function fruugoCheckRequiredfields( $product_ids )
        {
            $product_id = $product_ids[0];
            // $product_terms = get_the_terms($product_id, 'pa_brand');
            // foreach ($product_terms as $key => $value) {
            //     # code...
            // print_r($value->name);
            // }
            $product = wc_get_product( $product_id );

            if( isset($this->isProfileAssignedToProduct) && $this->isProfileAssignedToProduct )
            {
                $preparedData_profile = $this->getFormatedData($product_id , true);
                // print_r($preparedData_profile);die;
                $missingValues = array();
                if(isset($preparedData_profile[$product_id]) && is_array($preparedData_profile[$product_id]))
                {

                    if(empty($preparedData_profile[$product_id]['description'])){
                        $missingValues[] = __('Description' , 'ced-fruugo' );
                    }
                    if(empty($preparedData_profile[$product_id]['image1'])){
                        $missingValues[] = __('Product Image' , 'ced-fruugo' );
                    }
                    if(empty($preparedData_profile[$product_id]['product_sku'])){
                        $missingValues[] = __('Sku' , 'ced-fruugo');
                    }
                    if(empty($preparedData_profile[$product_id]['price'])){
                        $missingValues[] = __('Price' , 'ced-fruugo');
                    }
                    // return $missingValues;
                }
                $this->fetchAssignedProfileDataOfProduct( $product_id );

                    // print_r($this->profile_data);die('ds');
                foreach ($this->profile_data as $key_prodata => $value_prodata) {
                    $product_datas[$key_prodata] = $this->fetchMetaValueOfProduct($product_id , $key_prodata);
                }
                        // print_r($product_datas);die;
                if (isset($product_datas) && is_array($product_datas)) {

                    if(empty($product_datas['_umb_fruugo_standard_code_val'])){
                        $missingValues[] = "Standard Code";
                    }
                    if(empty($product_datas['_umb_fruugo_brand'])){
                        $missingValues[] = "Brand";
                    }
                    if(empty($product_datas['_umb_fruugo_category'])){
                        $missingValues[] = "Category";
                    }
                    if(empty($product_datas['_umb_fruugo_vat'])){
                        // $missingValues[] = "Vat";
                    }
                    return $missingValues;
                }
            }
            else
            {
                $productType = $product->get_type();
                try {
                    // print_r($product_id);
                    $preparedData = $this->getFormatedData($product_id  , true);
                                // print_r($preparedData);
                    $parent_product = $product->get_parent_id();
                    if(isset($preparedData[$product_id]) && is_array($preparedData[$product_id]))
                    {
                        if(empty($preparedData[$product_id]['brand'])){
                            $missingValues[] = __('Brand' , 'ced-fruugo' );
                        }
                        if(empty($preparedData[$product_id]['description'])){
                            $missingValues[] = __('Description' , 'ced-fruugo' );
                        }
                        if(empty($preparedData[$product_id]['standard_code'])){
                            $missingValues[] = __('Standard Code' , 'ced-fruugo' );
                        }
                        if(empty($preparedData[$product_id]['category'])){
                            $missingValues[] = __('Category' , 'ced-fruugo' );
                        }
                        if(empty($preparedData[$product_id]['image1'])){
                            $missingValues[] = __('Product Image' , 'ced-fruugo' );
                        }
                        if(empty($preparedData[$product_id]['product_sku'])){
                            $missingValues[] = __('Sku' , 'ced-fruugo');
                        }
                        if(empty($preparedData[$product_id]['price'])){
                            $missingValues[] = __('Price' , 'ced-fruugo');
                        }
                        if(empty($preparedData[$product_id]['vat'])){
                            // $missingValues[] = __('Vat' , 'ced-fruugo');
                        }

                        return $missingValues;
                    }
                }
                catch(Exception $e) {
                    $this->error_msg.="Message: ".$productId."--".$e;
                }
                // $missingValues[] = __( 'missing data', 'ced-fruugo' );
            }
        }

        /**
         * This function is to upload products on fruugo
         * @name upload()
         * @author CedCommerce <plugins@cedcommerce.com>
         * @link  http://www.cedcommerce.com/
         */
        function upload( $productIds=array() ) {
            // error_reporting(~0);
            // ini_set('display_errors', 1);
            // echo "1";print_r($productIds);
            $chunk_size=get_option('_ced_frugo_chunk');
            if($chunk_size=='' || $chunk_size==0){
                $chunk_size=100;
            }
            if(is_array($productIds) && !empty($productIds)){
                // self::prepareApi();
                // print_r(count($productIds));die;
                if(count($productIds) <= $chunk_size){

                    self::prepareItems($productIds);
                }
                else{
                    update_option('fruuggo_prod_files' , '');
                    update_option('fruugo_prod_offset' , '');
                    global $wpdb;
                    $table_cron_daily = $wpdb->prefix.'fruugo_products_upload';
                    if($wpdb->get_var("SHOW TABLES LIKE '$table_cron_daily'") != $table_cron_daily) {
                        $table_cron = "CREATE TABLE IF NOT EXISTS {$table_cron_daily} (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,`pids` TEXT DEFAULT '',PRIMARY KEY (id));";
                        $wpdb->query( $table_cron );
                    }
                    $del = "TRUNCATE TABLE `$table_cron_daily`;" ;
                    $wpdb->query( $del );
                    $productIds = array_chunk($productIds, $chunk_size);
                    foreach ($productIds as $key_sku => $value_sku){
                        set_time_limit(-1);
                        ignore_user_abort(true);
                        ini_set('memory_limit', '-1');
                        $val = json_encode($value_sku);
                        $wpdb->insert($table_cron_daily, array('pids'=>$val));
                //          die('dfs');
                    }
                    $qry                =   "SELECT * from `$table_cron_daily` ;";
                    $resultdata         =   $wpdb->get_results($qry);
                    // print_r($productIds);die('fg');
                    $notice['message'] = 'Product will be added to CSV please set cron you can get path from other section.';
                    $notice['classes'] = 'notice notice-success is-dismissable';
                    $this->final_response = $notice;
                }



                //self::doupload();
                return json_encode($this->final_response);
            }
        }

        public function prepareItems($productIds ,$cron = 'False' , $Offset = 'False'){
            // print_r($productIds);die;
            // error_reporting(~0);
            // ini_set('display_errors', 1);
            if(is_array($productIds) && !empty($productIds))
            {
                $this->error_msg = "";
                foreach ($productIds as $productId) {
                    set_time_limit(-1);
                    ignore_user_abort(true);
                    ini_set('memory_limit', '-1');
                    // delete_post_meta( $productId, 'fruugoSkuId' );
                    $this->fetchAssignedProfileDataOfProduct( $productId );
                    
                    $check_if_uploaded = get_post_meta($productId, "fruugoSkuId", false);
                    $check_if_uploaded = false;
                    $already_present = false;
                    if(!$check_if_uploaded) {

                        $_product = wc_get_product( $productId );
                        $image_id = get_post_thumbnail_id( $_product->get_id() );
                        $productType = $_product->get_type();
                        try {

                            if($productType == 'variable') 
                            {
                                // $this->data = $preparedData;
                                $variations = $_product->get_available_variations();

                                foreach ($variations as $variation) {
                                    $preparedData[$variation['variation_id']] = $this->getFormatedDataForVariation($variation , $productId);
                                    if(!is_array($preparedData[$variation['variation_id']])){
                                        unset($preparedData[$variation['variation_id']]);
                                    }
                                }

                            }
                            else 
                            {
                                $preparedData[$productId] = $this->getFormatedData($productId);
                                if(!is_array($preparedData[$productId])){
                                    unset($preparedData[$productId]);
                                }
                                //print_r($preparedData);die;

                            }

                        }
                        catch(Exception $e) {
                            $this->error_msg.="Message: ".$productId."--".$e->getLastResponse();
                        }
                    }
                    else {
                        $already_present = true;
                    }

                }
                if(isset($preparedData) && is_array($preparedData) &&!empty($preparedData)){

                    $this->create_csv($preparedData , $cron , $Offset );
                }else{
                    $Offset_to_save = $Offset + 1;
                    update_option('fruugo_prod_offset' , $Offset_to_save );
                }

                if($this->error_msg != "") {
                    if($already_present) {
                        $this->error_msg .= "Some products are already present.";
                    }
                    $message = $this->error_msg;
                    $classes = "error is-dismissable";
                    $this->final_response = array('message'=>$message,'classes'=>$classes);

                }
                else {

                    $notice['message'] = 'Product added successfully on CSV.';
                    if($already_present) {
                        $notice['message'] = "Same product are already present.";
                    }
                    $notice['classes'] = 'notice notice-success is-dismissable';
                    $this->final_response = $notice;
                }
            }
        }

        public function create_csv($preparedData , $cron = 'False' , $Offset = 'False' ){
           
            $wpuploadDir    =   wp_upload_dir();
            $baseDir        =   $wpuploadDir['basedir'];
            $uploadDir      =   $baseDir . '/cedcommerce_fruugouploads';
            $nameTime=time();
            if (! is_dir($uploadDir))
            {
                mkdir( $uploadDir, 0777 ,true);
            }
            //echo count($preparedData);
            if($cron == 'cron_products'){
                $file = fopen($uploadDir.'/Merchant'.$Offset.'.csv', 'w');
                $csv_files = get_option('fruuggo_prod_files');
                if(empty($csv_files) || $csv_files == ' '){
                    $csv_files =array();
                    $csv_files[$Offset] =  'Merchant'.$Offset ;
                }else{
                    $csv_files[$Offset] =  'Merchant'.$Offset ;
                }
                update_option('fruuggo_prod_files' , $csv_files);
                $csv_files = get_option('fruuggo_prod_files');
                //print_r($csv_files);
                $Offset = $Offset + 1;
                update_option('fruugo_prod_offset' , $Offset );
            }else{

                $file = fopen($uploadDir.'/Merchant.csv', 'w');
            }

            if(isset($preparedData) && is_array($preparedData) && !empty($preparedData))
            {
                $count = 0;
                foreach ($preparedData as $key_preparedData => $value_preparedData) {
                    if($count == 0){
                        foreach ($value_preparedData as $key_header => $value_header) {
                            $key_prodata[] = $key_header;
                        }
                    }
                    $count++;
                    $value_preparedDatas[] = $value_preparedData;
                }

                fputcsv($file , $key_prodata);
                foreach ($value_preparedDatas as $key => $value) {
                    fputcsv($file, $value);
                }
            }
        }

        public function getFormatedData($productId , $validate = false){
            // error_reporting(~0);
            // ini_set('display_errors', 1);
            $standard_code = '';
            $brand = '';
            $category = '';
            $language = '';
            $attributeSize = '';
            $attributeColor = '';
            $attribute1 = '';
            $attribute2 = '';
            $attribute3 = '';
            $attribute4 = '';
            $attribute5 = '';
            $attribute6 = '';
            $attribute7 = '';
            $attribute8 = '';
            $attribute9 = '';
            $attribute10 = '';
            $fruugoCurrency = '';
            $leadtime  ='';
            $ced_fruugo_packageWeight = '';
            $vat = '';
            $dis_price ='';
            $ced_country='';
            if($productId){
                $this->fetchAssignedProfileDataOfProduct( $productId );
                $product = wc_get_product( $productId );

                if( WC()->version > '3.0.0' )
                {
                    $product_data = $product->get_data();
                    // print_r($product_data);die;
                    $productType = $product->get_type();
                    $quantity = (int)get_post_meta($productId,'_stock',true);

                    $title = $product_data['name'];
                    $price = (float)$product_data['regular_price'];
                    $price = round($price,2);
                    $description = !empty($product_data['description'])?$product_data['description']:$product_data['short_description'];
                    if(empty($description)){
                        $des_id = $product->get_parent_id();
                        if($des_id > 0){
                            $product = wc_get_product( $des_id );
                            $descri_vari = $product->get_data();
                            $description = $descri_vari['description'];
                            
                        }
                    }
                }
                $product_sku = get_post_meta( $productId, '_sku', true );
                $image =wp_get_attachment_url( $product_data['image_id'] );
                if(empty($image)){
                    //$image_id = $product->get_parent_id();
                    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $productId ));
                }
                $attachmentIds = $product->get_gallery_image_ids();
                $imagesec = array();
                foreach( $attachmentIds as $attachmentId )
                {
                    $imagesec[] = wp_get_attachment_url( $attachmentId );
                }
                $imagesec0 = '';
                $imagesec1 = '';
                $imagesec2 = '';
                $imagesec3 = '';
                if(isset($imagesec['0'])){
                    $imagesec0 = $imagesec['0'];
                }
                if(isset($imagesec['1'])){
                    $imagesec1 = $imagesec['1'];
                }
                if(isset($imagesec['2'])){
                    $imagesec2 = $imagesec['2'];
                }
                if(isset($imagesec['3'])){
                    $imagesec3 = $imagesec['3'];
                }
                // print_r($imagesec);die;
                // $quantity = (int)get_post_meta($productId,'_stock',true);
                if( isset($this->isProfileAssignedToProduct) && $this->isProfileAssignedToProduct )
                {
                    // print_r($this->profile_data);die;
                    $ced_country=isset($this->profile_data['selected_product_country'])?$this->profile_data['selected_product_country']:array();
                    foreach ($this->profile_data as $key_prodata => $value_prodata) {
                        $product_datas[$key_prodata] = $this->fetchMetaValueOfProduct($productId , $key_prodata);
                    }
                       //print_r($product_datas);die;
                    if (isset($product_datas) && is_array($product_datas)) {

                        //$standard_code = get_post_meta($productId,'_umb_fruugo_standard_code_val', true);
                        $brand = get_post_meta($productId, '_umb_fruugo_brand' , true);
                        $category = get_post_meta($productId,'_umb_fruugo_category' , true);
                        $language = get_post_meta($productId, '_ced_fruugo_language_section' , true);
                        $attributeSize = get_post_meta($productId, '_ced_fruugo_attributeSize' , true);
                        $attributeColor = get_post_meta($productId, '_ced_fruugo_attributeColor' , true);
                        $fruugoCurrency = get_post_meta($productId, '_ced_fruugo_currency' , true);
                        $leadtime = get_post_meta($productId, '_ced_fruugo_leadTime' , true);
                        $ced_fruugo_packageWeight = get_post_meta($productId , '_ced_fruugo_packageWeight',true);
                        $vat = get_post_meta($productId , '_umb_fruugo_vat' ,true);
                        $dis_price = get_post_meta($productId,'_umb_fruugo_discount_price',true);


                        $standard_code = !empty($standard_code) ? $standard_code : $product_datas['_umb_fruugo_standard_code_val'];
                        $brand =!empty($brand) ? $brand : $product_datas['_umb_fruugo_brand'];
                        $category =!empty($category) ? $category : $product_datas['_umb_fruugo_category'];
                        $language = !empty($language) ? $language : $product_datas['_ced_fruugo_language_section'];
                        $attributeSize = !empty($attributeSize) ? $attributeSize : $product_datas['_ced_fruugo_attributeSize'];
                        $attributeColor = !empty($attributeColor) ? $attributeColor : $product_datas['_ced_fruugo_attributeColor'];
                        $attribute1 = isset($product_datas['_ced_fruugo_attribute1'])?$product_datas['_ced_fruugo_attribute1']:'';
                        $attribute2 = isset($product_datas['_ced_fruugo_attribute2'])?$product_datas['_ced_fruugo_attribute2']:'';
                        $attribute3 = isset($product_datas['_ced_fruugo_attribute3'])?$product_datas['_ced_fruugo_attribute3']:'';
                        $attribute4 = isset($product_datas['_ced_fruugo_attribute4'])?$product_datas['_ced_fruugo_attribute4']:'';
                        $attribute5 = isset($product_datas['_ced_fruugo_attribute5'])?$product_datas['_ced_fruugo_attribute5']:'';
                        $attribute6 = isset($product_datas['_ced_fruugo_attribute6'])?$product_datas['_ced_fruugo_attribute6']:'';
                        $attribute7 = isset($product_datas['_ced_fruugo_attribute7'])?$product_datas['_ced_fruugo_attribute7']:'';
                        $attribute8 = isset($product_datas['_ced_fruugo_attribute8'])?$product_datas['_ced_fruugo_attribute8']:'';
                        $attribute9 = isset($product_datas['_ced_fruugo_attribute9'])?$product_datas['_ced_fruugo_attribute9']:'';
                        $attribute10 = isset($product_datas['_ced_fruugo_attribute10'])?$product_datas['_ced_fruugo_attribute10']:'';
                        $fruugoCurrency = !empty($fruugoCurrency) ? $fruugoCurrency : $product_datas['_ced_fruugo_currency'];
                        $leadtime =!empty($leadtime) ? $leadtime : $product_datas['_ced_fruugo_leadTime'];
                        $ced_fruugo_packageWeight = !empty($ced_fruugo_packageWeight) ? $ced_fruugo_packageWeight : $product_datas['_ced_fruugo_packageWeight'];
                        $vat = !empty($vat) ? $vat : $product_datas['_umb_fruugo_vat'];
                        $dis_price = !empty($dis_price) ? $dis_price :'';
                        $increase_price_by = isset($product_datas['_ced_increase_price_fruugo']) ? intval( $product_datas['_ced_increase_price_fruugo']) : '';
                        if(!empty($ced_country))
                        {
                            $ced_fruugo_country=implode(" ",$ced_country);
                        }
                        else{
                            $ced_fruugo_country='';
                        }

                    }
                }else{

                    $standard_code = get_post_meta($productId,'_umb_fruugo_standard_code_val', true);
                    $brand = get_post_meta($productId, '_umb_fruugo_brand' , true);
                    $category = get_post_meta($productId,'_umb_fruugo_category' , true);
                    $language = get_post_meta($productId, '_ced_fruugo_language_section' , true);
                    $attributeSize = get_post_meta($productId, '_ced_fruugo_attributeSize' , true);
                    $attribute1 = get_post_meta($productId, '_ced_fruugo_attribute1' , true);
                    $attributeColor = get_post_meta($productId, '_ced_fruugo_attributeColor' , true);
                    $fruugoCurrency = get_post_meta($productId, '_ced_fruugo_currency' , true);
                    $leadtime = get_post_meta($productId, '_ced_fruugo_leadTime' , true);
                    $ced_fruugo_packageWeight = get_post_meta($productId , '_ced_fruugo_packageWeight',true);
                    $vat = get_post_meta($productId , '_umb_fruugo_vat' ,true);
                    $dis_price = get_post_meta($productId,'_umb_fruugo_discount_price',true);
                    $increase_price_by = intval( get_post_meta($productId , '_ced_increase_price_fruugo', true));

                }
                $status=get_post_meta($productId,"ced_status",true);
                if($status=="Updated"){
                $brand = get_post_meta($productId, 'Brand' , true);
                $category = get_post_meta($productId,'Category' , true);
                $language = get_post_meta($productId, 'Language' , true);
                $attributeSize = get_post_meta($productId, 'AttributeSize' , true);
                $attributeColor = get_post_meta($productId, 'AttributeColor' , true);
                $fruugoCurrency = get_post_meta($productId, 'Currency' , true);
                $leadtime = get_post_meta($productId, 'LeadTime' , true);
    $ced_fruugo_packageWeight = get_post_meta($productId , 'PackageWeight',true);
    $vat = get_post_meta($productId , 'VATRate' ,true);
    $dis_price = get_post_meta($productId,'DiscountPriceWithoutVAT',true);
    $image = get_post_meta($productId,'Imageurl1',true);
        $description=get_post_meta($productId,'Description',true);
        $standard_code = get_post_meta($productId, 'EAN' , true);
            
        $attribute1 =get_post_meta($productId, 'Attribute1' , true);
        $attribute2 = get_post_meta($productId, 'Attribute2' , true);
        $attribute3 = get_post_meta($productId, 'Attribute3' , true);
        $attribute4 = get_post_meta($productId, 'Attribute4' , true);
        $attribute5 = get_post_meta($productId, 'Attribute5' , true);
        $attribute6 = get_post_meta($productId, 'Attribute6' , true);
        $attribute7 = get_post_meta($productId, 'Attribute7' , true);
        $attribute8 = get_post_meta($productId, 'Attribute8' , true);
        $attribute9 = get_post_meta($productId, 'Attribute9' , true);
        $attribute10 = get_post_meta($productId, 'Attribute10' ,true);
        $productId = get_post_meta($productId, 'ProductId' , true);

}
                if($quantity == 0){
                    $stock_status = "OUTOFSTOCK";
                }else{
                    $stock_status = "INSTOCK";
                }
                if(isset($increase_price_by) && !empty($increase_price_by) && is_int($increase_price_by) && $increase_price_by < 100){
                    $price = $price+(($price*$increase_price_by)/100);
                    if($dis_price > 0)
                        $dis_price = $dis_price+(($dis_price*$increase_price_by)/100);
                }
                $category = str_replace('>', ' > ', $category);
                $category = str_replace('&', ' & ', $category);
                $category = preg_replace('/(\w+)([A-Z])/U', '\\1 \\2', $category);
                $leadtime = 7;
                if(empty($product_sku))
                    $product_sku = $productId;
                // die;
                if((!empty($product_sku)) && (!empty($product_sku)) && (!empty($standard_code)) && (!empty($brand)) && (!empty($category)) && (!empty($image)) && (!empty($title)) && (!empty($description)) && (!empty($price)))
                {
                    $args = array(
                    // 'data' => array(
                        "SkuId" => $product_sku,
                        "ProductId" => $productId,
                        "Title" => $title,
                        "StockQuantity" => $quantity,
                        "Description" => strip_tags($description),
                        "NormalPriceWithoutVAT" => round($price,2),
                        "EAN" => $standard_code,
                        "Brand" => $brand,
                        "Category" => $category,
                        "Imageurl1" => $image,
                        "VATRate"=> !empty($vat) ? $vat : 0,
                        "Language" =>!empty($language) ? $language : '',
                        "AttributeSize" => !empty($attributeSize) ? $attributeSize : '',
                        "AttributeColor" => !empty($attributeColor) ? $attributeColor : '',
                        "Attribute1" => !empty($attribute1) ? $attribute1 : '',
                        "Attribute2" => !empty($attribute2) ? $attribute2 : '',
                        "Attribute3" => !empty($attribute3) ? $attribute3 : '',
                        "Attribute4" => !empty($attribute4) ? $attribute4 : '',
                        "Attribute5" => !empty($attribute5) ? $attribute5 : '',
                        "Attribute6" => !empty($attribute6) ? $attribute6 : '',
                        "Attribute7" => !empty($attribute7) ? $attribute7 : '',
                        "Attribute8" => !empty($attribute8) ? $attribute8 : '',
                        "Attribute9" => !empty($attribute9) ? $attribute9 : '',
                        "Attribute10" => !empty($attribute10) ? $attribute10 : '',
                        "Currency" => !empty($fruugoCurrency) ? $fruugoCurrency : '',
                        "LeadTime" => !empty($leadtime) ? $leadtime : '',
                        "PackageWeight" => !empty($ced_fruugo_packageWeight) ? $ced_fruugo_packageWeight : '',
                        "DiscountPriceWithoutVAT" => !empty($dis_price) ? $dis_price : '',
                        'StockStatus' => !empty($stock_status) ? $stock_status : ' ',
                        "Imageurl2" => !empty($imagesec0) ? $imagesec0 : '',
                        "Imageurl3" => !empty($imagesec1) ? $imagesec1 : '',
                        "Imageurl4" => !empty($imagesec2) ? $imagesec2 : '',
                        "Imageurl5" => !empty($imagesec3)? $imagesec3 : '',
                        'Country' =>!empty($ced_fruugo_country)? $ced_fruugo_country : '',
                        // )
                        );
                    // print_r($args);
                    // die;
                    return $args;
                }
                else if($validate){
                    $args[$productId] = array(
                    // 'data' => array(
                        "quantity" => $quantity,
                        "title" => $title,
                        "description" => strip_tags($description),
                        "price" => $price,
                        "standard_code" => $standard_code,
                        "brand" => $brand,
                        "category" => $category,
                        "image1" => $image,
                        "image2" => $imagesec0,
                        "image3" => $imagesec1,
                        "image4" => $imagesec2,
                        "image5" => $imagesec3,
                        "vat"=> $vat,
                        "product_sku" => $product_sku,
                        "product_id" => $product_sku,
                        "lang_fruugo" =>$language,
                        "attri_size" => $attributeSize,
                        "attri_color" => $attributeColor,
                        "attri_1" =>$attribute1,
                        "attri_2" =>$attribute2,
                        "attri_3" =>$attribute3,
                        "attri_4" =>$attribute4,
                        "attri_5" =>$attribute5,
                        "attri_6" =>$attribute6,
                        "attri_7" =>$attribute7,
                        "attri_8" =>$attribute8,
                        "attri_9" =>$attribute9,
                        "attri_10" =>$attribute10,
                        "fruugo_curreny" => $fruugoCurrency,
                        "leadtime" => $leadtime,
                        "packwidth" => $ced_fruugo_packageWeight,
                        "discount_price" => $dis_price,
                        // )
                        );
                    return $args;
                }

            }
        }

        public function getFormatedDataForVariation($variation , $productId){

            $_product = wc_get_product( $productId );
            $product_data = $_product->get_data();
            $description = $description = !empty($product_data['description'])?$product_data['description']:$product_data['short_description'];
            $variations = $_product->get_available_variations();
            // print_r($variations);die;
            $args = array();
            foreach ($variations as $variation1) {

                $product_sku1 = get_post_meta( $variation1['variation_id'], '_sku', true );
                $product_id1[] = $variation1['variation_id'];
            }
            $product_sku = get_post_meta( $variation['variation_id'], '_sku', true );

                // echo $variation['variation_id'];
                // $productId = $variation['variation_id'];
            $image =wp_get_attachment_url( $variation['image_id'] );
            if(empty($image)){
                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $productId ));
                // print_r($image[1]);
            }
            $attachmentIds = $_product->get_gallery_image_ids();
            $imagesec = array();
            foreach( $attachmentIds as $attachmentId )
            {
                $imagesec[] = wp_get_attachment_url( $attachmentId );
            }
            $imagesec0 = '';
            $imagesec1 = '';
            $imagesec2 = '';
            $imagesec3 = '';
            if(isset($imagesec['0'])){
                $imagesec0 = $imagesec['0'];
            }
            if(isset($imagesec['1'])){
                $imagesec1 = $imagesec['1'];
            }
            if(isset($imagesec['2'])){
                $imagesec2 = $imagesec['2'];
            }
            if(isset($imagesec['3'])){
                $imagesec3 = $imagesec['3'];
            }
            $price = get_post_meta($variation['variation_id'],'_regular_price' , true);
            $quantity = (int)get_post_meta($variation['variation_id'],'_stock',true);
            if($quantity == 0){
                $stock_status = "OUTOFSTOCK";
            }else{
                $stock_status = "INSTOCK";
            }
            $title = get_post_meta($variation['variation_id'],'_umb_fruugo_variation_title' , true);
            if(empty($title)){

                $title = $product_data['name'];

            }
            $this->fetchAssignedProfileDataOfProduct( $productId );

            if( isset($this->isProfileAssignedToProduct) && $this->isProfileAssignedToProduct )
            {
                   //print_r($this->profile_data);die;
                $ced_country=isset($this->profile_data['selected_product_country'])?$this->profile_data['selected_product_country']:array();
                foreach ($this->profile_data as $key_prodata => $value_prodata) {
                    $product_datas[$key_prodata] = $this->fetchMetaValueOfProduct($variation['variation_id'] , $key_prodata);
                    if(empty($product_datas[$key_prodata])){
                        $product_datas[$key_prodata] = $this->fetchMetaValueOfProduct($productId , $key_prodata);
                        
                    }
                }
                        //print_r($product_datas);die;
                if (isset($product_datas) && is_array($product_datas)) {

                        //$standard_code = get_post_meta($variation['variation_id'],'_umb_fruugo_standard_code_val', true);
                        $brand = get_post_meta($variation['variation_id'], '_umb_fruugo_brand' , true);
                        $category = get_post_meta($variation['variation_id'],'_umb_fruugo_category' , true);
                        $language = get_post_meta($variation['variation_id'], '_ced_fruugo_language_section' , true);
                        $attributeSize = get_post_meta($variation['variation_id'], '_ced_fruugo_attributeSize' , true);
                        $attributeColor = get_post_meta($variation['variation_id'], '_ced_fruugo_attributeColor' , true);
                        $fruugoCurrency = get_post_meta($variation['variation_id'], '_ced_fruugo_currency' , true);
                        $leadtime = get_post_meta($variation['variation_id'], '_ced_fruugo_leadTime' , true);
                        $ced_fruugo_packageWeight = get_post_meta($variation['variation_id'] , '_ced_fruugo_packageWeight',true);
                        $vat = get_post_meta($variation['variation_id'] , '_umb_fruugo_vat' ,true);
                        $dis_price = get_post_meta($variation['variation_id'],'_umb_fruugo_discount_price',true);


                        $standard_code = !empty($standard_code) ? $standard_code : $product_datas['_umb_fruugo_standard_code_val'];
                        $brand =!empty($brand) ? $brand : $product_datas['_umb_fruugo_brand'];
                        $category =!empty($category) ? $category : $product_datas['_umb_fruugo_category'];
                        $language = !empty($language) ? $language : $product_datas['_ced_fruugo_language_section'];
                        $attributeSize = !empty($attributeSize) ? $attributeSize : $product_datas['_ced_fruugo_attributeSize'];
                        $attributeColor = !empty($attributeColor) ? $attributeColor : $product_datas['_ced_fruugo_attributeColor'];
                        $attribute1 = isset($product_datas['_ced_fruugo_attribute1'])?$product_datas['_ced_fruugo_attribute1']:'';
                        $attribute2 = isset($product_datas['_ced_fruugo_attribute2'])?$product_datas['_ced_fruugo_attribute2']:'';
                        $attribute3 = isset($product_datas['_ced_fruugo_attribute3'])?$product_datas['_ced_fruugo_attribute3']:'';
                        $attribute4 = isset($product_datas['_ced_fruugo_attribute4'])?$product_datas['_ced_fruugo_attribute4']:'';
                        $attribute5 = isset($product_datas['_ced_fruugo_attribute5'])?$product_datas['_ced_fruugo_attribute5']:'';
                        $attribute6 = isset($product_datas['_ced_fruugo_attribute6'])?$product_datas['_ced_fruugo_attribute6']:'';
                        $attribute7 = isset($product_datas['_ced_fruugo_attribute7'])?$product_datas['_ced_fruugo_attribute7']:'';
                        $attribute8 = isset($product_datas['_ced_fruugo_attribute8'])?$product_datas['_ced_fruugo_attribute8']:'';
                        $attribute9 = isset($product_datas['_ced_fruugo_attribute9'])?$product_datas['_ced_fruugo_attribute9']:'';
                        $attribute10 = isset($product_datas['_ced_fruugo_attribute10'])?$product_datas['_ced_fruugo_attribute10']:'';
                        $fruugoCurrency = !empty($fruugoCurrency) ? $fruugoCurrency : $product_datas['_ced_fruugo_currency'];
                        $leadtime =!empty($leadtime) ? $leadtime : $product_datas['_ced_fruugo_leadTime'];
                        $ced_fruugo_packageWeight = !empty($ced_fruugo_packageWeight) ? $ced_fruugo_packageWeight : $product_datas['_ced_fruugo_packageWeight'];
                        $vat = !empty($vat) ? $vat : $product_datas['_umb_fruugo_vat'];
                        $dis_price = !empty($dis_price) ? $dis_price : $product_datas['_umb_fruugo_discount_price'];
                        $increase_price_by = isset($product_datas['_ced_increase_price_fruugo']) ? intval( $product_datas['_ced_increase_price_fruugo']) : '';
                        if(!empty($ced_country))
                        {
                            $ced_fruugo_country=implode(" ",$ced_country);
                        }
                        else{
                            $ced_fruugo_country='';
                        }

                    }
            }
            else{

                $brand = get_post_meta($variation['variation_id'], '_umb_fruugo_brand' , true);
                $category = get_post_meta($variation['variation_id'],'_umb_fruugo_category' , true);
                $standard_code = get_post_meta($variation['variation_id'],'_umb_fruugo_standard_code_val', true);
                $language = get_post_meta($variation['variation_id'], '_ced_fruugo_language_section' , true);
                $attributeSize = get_post_meta($variation['variation_id'], '_ced_fruugo_attributeSize' , true);
                $attributeColor = get_post_meta($variation['variation_id'], '_ced_fruugo_attributeColor' , true);
                $fruugoCurrency = get_post_meta($variation['variation_id'], '_ced_fruugo_currency' , true);
                $leadtime = get_post_meta($variation['variation_id'], '_ced_fruugo_leadTime' , true);
                $vat = get_post_meta($variation['variation_id'] , '_umb_fruugo_vat' ,true);
                $ced_fruugo_packageWeight = get_post_meta($variation['variation_id'] , '_ced_fruugo_packageWeight',true);
                $dis_price = get_post_meta($variation['variation_id'],'_umb_fruugo_discount_price',true);
                $dis_price = get_post_meta($variation['variation_id'],'_umb_fruugo_discount_price',true);
                $increase_price_by = intval( get_post_meta($variation['variation_id'],'_ced_increase_price_fruugo',true));

            }
            if(isset($increase_price_by) && !empty($increase_price_by) && is_int($increase_price_by) && $increase_price_by < 100){
                $price = $price+(($price*$increase_price_by)/100);
                if($dis_price > 0)
                    $dis_price = $dis_price+(($dis_price*$increase_price_by)/100);
            }
            $category = str_replace('>', ' > ', $category);
            $category = str_replace('&', ' & ', $category);
            $category = preg_replace('/(\w+)([A-Z])/U', '\\1 \\2', $category);
            $leadtime = 7;

            if((!empty($product_sku)) && (!empty($product_sku)) && (!empty($standard_code)) && (!empty($brand)) && (!empty($category)) && (!empty($image)) && (!empty($title)) && (!empty($description)) && (!empty($price)))
            {
                $args =  array(
                    "SkuId" => $product_sku,
                    "ProductId" => $product_id1[0],
                    "Title" => $title,
                    "StockQuantity" => $quantity,
                    "Description" => strip_tags($description),
                    "NormalPriceWithoutVAT" => $price,
                    "EAN" => $standard_code,
                    "Brand" => $brand,
                    "Category" => $category,
                    "Imageurl1" => $image,
                    "VATRate"=> !empty($vat) ? $vat : 0,
                    "Language" =>!empty($language) ? $language : '',
                    "AttributeSize" => !empty($attributeSize) ? $attributeSize : '',
                    "AttributeColor" => !empty($attributeColor) ? $attributeColor : '',
                    "Attribute1" => !empty($attribute1) ? $attribute1 : '',
                    "Attribute2" => !empty($attribute2) ? $attribute2 : '',
                    "Attribute3" => !empty($attribute3) ? $attribute3 : '',
                    "Attribute4" => !empty($attribute4) ? $attribute4 : '',
                    "Attribute5" => !empty($attribute5) ? $attribute5 : '',
                    "Attribute6" => !empty($attribute6) ? $attribute6 : '',
                    "Attribute7" => !empty($attribute7) ? $attribute7 : '',
                    "Attribute8" => !empty($attribute8) ? $attribute8 : '',
                    "Attribute9" => !empty($attribute9) ? $attribute9 : '',
                    "Attribute10" => !empty($attribute10) ? $attribute10 : '',
                    "Currency" => !empty($fruugoCurrency) ? $fruugoCurrency : '',
                    "LeadTime" => !empty($leadtime) ? $leadtime : '',
                    "PackageWeight" => !empty($ced_fruugo_packageWeight) ? $ced_fruugo_packageWeight: '',
                    "DiscountPriceWithoutVAT" => !empty($dis_price) ? $dis_price : '',
                    'StockStatus' => !empty($stock_status) ? $stock_status : '',
                    "Imageurl2" => !empty($imagesec0) ? $imagesec0 : '',
                    "Imageurl3" => !empty($imagesec1) ? $imagesec1 : '',
                    "Imageurl4" => !empty($imagesec2) ? $imagesec2 : '',
                    "Imageurl5" => !empty($imagesec3)? $imagesec3 : '',
                    'Country' =>$ced_fruugo_country,
                    );
                // print_r($args);die();
                return $args;
            }
            // }
            // print_r($args);

        }
        /**
         * This function fetches data in accordance with profile assigned to product.
         * @name fetchAssignedProfileDataOfProduct()
         * @author CedCommerce <plugins@cedcommerce.com>
         * @link  http://www.cedcommerce.com/
         */
        function fetchAssignedProfileDataOfProduct( $product_id ) {
            global $wpdb;
            $table_name = $wpdb->prefix.CED_FRUUGO_PREFIX.'_fruugoprofiles';
            $profileID = get_post_meta( $product_id, 'ced_fruugo_profile', true);
            $profile_data = array();
            if( isset($profileID) && !empty($profileID) && $profileID != "" ) {
                $this->isProfileAssignedToProduct = true;
                $profileid = $profileID;
                $query = "SELECT * FROM `$table_name` WHERE `id`=$profileid";
                $profile_data = $wpdb->get_results($query,'ARRAY_A');
                if(is_array($profile_data)) {
                    $profile_data = isset($profile_data[0]) ? $profile_data[0] : $profile_data;
                    $profile_data = isset($profile_data['profile_data']) ? json_decode($profile_data['profile_data'],true) : array();
                }
            }
            else {
                $this->isProfileAssignedToProduct = false;
            }
            $this->profile_data = $profile_data;
        }
        
        /**
         * This function fetches meta value of a product in accordance with profile assigned and meta value available.
         * @name fetchMetaValueOfProduct()
         * @author CedCommerce <plugins@cedcommerce.com>
         * @link  http://www.cedcommerce.com/
         */

        function fetchMetaValueOfProduct( $product_id, $metaKey ) {

            if(isset($this->isProfileAssignedToProduct) && $this->isProfileAssignedToProduct) {

                $_product = wc_get_product($product_id);

                if( WC()->version < '3.0.0' ){
                    if( $_product->product_type == "variation" ) {
                        $parentId = $_product->parent->id;
                    }
                    else {
                        $parentId = "0";
                    }
                }else{
                    if( $_product->get_type() == "variation" ) {
                        $parentId = $_product->get_parent_id();
                    }
                    else {
                        $parentId = "0";
                    }
                }

                if(!empty($this->profile_data) && isset($this->profile_data[$metaKey])) {
                    $tempProfileData = $profileData = $this->profile_data[$metaKey];
                    if( isset($tempProfileData['default']) && !empty($tempProfileData['default']) && $tempProfileData['default'] != "" && !is_null($tempProfileData['default']) ) {
                        $value = $tempProfileData['default'];
                    }
                    else if( isset($tempProfileData['metakey']) && !empty($tempProfileData['metakey']) && $tempProfileData['metakey'] != "" && $tempProfileData['metakey'] != "null" && !is_null($tempProfileData['metakey']) ) {

                        //if woo attribute is selected
                        if (strpos($tempProfileData['metakey'], 'umb_pattr_') !== false) {

                            $wooAttribute = explode('umb_pattr_', $tempProfileData['metakey']);
                           // print_r($wooAttribute);
                            $wooAttribute = end($wooAttribute);
                           // print_r( $wooAttribute) ;

                            if( WC()->version < '3.0.0' )
                            {
                                if( $_product->product_type == "variation" ) {
                                    $attributes =  $_product->get_variation_attributes() ;
                                    if(isset($attributes['attribute_pa_'.$wooAttribute]) && !empty($attributes['attribute_pa_'.$wooAttribute])) {
                                        $wooAttributeValue = $attributes['attribute_pa_'.$wooAttribute];
                                        if( $parentId != "0" ){
                                            $product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
                                        }
                                        else {
                                            $product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
                                        }
                                    }
                                    else {
                                        $wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );

                                        $wooAttributeValue = explode(",", $wooAttributeValue);
                                        $wooAttributeValue = $wooAttributeValue[0];

                                        if( $parentId != "0" ) {
                                            $product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
                                        }
                                        else {
                                            $product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
                                        }
                                    }

                                    if(is_array($product_terms) && !empty($product_terms)) {
                                        foreach ($product_terms as $tempkey => $tempvalue) {
                                            if($tempvalue->slug == $wooAttributeValue ) {
                                                $wooAttributeValue = $tempvalue->name;
                                                break;
                                            }
                                        }
                                        if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
                                            $value = $wooAttributeValue;
                                        }
                                        else {
                                            $value = get_post_meta( $product_id, $metaKey, true );
                                        }
                                    }
                                    else {
                                        $value = get_post_meta( $product_id, $metaKey, true );
                                    }
                                }
                                else {
                                    $wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );
                                    
                                    $product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
                                    if(is_array($product_terms) && !empty($product_terms)) {
                                        foreach ($product_terms as $tempkey => $tempvalue) {
                                            if($tempvalue->slug == $wooAttributeValue ) {
                                                $wooAttributeValue = $tempvalue->name;
                                                break;
                                            }
                                        }
                                        if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
                                            $value = $wooAttributeValue;
                                        }
                                        else {
                                            $value = get_post_meta( $product_id, $metaKey, true );
                                        }
                                    }
                                    else {
                                        $value = get_post_meta( $product_id, $metaKey, true );
                                    }
                                }
                            }else{
                                if( $_product->get_type() == "variation" ) {
                                    $attributes =  $_product->get_variation_attributes() ;
                                    if(isset($attributes['attribute_pa_'.$wooAttribute]) && !empty($attributes['attribute_pa_'.$wooAttribute])) {
                                        $wooAttributeValue = $attributes['attribute_pa_'.$wooAttribute];
                                        if( $parentId != "0" ){
                                            $product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
                                        }
                                        else {
                                            $product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
                                        }
                                    }
                                    else {
                                        $wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );


                                        $wooAttributeValue = explode(",", $wooAttributeValue);
                                        $wooAttributeValue = $wooAttributeValue[0];

                                        if( $parentId != "0" ) {
                                            $product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
                                        }
                                        else {
                                            $product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
                                        }
                                    }

                                    if(is_array($product_terms) && !empty($product_terms)) {
                                        foreach ($product_terms as $tempkey => $tempvalue) {
                                            if($tempvalue->slug == $wooAttributeValue ) {
                                                $wooAttributeValue = $tempvalue->name;
                                                break;
                                            }
                                        }
                                        if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
                                            $value = $wooAttributeValue;
                                        }
                                        else {
                                            $value = get_post_meta( $product_id, $metaKey, true );
                                        }
                                    }
                                    else {
                                        $value = get_post_meta( $product_id, $metaKey, true );
                                    }
                                }
                                else {
                                    $wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );

                                    $product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
                                    if(is_array($product_terms) && !empty($product_terms)) {
                                        foreach ($product_terms as $tempkey => $tempvalue) {
                                            if($tempvalue->slug == $wooAttributeValue ) {
                                                $wooAttributeValue = $tempvalue->name;
                                                break;
                                            }
                                        }
                                        if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
                                            $value = $wooAttributeValue;
                                        }
                                        else {
                                            $value = get_post_meta( $product_id, $metaKey, true );
                                        }
                                    }
                                    else {
                                        $value = get_post_meta( $product_id, $metaKey, true );
                                    }
                                }
                            }
                        }
                       // print_r($tempProfileData['metakey'], 'umb_customtaxonony_'); die();
                        if (strpos($tempProfileData['metakey'], 'umb_customtaxonony_') !== false) {
                         //print_r($tempProfileData['metakey']);
                            $wootaxonomy = explode('umb_customtaxonony_', $tempProfileData['metakey']);
                          //print_r( $wootaxonomy);
                            $wootaxonomy = end($wootaxonomy);
                       // print_r($product_id);
                          $term = wp_get_post_terms($product_id,$wootaxonomy,array('fields'=>'names'));
                          // print_r($term);
                          if( is_array($term) && isset( $term[0] ) )
                            return $term[0];
                        }
                        else {

                            $value = get_post_meta( $product_id, $tempProfileData['metakey'], true );
                           // print_r( $value );
                            if($tempProfileData['metakey'] == '_thumbnail_id'){
                                $value = wp_get_attachment_image_url( get_post_meta( $product_id,'_thumbnail_id',true), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $product_id,'_thumbnail_id',true), 'thumbnail' ) : '';
                            }
                            if( !isset($value) || empty($value) || $value == "" || is_null($value) || $value == "0" || $value == "null") {
                                if( $parentId != "0" ) {

                                    $value = get_post_meta( $parentId, $tempProfileData['metakey'], true );
                                    if($tempProfileData['metakey'] == '_thumbnail_id'){
                                        $value = wp_get_attachment_image_url( get_post_meta( $parentId,'_thumbnail_id',true), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $parentId,'_thumbnail_id',true), 'thumbnail' ) : '';
                                    }

                                    if( !isset($value) || empty($value) || $value == "" || is_null($value) ) {
                                        $value = get_post_meta( $product_id, $metaKey, true );

                                    }
                                }
                                else {
                                    $value = get_post_meta( $product_id, $metaKey, true );
                                }
                            }

                        }
                    }
                    else {
                        $value = get_post_meta( $product_id, $metaKey, true );
                    }
                }
                else {
                    $value = get_post_meta( $product_id, $metaKey, true );
                }
            }
            else {
                $value = get_post_meta( $product_id, $metaKey, true );
            }
            
            return $value;
        }
        
        /**
         * This function formats php array in SIMPLE_XML_ELEMENT object.
         * @name array2XML()
         * @author CedCommerce <plugins@cedcommerce.com>
         * @link  http://www.cedcommerce.com/
         */
        function array2XML($xml_obj, $array) {
            foreach ($array as $key => $value) {
                if(is_numeric($key)) {
                    $key = $key;
                }
                if (is_array($value)) {
                    $node = $xml_obj->addChild($key);
                    $this->array2XML($node, $value);
                }
                else {
                    $xml_obj->addChild($key, htmlspecialchars($value));
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
        // function my_cron_schedules($schedules){
        //     if(!isset($schedules["5min"])){
        //         $schedules["5min"] = array(
        //             'interval' => 5*60,
        //             'display' => __('Once every 5 minutes'));
        //     }
        //     if(!isset($schedules["30min"])){
        //         $schedules["30min"] = array(
        //             'interval' => 30*60,
        //             'display' => __('Once every 30 minutes'));
        //     }
        //     return $schedules;
        // }
        // add_filter('cron_schedules','my_cron_schedules');
        // wp_schedule_event(time(), '5min', 'ced_fruugo_cron_job', $args);
        // do_action('ced_fruugo_cron_job', array(CED_FRUUGO_manager,'ced_fruugo_cron_manager'));
    }
}
