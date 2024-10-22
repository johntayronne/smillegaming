<?php
// ---------------------------------------------------
// Activate
// ---------------------------------------------------
function wpggma_activate(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !current_user_can('manage_options') ) 
		return;
	
	if(!get_option('wpggma_Activate')){
		update_option('wpggma_Activate', 1);
		echo 1;
	}
	
	die();
	
}
add_action('wp_ajax_wpggma_activate', 'wpggma_activate');


// ---------------------------------------------------
// Check Ref
// ---------------------------------------------------
function wpggma_check_ref(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['data']) || !current_user_can('manage_options') ) 
		return;
	
	$link_id = (int)$_POST['data'];
	if(get_post_type($link_id) != 'wpggma_links')
		return;
	
	$ref = wpggma_get_ref_link($link_id, true);
	
	$scrap = wpggma_scrap($ref, array('User-Agent' => 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)'));
	$headers = $scrap->headers;

	if($headers[0] != 'HTTP/1.1 302 Moved Temporarily' || strpos($headers['Location'], '&reflink-not-found') !== false){
		wp_delete_post($link_id);
		
	}else{
		echo 1;
		
	}

	die();
	
}
add_action('wp_ajax_wpggma_check_ref', 'wpggma_check_ref');


// ---------------------------------------------------
// Search Link
// ---------------------------------------------------
function wpggma_search(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['data']) || !current_user_can('manage_options') ) 
		return;
	
	parse_str($_POST['data'], $data);
	
	$search = array();
	$result = $found = $col = false;
	$search['query'] = 		trim($data['wpggma_search_link']);
	$search['platform'] = 	$data['wpggma_search_link_platform'];
	$search['sort'] = 		$data['wpggma_search_link_sort'];
	$search['rows'] = 		$data['wpggma_search_link_rows'];
	
	// Search: Direct
	if(strpos($search['query'], 'direct:https://www.g2a.com/') === 0){
		$search['type'] = 'direct';
		
		$url = str_replace('direct:', '', $search['query'] . '?___store=english');		
		$link = array();
		$link = wpggma_scrap_link_type($url);
		
		if(!empty($link['name'])){
			$found = 1;
			$col = 6;
			$result[] = $link;
		}
		
		
	// Search: Game
	}else{
		$search['type'] = 'game';
		
		$url = 'https://www.g2a.com/lucene/search/filter?minPrice=0.00&maxPrice=640.00&cn=&kr=&stock=all&event=&platform='.$search['platform'].'&search='.urlencode($search['query']).'&genre=0&cat=0&sortOrder='.$search['sort'].'&start=0&rows='.$search['rows'].'&steam_app_id=&steam_category=&steam_prod_type=&includeOutOfStock=false&includeFreeGames=false';
		$scrap = wpggma_scrap($url);
		$json = json_decode($scrap->raw_body, true);
		$result = $json['docs'];
		$found = $json['numFound'];
		$col = 6;
		
	}
	
	if($found == 0){ ?>
	<div style="padding:20px; background:#fcfcfc;">
		<div class="text-center">
			Sorry, nothing found. Try an another search!
		</div>
	</div>
	
	<?php }elseif(!empty($found)){ ?>
		<div style="padding:10px;">
		<table class="postbox widefat striped" style="margin-top:0;">
			<!--
			<thead>
				<tr>
					<th class="manage-column">
					</th>
					<th class="manage-column">
						<span>Title</span>
					</th>
					<th class="manage-column">
						<div class="text-center"><span>Action</span></div>
					</th>
				</tr>
			</thead>
			-->
			<tbody>
				
				<?php foreach($result as $link){
				
				if($search['type'] == 'game'){
					$link['type'] = 'game';
					$link['url'] = 'https://www.g2a.com' . $link['slug'];
					$link['g2id'] = $link['id'];
					$link['image'] = $link['smallImage'];
					$link['price'] = number_format((float)$link['minPrice'], 2, '.', '');
				}
				
				$already_in = false;
				$check_args = array(
					'post_type'   		=> 'wpggma_links',
					'posts_per_page' 	=> 1,
					'meta_key'			=> 'wpggma_link_url',
					'meta_value'		=> $link['url'],
					'meta_compare'		=> '=',
					'fields'			=> 'ids'
				);
				$check_query = 			new WP_Query($check_args);
				$check_query_posts = 	$check_query->posts;
				$check_id = 			$check_query_posts[0];
				
				if(!empty($check_id))
					$already_in = true;
				?>
				
					<tr style="border-bottom:1px solid #eee;" valign="middle">
						<td style="width:45px; min-width:45px;">
							<img src="<?php echo wpggma_image($link['image']); ?>" class="img-responsive" style="max-width:45px;<?php echo ($already_in) ? '-webkit-filter: grayscale(100%);filter: grayscale(100%); opacity: 0.5; filter: alpha(opacity=50);' : ''; ?>" />
						</td>
						<td style="vertical-align:middle;">	
							<div>
								<strong>
								<a href="<?php echo $link['url']; ?>" target="_blank" <?php echo ($already_in) ? 'style="color:#ccc;"' : ''; ?>>
									<?php echo $link['name']; ?>
								</a>
								</strong> 
							</div>
							
							<div <?php echo ($already_in) ? 'style="color:#ccc;"' : ''; ?>>
								<?php if($already_in){ ?>
									Already Added
								<?php }else{ ?>
									<?php if($link['price'] && $link['price'] != '-'){ ?>
										<span><?php echo $link['price']; ?>€</span>
									<?php } ?>
								<?php } ?>
							</div>
							
						</td>
						<td style="vertical-align:middle; width:200px;" class="text-center">
							<form class="wpggma_add_link">
								<input type="hidden" name="wpggma_add_link_type" 	value="<?php echo $link['type']; ?>" />
								<input type="hidden" name="wpggma_add_link_g2aid" 	value="<?php echo $link['g2id']; ?>" />
								<input type="hidden" name="wpggma_add_link_name" 	value="<?php echo $link['name']; ?>" />
								<input type="hidden" name="wpggma_add_link_image" 	value="<?php echo $link['image']; ?>" />
								<input type="hidden" name="wpggma_add_link_price" 	value="<?php echo $link['price']; ?>" />
								<input type="hidden" name="wpggma_add_link_url" 	value="<?php echo $link['url']; ?>" />
							
								<?php if(!$already_in){ ?>
									<div class="wpggma_add_link_wrapper">
										<button class="button"><i class="dashicons dashicons-v-middle dashicons-plus"></i> Add Link</button>
									</div>
								<?php } ?>
							</form>
						</td>
					</tr>

				<?php } ?>
			</tbody>
		</table>
		</div>
		
	<?php }else{ ?>
		<div style="padding:20px; background:#fcfcfc;">
			<div class="text-center">
				Looks like G2A is busy at the moment. Try again!
			</div>
		</div>
	<?php }
	die();
	
}
add_action('wp_ajax_wpggma_search', 'wpggma_search');


