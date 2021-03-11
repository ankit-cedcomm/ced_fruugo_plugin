<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Orders listing class.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce fruugo Integration
 * @subpackage Woocommerce fruugo Integration/admin/helper
 */

if( !class_exists( 'CED_FRUUGO_order_lister' ) ) :

/**
 * order listing page.
*
*
* @since      1.0.0
* @package    Woocommerce fruugo Integration
* @subpackage Woocommerce fruugo Integration/admin/helper
* @author     CedCommerce <cedcommerce.com>
*/
class CED_FRUUGO_order_lister extends WP_List_Table {

	/**
	 * order data query response.
	 *
	 * @since 1.0.0
	 */
	private $_loop;
	
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		global $status, $page;
		
		parent::__construct( array(
				'singular'  => 'ced_fruugo_mo',
				'plural'    => 'ced_fruugo_mos',
				'ajax'      => true
		) );
	}

	/**
	 * columns.
	 *
	 * @since 1.0.0
	 * @see WP_List_Table::get_columns()
	 */
	public function get_columns(){
		$columns = array(
				//'cb'        => '<input type="checkbox" />',
				'id'     => __( 'Woo Order #', 'ced-fruugo' ),
				'products'    => __( 'Product #', 'ced-fruugo' ),
				'marketplaceoid'  => __( 'Fruugo Order #', 'ced-fruugo' ),
				'status'    => __( 'Status', 'ced-fruugo' ),
				'action'  => __( 'Action', 'ced-fruugo' ),
		);
		return $columns;
	}
	
	/**
	 * supported bulk actions for managing orders.
	 *
	 * @since 1.0.0
	 * @see WP_List_Table::get_bulk_actions()
	 */
	public function top_actions( ){
			
		//$marketplaces = $this->get_active_marketplaces();
		$marketplaces = fruugoget_enabled_marketplaces();
			
		if(!count($marketplaces)) {
			_e('<h3>Please configure fruugo first.</h3>','ced-fruugo');
			return;
		}
			
		
		echo '<select name="umb_slctd_marketplace" id="bulk_action_marketplace" class="umb_eby_select_fruugo_for_order_fetch">"';
		echo '<option value="all">' . __( 'Marketplace', 'ced-fruugo' ) . "</option>\n";
		foreach ($marketplaces as $marketplace):
		echo "\t" . '<option value="' . $marketplace . '" selected>' . $marketplace . "</option>\n";
		endforeach;
		echo "</select>\n";
			
		submit_button( __( 'Fetch Orders', 'ced-fruugo' ), 'action', '', false, array( 'id' => "ced_fruugo_fetch_order", 'name' => 'umb_fetch_fruugo_order' ) );
		do_action('ced_fruugo_after_fetch_order');
		//echo "\n";
	}
	
	// /**
	//  * get active marketplaces.
	//  *
	//  * @since 1.0.0
	//  * @return array
	//  */
	// function get_active_marketplaces(){
	
	// 	$activated_marketplaces = is_array(get_option('ced_fruugo_activated_marketplaces',true)) ? get_option('ced_fruugo_activated_marketplaces',true) : array();
	
	// 	return $activated_marketplaces;
	// }
	
	/**
	 * preparing the table data for listing orders
	 *
	 * @since 1.0.0
	 * @see WP_List_Table::prepare_items()
	 */
	function prepare_items() {
		global $wpdb;

		$per_page = 10;
		$total_items = 0;
		
		$UmbOrders = $wpdb->get_results($wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`='_is_ced_fruugo_order' AND `meta_value`='%s' group by `post_id`", 1 ),'ARRAY_A' );
		/* $UmbOrders = get_posts( array(
			        'numberposts' => -1,
			        'meta_key'    => '_is_umb_order',
			        'meta_value'  => '1',
			        'post_type'   => wc_get_order_types(),
			        'post_status' => array_keys( wc_get_order_statuses() ),
			    ) ); */
		$post_ids = array();
		if(is_array($UmbOrders)){
			
			$total_items = count($UmbOrders);
		}
		
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
	
		$this->_column_headers = array($columns, $hidden, $sortable);
	
		$current_page = $this->get_pagenum();
	
		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil($total_items/$per_page)
		) );
	}

	/**
	 * items available for listing.
	 *
	 * @since 1.0.0
	 * @see WP_List_Table::has_items()
	 */
	public function has_items(){
		
		$current_page = $this->get_pagenum();
	
		$args = array(
				 'post_type'   => wc_get_order_types(),
			     'post_status' => array_keys( wc_get_order_statuses() ),
				'paged'				=> $current_page,
				'posts_per_page'    => '10',
				'meta_query'    => array(
						array(
								'key'       => '_is_ced_fruugo_order',
								'value'     => array('1'),
								'compare'   => 'IN',
						)
				)
		);
	
		$loop = new WP_Query($args);
		$this->_loop = $loop;
	
		if($loop->have_posts()){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * displaying the marketplace listable products.
	 * 
	 * @since 1.0.0
	 * @see WP_List_Table::display_rows()
	 */
	public function display_rows(){
		
		if( $this->has_items() ){
			$loop = $this->_loop;
			if($loop->have_posts()){
				while($loop->have_posts()){
					$loop->the_post();
					$this->get_order_row_html($loop->post);
				}
			}
		}
	}
	
	/**
	 * order row html.
	 * 
	 * @since 1.0.0
	 */
	public function get_order_row_html($order){
		$orderId = $order->ID;
		$order = wc_get_order($orderId);
		if(is_wp_error($order))
			return;
		
		$columns = $this->get_columns();
		echo '<tr id="post-'.$orderId.'" class="ced_fruugo_inline_edit">';
		foreach($columns as $column_id => $column_name){
			$this->print_column_data($column_id, $order);
		}
		echo '</tr>';
	}
	
	/**
	 * print column data.
	 * 
	 * @since 1.0.0
	 */
	public function print_column_data($column_name, $order){
		
		if( WC()->version < '3.0.0' )
		{
			$order_status = $order->post_status;
			$edit_link = get_edit_post_link( $order->id );
			$framewor_name = get_post_meta($order->id,'_umb_marketplace',true);
			$refund_link = add_query_arg(array('sub-section' => 'refund','framework'=>$framewor_name, 'orderid' => $order->id),$_SERVER["REQUEST_URI"]);
			$classes = "$column_name column-$column_name";
			$data = 'data-colname="'.$column_name.'"';
			
			switch ( $column_name ) {
					
				case 'cb':
					echo '<td class="'.$classes.'" '.$data.'>';
					if ( current_user_can( 'edit_post', $order->id ) ):
					echo '<input id="cb-select-'.$order->id.'" type="checkbox" name="post[]" value="'.$order->ID.'" />';
					echo '<div class="locked-indicator"></div>';
					endif;
					echo '</td>';
					break;
				case 'id':
					echo '<td class="'.$classes.'" '.$data.'>';
						echo '<a href="'.$edit_link.'">'.$order->id.'</a>';
					echo '</td>';
					break;
				case 'products':
					echo '<td class="'.$classes.'" '.$data.'>';
					$items = $order->get_items();
					if(is_array($items)){
						foreach($items as $item_id => $item_info){
							if( WC()->version < '3.0.0' ){

								$item_name = isset($item_info['name']) ? esc_attr($item_info['name']) : '';
								$item_meta = isset($item_info['item_meta']) ? $item_info['item_meta'] : array();
								if(is_array($item_meta)){
									$qty = isset($item_meta['_qty'][0]) ? intval($item_meta['_qty'][0]) : 1;
								}
								if(is_null($item_name))
									$item_name = $item_id;
								
								echo '<p>'.$item_name.'('.$qty.')</p>';
							}else{
								$item_meta = $item_info->get_data();
								$item_name = '';
								$qty = 1;
								if(is_array($item_meta)){
									$item_name = $item_meta['name'];
									$qty = $item_meta['quantity'];
									if(is_null($item_name)){
										$item_name = $item_meta['id'];
									}
								}
								echo '<p>'.$item_name.'('.$qty.')</p>';
							}
						}
					}
					echo '</td>';
					break;
				case 'marketplaceoid':
					echo '<td class="'.$classes.'" '.$data.'>';
						$oid = get_post_meta($order->id,'_ced_fruugo_order_id',true);
						echo '<span>'.$oid.'</span>';
					echo '</td>';
					break;
				case 'status':
					echo '<td class="'.$classes.'" '.$data.'>';
					$status = get_post_meta($order->id,'_fruugo_umb_order_status',true);
					if( $status == 'Fetched' )
						echo '<span>Fetched</span>';
					else
						echo '<span>'.$status.'</span>';
					echo '</td>';
					break;
				case 'action':
					echo '<td class="'.$classes.'" '.$data.'>';
					echo '<a href="'.$edit_link.'">'.__('edit','ced-fruugo').'</a>';
					echo '</td>';
					break;
				default :
					echo '<td class="'.$classes.'" '.$data.'>';
					echo $column_name;
					echo '</td>';
					break;
			}
		}
		else{
			$order_status = $order->get_status();
			$edit_link = get_edit_post_link( $order->get_id() );
			$framewor_name = get_post_meta($order->get_id(),'_umb_marketplace',true);
			$refund_link = add_query_arg(array('sub-section' => 'refund','framework'=>$framewor_name, 'orderid' => $order->get_id()),$_SERVER["REQUEST_URI"]);

			$classes = "$column_name column-$column_name";
			$data = 'data-colname="'.$column_name.'"';
			
			switch ( $column_name ) {
					
				case 'cb':
					echo '<td class="'.$classes.'" '.$data.'>';
					if ( current_user_can( 'edit_post', $order->get_id() ) ):
					echo '<input id="cb-select-'.$order->get_id().'" type="checkbox" name="post[]" value="'.$order->get_id().'" />';
					echo '<div class="locked-indicator"></div>';
					endif;
					echo '</td>';
					break;
				case 'id':
					echo '<td class="'.$classes.'" '.$data.'>';
						echo '<a href="'.$edit_link.'">'.$order->get_id().'</a>';
					echo '</td>';
					break;
				case 'products':
					echo '<td class="'.$classes.'" '.$data.'>';
					$items = $order->get_items();
					if(is_array($items)){
						foreach($items as $item_id => $item_info){
							if( WC()->version < '3.0.0' ){

								$item_name = isset($item_info['name']) ? esc_attr($item_info['name']) : '';
								$item_meta = isset($item_info['item_meta']) ? $item_info['item_meta'] : array();
								if(is_array($item_meta)){
									$qty = isset($item_meta['_qty'][0]) ? intval($item_meta['_qty'][0]) : 1;
								}
								if(is_null($item_name))
									$item_name = $item_id;
								
								echo '<p>'.$item_name.'('.$qty.')</p>';
							}else{
								$item_meta = $item_info->get_data();
								$item_name = '';
								$qty = 1;
								if(is_array($item_meta)){
									$item_name = $item_meta['name'];
									$qty = $item_meta['quantity'];
									if(is_null($item_name)){
										$item_name = $item_meta['id'];
									}
								}
								echo '<p>'.$item_name.'('.$qty.')</p>';
							}
						}
					}
					echo '</td>';
					break;
				case 'marketplaceoid':
					echo '<td class="'.$classes.'" '.$data.'>';
						$oid = get_post_meta($order->get_id(),'_ced_fruugo_order_id',true);
						echo '<span>'.$oid.'</span>';
					echo '</td>';
					break;
				case 'status':
					echo '<td class="'.$classes.'" '.$data.'>';
					$status = get_post_meta($order->get_id(),'_fruugo_umb_order_status',true);
					echo '<span>'.$status.'</span>';
					echo '</td>';
					break;
				case 'action':
					echo '<td class="'.$classes.'" '.$data.'>';
					echo '<a href="'.$edit_link.'">'.__('edit','ced-fruugo').'</a>';
					echo '</td>';
					break;
				default :
					echo '<td class="'.$classes.'" '.$data.'>';
					echo $column_name;
					echo '</td>';
					break;
			}
		}



	}
}
endif;