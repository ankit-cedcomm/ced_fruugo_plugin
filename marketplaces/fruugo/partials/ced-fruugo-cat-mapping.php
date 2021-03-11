<div class="ced_fruugo_cat_mapping ced_fruugo_toggle_wrapper">
	<div class="ced_fruugo_toggle_section">
		<div class="ced_fruugo_toggle">
			<h2><?php _e('fruugo Category','ced-fruugo');?></h2>
		</div>
		
		<div class="ced_fruugo_cat_activate_ul ced_fruugo_toggle_div ced_scr_wrapper2">
			<!-- <p class="ced_fruugo_button_right">
				<button id="ced_fruugo_fetch_cat" class="button button-ced_fruggo"><?php _e('Fetch Categories','ced-fruugo');?></button>
			</p> -->
		<?php 
		$folderName = CED_FRUUGO_DIRPATH.'marketplaces/fruugo/lib/json/';
		$catFirstLevelFile = $folderName.'category.json';
		if(file_exists($catFirstLevelFile)){
			$catFirstLevel = file_get_contents($catFirstLevelFile);
			$catFirstLevel = json_decode($catFirstLevel,true);
			//print_r($catFirstLevel);die;
			// $catFirstLevel = isset( $catFirstLevel['results'] ) ? $catFirstLevel['results'] : array();
			if( is_array( $catFirstLevel ) && !empty( $catFirstLevel ) )
			{
				$breakPoint = floor(count($catFirstLevel)/3);
				$counter = 0;
				echo '<ul class="ced_fruugo_cat_ul ced_fruugo_1lvl">';
				echo '<h1>'.__('Root Categories','ced-fruugo').'</h1>';
				if(is_array($catFirstLevel))
				{
					foreach ($catFirstLevel as $key => $value) {
						$catFirstLevl[] = $value['level1'];
						$catFirstLevl = array_unique($catFirstLevl);
					}
					// echo "<pre>";print_r($catFirstLevl);die;
					foreach ($catFirstLevl as $key => $category) 
					{
						// print_r($category);die;
						$catName = $category;
						if( isset( $category['children'] ) && intval( $category['children'] ) <= 0 )
						{
							$checkbox = '<input type="checkbox" class="ced_fruugo_cat_select" id="'.$category['id'].'" name="'.$category['id'].'" value="'.$category['id'].'"  >';
							$span = '<label for = "'.$category['id'].'" class="ced_fruugo_lab">'.$catName.'</label>';
						}else{
							$checkbox = "";
							$span = '<label class="ced_fruugo_expand_fruugocat " data-parentCatName="'.$category.'" data-catName="'.$category.'" data-catId="'.$key.'" data-catLevel = "1"> '.$catName.'> <img class="ced_fruugo_category_loader" src="'.CED_FRUUGO_URL.'admin/images/loading.gif" width="20px" height="20px"> </label>';
						}
						echo '<li>'.$checkbox.$span.'</li>';
					}
					echo '</ul>';
					echo '<ul class="ced_fruugo_cat_ul ced_fruugo_2lvl"></ul>';
					echo '<ul class="ced_fruugo_cat_ul ced_fruugo_3lvl"></ul>';
					echo '<ul class="ced_fruugo_cat_ul ced_fruugo_4lvl"></ul>';
					echo '<ul class="ced_fruugo_cat_ul ced_fruugo_5lvl"></ul>';
					echo '<ul class="ced_fruugo_cat_ul ced_fruugo_6lvl"></ul>';
					echo '<ul class="ced_fruugo_cat_ul ced_fruugo_7lvl"></ul>';
				}	
			}
			else
			{
				?>
				<div>
					<span><?php _e( 'Please fetch the Categories', 'ced-fruugo' ); ?></span>
				</div>
				<?php
			}
		}
		else
		{
			?>
			<div>
				<span><?php _e( 'Please fetch the Categories', 'ced-fruugo' ); ?></span>
			</div>
			<?php	
		}
					
		?>
		</div>
	</div>
</div>