// ---------------------------------------------------
// Add Link
// ---------------------------------------------------
function wpggma_add_link(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['data']) || !current_user_can('manage_options') ) 
		return;
	
	parse_str($_POST['data'], $data);
	
	foreach($data as $key => $value){
		$key = str_replace('wpggma_add_link_', '', $key);
		$link[$key] = $value;
	}
	
	$link_insert = array(
						'post_title'    => 		$link['name'],
						'post_content'  => 		'',
						'post_status'   => 		'publish',
						'post_type'     => 		'wpggma_links'
	);
	
	$wp_link_id = wp_insert_post($link_insert);
	$link_slug = wpggma_get_ref_link($wp_link_id);
	$link_slug = substr( $link_slug, strlen(esc_attr(get_option('wpggma_RewriteGameSlug')) . '-') );
	$link_slug = str_replace('-'.$wp_link_id, '', $link_slug);
	
	wp_update_post(array(
		'ID' => $wp_link_id,
		'post_name' => $link_slug
	));
	
	update_post_meta( $wp_link_id, 'wpggma_link_type', 			$link['type'] );
	update_post_meta( $wp_link_id, 'wpggma_link_g2aid', 		$link['g2aid'] );
	update_post_meta( $wp_link_id, 'wpggma_link_url', 			$link['url'] );
	update_post_meta( $wp_link_id, 'wpggma_link_image', 		$link['image'] );
	update_post_meta( $wp_link_id, 'wpggma_link_price', 		$link['price'] );
	update_post_meta( $wp_link_id, 'wpggma_link_sync_time', 	time() );
	update_post_meta( $wp_link_id, 'wpggma_link_sync_status', 	1 );
	
	echo wpggma_table_row($wp_link_id, $link);
	
	die();
	
}
add_action('wp_ajax_wpggma_add_link', 'wpggma_add_link');


