<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * product meta related functionalities.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce fruugo Integration
 * @subpackage Woocommerce fruugo Integration/admin/helper
 */

if( !class_exists( 'CED_FRUUGO_productMeta' ) ) :

/**
 * product meta fields get/set functionalities
 * for each framework.
*
*
* @since      1.0.0
* @package    Woocommerce fruugo Integration
* @subpackage Woocommerce fruugo Integration/admin/helper
* @author     CedCommerce <cedcommerce.com>
*/
class CED_FRUUGO_productMeta{
	
	/**
	 * The Instace of CED_FRUUGO_productMeta.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      $_instance   The Instance of CED_FRUUGO_productMeta class.
	 */
	private static $_instance;
	
	/**
	 * CED_FRUUGO_productMeta Instance.
	 *
	 * Ensures only one instance of CED_FRUUGO_productMeta is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return CED_FRUUGO_productMeta instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * get conditional price.
	 * 
	 * @since 1.0.0
	 */
	public function get_conditional_price($ProId,$marketplace){
		
		if($ProId){
			$priceCondition = get_post_meta();
		}
	}
}
endif;