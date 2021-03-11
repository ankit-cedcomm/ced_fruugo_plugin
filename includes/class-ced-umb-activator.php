<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woocommerce fruugo Integration
 * @subpackage Woocommerce fruugo Integration/includes
 */
class CED_FRUUGO_Activator {

	/**
	 * Activation actions.
	 *
	 * All required actions on plugin activation.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		self::create_tables();
		// self::register();
	}
	
	/**
	 * Tables necessary for this plugin.
	 * 
	 * @since 1.0.0
	 */
	private static function create_tables(){
		
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		define( 'CED_FRUUGO_TABLE_PREFIX' , 'ced_fruugo' );
		$prefix = $wpdb->prefix . CED_FRUUGO_TABLE_PREFIX;
		$table_name = "{$prefix}_fruugoprofiles";
		// profile table
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $create_profile = "CREATE TABLE {$prefix}_fruugoprofiles (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,`name` VARCHAR(255) NOT NULL DEFAULT '',active bool NOT NULL DEFAULT true,marketplace VARCHAR(255) DEFAULT 'fruugo',profile_data longtext DEFAULT NULL,profile_required_attribute longtext DEFAULT NULL,PRIMARY KEY (id));";
            dbDelta( $create_profile );
        }
		
		update_option('ced_fruugo_database_version',CED_FRUUGO_VERSION);
	}
	 /**
	  * 
	  * 
	  */
	public function scheduleEvent()
	{
		if (! wp_next_scheduled ( 'ced_fruugo_schedule_mail' )) {
			wp_schedule_event(time(), 'daily', 'ced_fruugo_scheduled_mail');
		}
	}

	public function register()
    {
    	$domain_register = get_option( 'register_fru_domain', null );
    	if(empty($domain_register)){
    		update_option('register_fru_domain' , 'yes');
	    	$admin_email = get_option( 'admin_email', null );
	    	$domain = $_SERVER['HTTP_HOST'];
	    	$data =  array('domain'=>$domain,'email'=>$admin_email,'framework'=>'Woocommerce');
	       $url = 'http://admin.apps.cedcommerce.com/magento-fruugo-info/create?'.http_build_query($data);
	       $headers = array();
	       $headers[] = "Content-Type: application/json";
	       $ch = curl_init();
	       curl_setopt($ch, CURLOPT_URL, $url);
	       curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	       //curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	       curl_setopt($ch, CURLOPT_HEADER, 1);
	       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	       $serverOutput = curl_exec($ch);
	       $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	       $header = substr($serverOutput, 0, $header_size);
	       $body = substr($serverOutput, $header_size);
	       curl_close($ch);
	       return $body;
    	}
    }


}
?>