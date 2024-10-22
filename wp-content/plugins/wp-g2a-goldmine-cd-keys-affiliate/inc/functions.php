<?php

function wpggma_array2obj($array, $exceptions = array()){
	foreach($array as $key => $value){
		if( is_array($value) && !in_array($key, $exceptions)) 
			$array[$key] = wpcdk_array2obj($value, $exceptions);
	}
	return (object) $array;
}

function wpggma_get_ref_link($post_id, $full = false){

	if(get_post_type($post_id) != 'wpggma_links')
		return;
	
	$post = get_post($post_id);
	
	$post_id_len = strlen($post_id);
	
	$post_slug = $post->post_name . '-';
	$post_slug_len = strlen($post_slug);
	
	$base = esc_attr(get_option('wpggma_RewriteGameSlug')) . '-';
	$base_len = strlen($base);
	
	if($base_len + $post_slug_len + $post_id_len >= 50){
		$post_slug = substr($post_slug, 0, (48 - ($base_len + $post_id_len))) . '-';
		$post_slug = str_replace('--', '-', $post_slug);
	}
	
	$return = $base . $post_slug . $post_id;
	
	if($full){
		$check_cref = get_post_meta($post_id, 'wpggma_link_cref', true);
		if($check_cref)
			return 'https://www.g2a.com/r/' . $check_cref;
		
		return 'https://www.g2a.com/r/' . $return;
		
	}else{
		return $return;
		
	}
	
	
}

function wpggma_scrap($url, $headers = array()){
	$reponse_error = false;
	try{
		$response = Unirest\Request::get($url, $headers, $parameters = null);
	}catch (Exception $e){
		$reponse_error = $e;
	}
	
	if($reponse_error)
		die('cURL Error');
	
	return $response;
}

function wpggma_scrap_link_type($url, $object = false){
	
	$scrap = wpggma_scrap($url);
	$html = str_get_html($scrap->raw_body);
	
	$link = array();
	
	$link['name'] = $link['type'] = $link['image'] = $link['g2aid'] = '';
	$link['price'] = '-';
	$link['url'] = str_replace('?___store=english', '', $url);
	
	// Type: Game
	if(empty($link['name'])){
		$link['name'] = trim($html->find('.nameContent h1', 0)->plaintext);
		
		if(!empty($link['name'])){
			$link['type'] = 'game';
			$link['g2aid'] = trim($html->find('input[name=product]', 0)->value);
			
			$search = 'https://www.g2a.com/lucene/search/filter?minPrice=0.00&maxPrice=640.00&cn=&kr=&stock=all&event=&platform=0&search=' . urlencode($link['name']) . '&genre=0&cat=0&sortOrder=popularity+desc&start=0&rows=12&steam_app_id=&steam_category=&steam_prod_type=&includeOutOfStock=false&includeFreeGames=false';
			$scrap = wpggma_scrap($search);
			$json = json_decode($scrap->raw_body);
			
			$find = $json->docs;
			$find = $find[0];
			
			if(!empty($find)){
				$link['price'] = number_format((float)$find->minPrice, 2, '.', '');
				$link['image'] = $find->smallImage;
			}
		}
	}
	
	// Type: Category
	if(empty($link['name'])){
		$link['name'] = trim($html->find('h1#gamelist', 0)->plaintext);
		
		if(!empty($link['name'])){
			$link['type'] = 'category';
			
			$search = 'https://www.g2a.com/lucene/search/filter?minPrice=0.00&maxPrice=640.00&cn=&kr=&stock=all&event=&platform=0&search=' . urlencode($link['name']) . '&genre=0&cat=0&sortOrder=popularity+desc&start=0&rows=12&steam_app_id=&steam_category=&steam_prod_type=&includeOutOfStock=false&includeFreeGames=false';
			$scrap = wpggma_scrap($search);
			$json = json_decode($scrap->raw_body);
			
			$find = $json->docs;
			$find = $find[0];

			if(!empty($find)){
				$link['price'] = number_format((float)$find->minPrice, 2, '.', '');
				$link['image'] = $find->smallImage;
			}
			
			$link['name'] = $link['name'] . ' Games';
		}
	}
	
	// Type: Direct
	if(empty($link['name'])){
		$link['name'] = str_ireplace(' - G2A.com', '', trim($html->find('title', 0)->plaintext));
		if(empty($link['name']))
			$link['name'] = 'Cheap Games Sales!';
		
		$link['type'] = 'direct';
	}
	
	if($object)
		return wpggma_array2obj($link);
	
	return $link;
}

