<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if(!class_exists('CED_FRUUGO_product_fields')){
	
	require_once CED_FRUUGO_DIRPATH.'admin/helper/class-product-fields.php';
}

$product_fields = CED_FRUUGO_product_fields::get_instance();
$required_fields = $product_fields->get_custom_fields('required',false); 
$fieldIDSArray=array();
foreach($required_fields as $number => $fieldInfo){
	$FieldId=isset($fieldInfo['id'])?$fieldInfo['id']:'';
	if(in_array($FieldId,$fieldIDSArray)){
		unset($required_fields[$number]);
	}else{
		$fieldIDSArray[]=$FieldId;
	}
}
?>
<form method="get">
	<table style="display: none">
		<tbody id="inlineedit">
		<?php
		$hclass = 'post';
		$bulk = 0;
		while ( $bulk < 2 ) { ?>

		<tr id="<?php echo $bulk ? 'bulk-edit' : 'inline-edit'; ?>" class="inline-edit-row inline-edit-row-<?php echo "$hclass inline-edit-" . $screen->post_type;
			echo $bulk ? " bulk-edit-row bulk-edit-row-$hclass bulk-edit-{$screen->post_type}" : " quick-edit-row quick-edit-row-$hclass inline-edit-{$screen->post_type}";
		?>" style="display: none"><td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">

					<fieldset class="inline-edit-col-left">
						<div id="ced_fruugo_quick_edit" class="inline-edit-col">
							<div class="ced_fruugo_profile_field">
								<label>
									<span class="title"><?php _e( 'SKU', 'ced-fruugo' ); ?></span>
								</label>
								<input type="text" name="_sku" value="">
							</div>
							<?php $product_fields->custom_field_html($required_fields);?>
							<?php do_action('ced_fruugo_quick_edit');?>
							<input type="hidden" name="ced_fruugo_quick_edit" value="1" />
							<input type="hidden" name="ced_fruugo_quick_edit_nonce" value="<?php echo wp_create_nonce( 'ced_fruugo_quick_edit_nonce' ); ?>" />
							<br class="clear" />
						</div>
					</fieldset>	
					<p class="submit inline-edit-save">
						<button type="button" class="button-secondary cancel alignleft"><?php _e( 'Cancel', 'ced-fruugo' ); ?></button>
						<?php if ( ! $bulk ) {
							wp_nonce_field( 'inlineeditnonce', '_inline_edit', false );
							?>
							<button type="button" class="button-primary save alignright"><?php _e( 'Update', 'ced-fruugo' ); ?></button>
							<span class="spinner"></span>
						<?php } else {
							submit_button( __( 'Update', 'ced-fruugo' ), 'button-primary alignright', 'bulk_edit', false );
						} ?>
						<input type="hidden" name="post_view" value="<?php echo esc_attr( $m ); ?>" />
						<input type="hidden" name="screen" value="<?php echo esc_attr( $screen->id ); ?>" />
						<?php if ( ! $bulk && ! post_type_supports( 'product', 'author' ) ) { ?>
							<input type="hidden" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>" />
						<?php } ?>
						<span class="error" style="display:none"></span>
						<br class="clear" />
					</p>
					<?php
						$bulk++;
					}
					?>
				</td>
			</tr>
		</tbody>
	</table>
</form>