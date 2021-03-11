<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$isVariableProductCase = false;
if( isset($variation_data) ) {
	$isVariableProductCase = true;
}

$required_fields = $this->get_custom_fields('required',false);
$extra_fields = $this->get_custom_fields('extra_fields',false);
$framework_fields = $this->get_custom_fields('framework_specific',false);
?>
<div id="ced_fruugo_fields" class="panel woocommerce_options_panel">
	<div id="ced_fruugo_accordian">
	<!-- --------------------------Required fields-------------------------------- -->
		<?php if(count($required_fields)) : ?>
		<div class="ced_fruugo_panel">
			<div class="ced_fruugo_panel_heading">
				<h4><?php _e('Required Fields','ced-fruugo') ?></h4>
			</div>
			<div class="ced_fruugo_collapse">
				<div class="options_group ced_fruugo_label_data">
				<?php 

				$requiredInAnyCase = array('_umb_fruugo_id_type','_umb_fruugo_id_val','_umb_fruugo_brand');
							
				if( $isVariableProductCase ) {  // adding variation title field only in case of variation product
					$addedFieldForVariableProTitle = array(
						'type' => '_text_input',
						'id'   => '_umb_fruugo_variation_title',
						'fields' => array(
							'id'   => '_umb_fruugo_variation_title',
							'label' => __('Variation Title', 'ced-fruugo'),
							'desc_tip' => '',
							'description' => __('This Will Be Your Variation Name In Your MarketPlace.','ced-fruugo')
						)	
					);
					array_unshift($required_fields, $addedFieldForVariableProTitle);
				}

				foreach ($required_fields as $field_array):
					if(isset($field_array['id']) && isset($field_array['type']) && isset($field_array['fields'])){
						$type = esc_attr($field_array['type']);
						$fields = is_array($field_array['fields']) ? $field_array['fields'] : array();
						$id = isset($fields['id']) ? $fields['id'] : isset($field_array['id']) ? $field_array['id'] : '';
						$label = isset($fields['label']) ? esc_attr($fields['label']) : '';
						
						if($type=='_umb_fruugo_select'){
							global $post;
							if( $isVariableProductCase ) {
								$optionValue = get_post_meta($variation->ID,$id,true);
							}
							else {
								$optionValue = get_post_meta($post->ID,$id,true);
							}
							
							$options = isset($fields['options']) ? $fields['options'] : array();
							$optionsHtml = '';
							$optionsHtml .= '<option value="null">'.__('fruugo Subcategory','ced-fruugo').'</option>';
							if(is_array($options)){
								foreach($options as $industry => $subcats){
									
									if(is_array($subcats)){
										$optionsHtml .= '<option value="null" class="umb_parent" disabled>'.$industry.'</option>';
										foreach($subcats as $Sid => $name){
											
											$optionsHtml .= '<option value="'.$Sid.'" "'.selected($optionValue,$Sid,false).'">'.$name.'</option>';
										}
									}
								}
							}

							if( $isVariableProductCase ) {
								echo '<p class="form-field '.$id.'">';
									echo '<label for="'.$id.'">'.$label.'</label>';
									echo '<select name="'.$id.'['.$loop.']" id="'.$id.'">';
										echo $optionsHtml;
									echo '</select>';
								echo '</p>';
							}
							else{
								echo '<p class="form-field '.$id.'">';
									echo '<label for="'.$id.'">'.$label.'</label>';
									echo '<select name="'.$id.'" id="'.$id.'">';
										echo $optionsHtml;
									echo '</select>';
								echo '</p>';
							}
							
						}else{
							if( $isVariableProductCase ) {
								$this->umb_render_variation_html($field_array,$loop,$variation);
							}
							else {
								
								if(in_array($fields['id'], $requiredInAnyCase)) {
									$nameToRender = ucfirst($fields['label']);
									$nameToRender .= '<span class="ced_fruugo_wal_required"> [ '.__("Required", "ced-fruugo").' ]</span>';
									$fields['label'] = $nameToRender;
								}
								
								$function_name = "woocommerce_wp$type";
								if(function_exists($function_name))
									$function_name($fields);
							}
							
						}
					}
				endforeach;
				?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	<!-- --------------------------End of Required fields-------------------------------- -->
	<!-- --------------------------Framework Specific fields-------------------------------- -->	
		<?php if(count($framework_fields)) :?>
		<div class="ced_fruugo_panel">
			<div class="ced_fruugo_panel_heading">
				<h4><?php _e('Framework Specific Fields','ced-fruugo'); ?></h4>
			</div>
			<div class="ced_fruugo_collapse">
			<?php foreach($framework_fields as $fname=> $ffields_details): ?>
				<?php if(count($ffields_details)) :?>
				<div class="ced_fruugo_sub_accordion">
					<div class="ced_fruugo_sub_panel">
						<div class="ced_fruugo_sub_panel_heading">	
							<h4><?php echo esc_attr($fname); ?></h4>
						</div>
						<div class="ced_fruugo_sub_collapse">
						<?php 
							foreach($ffields_details as $ffields_array){
								if(isset($ffields_array['id']) && isset($ffields_array['type']) && isset($ffields_array['fields'])){
									$ftype = esc_attr($ffields_array['type']);
									$ffields = is_array($ffields_array['fields']) ? $ffields_array['fields'] : array();
									$ffunction_name = "woocommerce_wp$ftype";

									if( $isVariableProductCase ) {
										$this->umb_render_variation_html($ffields_array,$loop,$variation);
									}
									else {
										if(function_exists($ffunction_name))
										$ffunction_name($ffields);
									}
								}
							}
						?>
						</div>
					</div>
				</div>
				<?php endif;?>
			<?php endforeach;?>
			</div>
		</div>
		<?php endif;?>
		<!-- -------------------------- End of framework specific fields-------------------------------- -->
		<!-- --------------------------Extra fields-------------------------------- -->
		<?php if(count($extra_fields)) :?>
		<div class="ced_fruugo_panel">
			<div class="ced_fruugo_panel_heading">
				<h4><?php _e('Recommended Fields','ced-fruugo') ?></h4>
			</div>
			<div class="ced_fruugo_collapse">
				<div class="options_group ced_fruugo_label_data">
					<?php 
					foreach($extra_fields as $efield_array):
						if(isset($efield_array['id']) && isset($efield_array['type']) && isset($efield_array['fields'])){
							$etype = esc_attr($efield_array['type']);
							$efields = is_array($efield_array['fields']) ? $efield_array['fields'] : array();
							$efunction_name = "woocommerce_wp$etype";
							
							if( $isVariableProductCase ) {
								$this->umb_render_variation_html($efield_array,$loop,$variation);
							}
							else {
								if(function_exists($efunction_name)) {
									$efunction_name($efields);
								}
								else{
									switch($etype){
										case 'lwh' :
											$id = esc_attr($efield_array['id']);
											$label = isset($efields['label']) ? esc_attr( $efields['label'] ) : '';
											$desc_tip = isset($efields['desc_tip']) ? $efields['desc_tip'] : 0;
											$desc = isset($efields['description']) ? $efields['description'] : 0;
											?><p class="form-field dimensions_field">
												<label for="<?php $id; ?>"><?php echo $label . ' (' . get_option( 'woocommerce_dimension_unit' ) . ')'; ?></label>
												<span class="wrap">
													<input id="<?php $id; ?>" placeholder="<?php esc_attr_e( 'Length', 'ced-fruugo' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="<?php echo $id ?>_length" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $post->ID, $id.'_length', true ) ) ); ?>" />
													<input placeholder="<?php esc_attr_e( 'Width', 'ced-fruugo' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="<?php echo $id ?>_width" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $post->ID, $id.'_width', true ) ) ); ?>" />
													<input placeholder="<?php esc_attr_e( 'Height', 'ced-fruugo' ); ?>" class="input-text wc_input_decimal last" size="6" type="text" name="<?php echo  $id ?>_height" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $post->ID, $id.'_height', true ) ) ); ?>" />
												</span>
												<?php if(isset($desc_tip)): echo wc_help_tip($desc); endif;?>
											</p><?php
											break;
									}
								}
							}
						}
					endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif;?>
		<!-- --------------------------End of extra fields-------------------------------- -->
	</div>
</div>