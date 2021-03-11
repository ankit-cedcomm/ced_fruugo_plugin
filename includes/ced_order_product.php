<?php
require_once ('../../../../wp-blog-header.php');

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
/**
 * Cron to fetch order and auto acknowledge
 *
 * @class    Class_CED_fruugo_Cron
 * @version  1.0.0
 * @category Class
 * @author   CedCommerce
 */

class Class_CED_fruugo_Cron{

	public function __construct(){

		do_action('ced_fruugo_product_order');
	}
}
$marketplace_cron_obj =	new Class_CED_fruugo_Cron();
?>