// ---------------------------------------------------
// Delete Link
// ---------------------------------------------------
function wpggma_delete_link(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['data']) || !current_user_can('manage_options') ) 
		return;
	
	$link_id = (int)$_POST['data'];
	if(get_post_type($link_id) != 'wpggma_links')
		return;
	
	wp_delete_post($link_id);
	echo 1;
	die();
	
}
add_action('wp_ajax_wpggma_delete_link', 'wpggma_delete_link');


// ---------------------------------------------------
// ReSync. Link
// ---------------------------------------------------
function wpggma_resync_link(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['data']) || !current_user_can('manage_options') ) 
		return;
	
	$wp_link_id = (int)$_POST['data'];
	if(get_post_type($wp_link_id) != 'wpggma_links')
		return;
	
	$link_ref = wpggma_get_ref_link($wp_link_id, true);
	$scrap = wpggma_scrap($link_ref, array('User-Agent' => 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)'));
	$headers = $scrap->headers;
	
	$link = array();
	$link['name'] = 	get_the_title($wp_link_id);
	$link['url'] = 		get_post_meta($wp_link_id, 'wpggma_link_url', true);
	$link['image'] = 	get_post_meta($wp_link_id, 'wpggma_link_image', true);
	$link['price'] = 	get_post_meta($wp_link_id, 'wpggma_link_price', true);

	if($headers[0] != 'HTTP/1.1 302 Moved Temporarily' || strpos($headers['Location'], '&reflink-not-found') !== false){
		
		update_post_meta( $wp_link_id, 'wpggma_link_sync_time', 	time() );
		update_post_meta( $wp_link_id, 'wpggma_link_sync_status', 	0 );
	
		$last = '<div class="wpggma_search_added"><div class="text-danger"><i class="dashicons dashicons-v-bottom dashicons-flag"></i> Sync. Failed</div><div><p>G2A REF Link doesn\'t exists!</p></div></div>';
		
		echo wpggma_table_row($wp_link_id, $link, $last);
		die();
		
	}
	
	$link_price = 	get_post_meta($wp_link_id, 'wpggma_link_price', true);
	$link_url = 	get_post_meta($wp_link_id, 'wpggma_link_url', true);
	
	$link = array();
	$link = wpggma_scrap_link_type($link_url);
	
	$price_updated = false;
	if($link['price'] != $link_price){
		update_post_meta( $wp_link_id, 'wpggma_link_price', 	$link['price'] );
		$price_updated = true;
	}
	
	update_post_meta( $wp_link_id, 'wpggma_link_sync_time', 	time() );
	update_post_meta( $wp_link_id, 'wpggma_link_sync_status', 	1 );
	
	$last = '<div class="wpggma_search_added"><div class="text-success"><i class="dashicons dashicons-v-bottom dashicons-yes"></i> Sync. Done</div></div>';
	if($price_updated)
		$last = '<div class="wpggma_search_added"><div class="text-success"><i class="dashicons dashicons-v-bottom dashicons-yes"></i> Sync. Done</div><div><p>Price Updated!</p></div></div>';
	
	echo wpggma_table_row($wp_link_id, $link, $last);
	
	die();
	
}
add_action('wp_ajax_wpggma_resync_link', 'wpggma_resync_link');