function wpggma_image($img = ''){
	if(empty($img))
		return '/wp-content/plugins/wp-g2a-goldmine/images/cover-g2a.jpg';
	
	return $img;
}


function wpggma_table_row($wp_link_id, $link, $last = ''){
	
	$wpggma_link_sync_time = 	get_post_meta($wp_link_id, 'wpggma_link_sync_time', 	true);
	$wpggma_link_sync_status = 	get_post_meta($wp_link_id, 'wpggma_link_sync_status', 	true);
	
	if(!$wpggma_link_sync_status){
		$class = 'border-left:2px solid #cc0000;';
		
	}elseif($wpggma_link_sync_status && (time() - $wpggma_link_sync_time > 172800)){
		$class = 'border-left:2px solid #ccc;';
		
	}elseif($wpggma_link_sync_status && (time() - $wpggma_link_sync_time < 172800)){
		$class = 'border-left:2px solid #5cb85c;';
		
	}else{
		$class = 'border-left:2px solid #ccc;';
		
	}

	ob_start();
	?>
	<tr valign="middle" style="border-bottom:1px solid #eee; <?php if(!$wpggma_link_sync_status){ ?>background:#f9ebeb;<?php }?>" data-id="<?php echo $wp_link_id; ?>">
		<td style="width:35px; min-width:35px; <?php echo $class; ?>">
			<img src="<?php echo wpggma_image($link['image']); ?>" style="max-width:35px;" />
		</td>
		<td style="vertical-align:middle;">
			<div><strong><a href="/wp-admin/post.php?post=<?php echo $wp_link_id; ?>&action=edit" class="row-title"><?php echo $link['name']; ?></a></strong><?php if($link['price'] && $link['price'] != '-'){ ?> <span><?php echo $link['price']; ?>€</span><?php } ?></div>
			
			<input type="hidden" class="wpggma_link_ref" value="<?php echo wpggma_get_ref_link($wp_link_id); ?>" />
			<input type="hidden" class="wpggma_link_url" value="<?php echo $link['url']; ?>" />
			
			<div class="row-actions">
				<span><a href="<?php echo get_permalink($wp_link_id); ?>" target="_blank">Redirection</a> | </span>
				<span><a href="<?php echo wpggma_get_ref_link($wp_link_id, true); ?>" target="_blank">G2A Ref</a> | </span>
				<span><a href="javascript:void(0);" data-id="<?php echo $wp_link_id; ?>" class="wpggma_DeleteLink text-danger">Trash</a> | </span>
				<span><a href="javascript:void(0);" data-id="<?php echo $wp_link_id; ?>" class="wpggma_ReSyncLink">ReSync.</a></span>
			</div>
		</td>
		<td style="vertical-align:middle;" class="text-center">
			<div><kbd><?php echo $wp_link_id; ?></kbd></div>
		</td>
		<td style="vertical-align:middle; width:200px;" class="text-center">
			<?php if(!$last){ ?>
				<div class="wpggma_search_added"></div>
				<div><a href="javascript:void(0);" data-id="<?php echo $wp_link_id; ?>" class="wpggma_ReSyncLink button"><i class="dashicons dashicons-v-bottom dashicons-update"></i> ReSync.</a></div>
			<?php }else{ ?>
				<?php echo $last; ?>
			<?php } ?>
		</td>
	</tr>
	<?php
	return ob_get_clean();
}



function wpggma_shortcode($atts){
    $a = shortcode_atts(array(
		'id' => false,
		'display' => false,
    ), $atts);
	
	$link_id = (int)$a['id'];
	$link_display = $a['display'];
	
	if(!$link_id || !$link_display || get_post_type($link_id) != 'wpggma_links')
		return;
	
	$game = get_post($link_id);
	
	if($link_display == 'title'){
		return $game->post_title;
		
	}elseif($link_display == 'image'){
		$image = get_post_meta($link_id, 'wpggma_link_image', true);
		return '<img src="' . wpggma_image($image) . '" class="g2a_image" />';
		
	}elseif($link_display == 'image_url'){
		$image = get_post_meta($link_id, 'wpggma_link_image', true);
		return wpggma_image($image);
		
	}elseif($link_display == 'price'){
		$price = get_post_meta($link_id, 'wpggma_link_price', true);
		return $price;
		
	}elseif($link_display == 'url'){
		$url = get_permalink($link_id);
		return $url;
		
	}
	
}
add_shortcode('G2A', 'wpggma_shortcode');