// ---------------------------------------------------
// Tool: Parse
// ---------------------------------------------------
function wpggma_tool_parse(){
	if( !defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['action']) || !isset($_POST['data']) || !current_user_can('manage_options') ) 
		return;
	
	parse_str($_POST['data'], $data);
	
	$table = str_get_html($data['wpggma_tool_parse_textarea']);
	
	$i=0; $row = array();
	foreach($table->find('tr.rf-main') as $e) {
		$i++;
		$row[] = $e;
	}
	
	if(!$i)
		die('<div>[<span class="currentDate"></span>] <span class="text-danger">No data found.</span></div>');
	
	if((int)$_POST['total'] && !empty($row)){
		$total = (int)$_POST['total'];
		$offset = (int)$_POST['offset'];
		$current = $offset+1;
		
		$element = $row[$offset];
		
		$url = trim($element->find('.redirect .url', 0)->plaintext);
		$cref = trim($element->find('.reference .url', 0)->plaintext);
		
		$check_args = array(
			'post_type'   		=> 'wpggma_links',
			'posts_per_page' 	=> 1,
			'meta_key'			=> 'wpggma_link_url',
			'meta_value'		=> $url,
			'meta_compare'		=> '=',
			'fields'			=> 'ids'
		);
		$check_query = 			new WP_Query($check_args);
		$check_query_posts = 	$check_query->posts;
		$check_id = 			$check_query_posts[0];
		
		if(!empty($check_id)){
			$check_cref = get_post_meta($check_id, 'wpggma_link_cref', true);
			$check_ref = wpggma_get_ref_link($check_id);
			if( ($check_cref && $check_cref == $cref) || ($check_ref == $cref) ){
				echo '<div>[<span class="currentDate"></span>] ['.$current.'] <span class="text-danger">Already exists</span>: <a href="' . $url . '" target="_blank">' . $cref . '</a></div>';
				die();
			}
		}
		
		$link = array();
		$link = wpggma_scrap_link_type($url . '?___store=english');
		$link['cref'] = $cref;
		
		$link_insert = array(
							'post_title'    => 		$link['name'],
							'post_content'  => 		'',
							'post_name'  => 		$link['cref'],
							'post_status'   => 		'publish',
							'post_type'     => 		'wpggma_links'
		);

		$link_id = wp_insert_post($link_insert);
		
		update_post_meta( $link_id, 'wpggma_link_type', 		$link['type'] );	
		update_post_meta( $link_id, 'wpggma_link_cref', 		$link['cref'] );
		update_post_meta( $link_id, 'wpggma_link_g2aid', 		$link['g2aid'] );
		update_post_meta( $link_id, 'wpggma_link_image', 		$link['image'] );
		update_post_meta( $link_id, 'wpggma_link_price', 		$link['price'] );
		update_post_meta( $link_id, 'wpggma_link_url', 			$link['url'] );
		update_post_meta( $link_id, 'wpggma_link_sync_time', 	time() );
		update_post_meta( $link_id, 'wpggma_link_sync_status', 	1 );
		
		echo '<div>[<span class="currentDate"></span>] ['.$current.'] <span class="text-success">Added:</span> <a href="' . $link['url'] . '" target="_blank">' . $link['cref'] . '</a> <span>'.$link['price'].'€</span></div>';
		die();
		
	}
	
	echo '<div>[<span class="currentDate"></span>] Found <strong>'.$i.'</strong> Links!</div>';
	echo '<input type="hidden" class="wpggma_tool_parse_total" value="'.$i.'" />';
	die();
	
}
add_action('wp_ajax_wpggma_tool_parse', 'wpggma_tool_parse');