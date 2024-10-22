<?php 
/*
Plugin Name: WP G2A Goldmine Affiliate
Plugin URI: http://hwk.fr
Description: Search, Add & Manage G2A Goldmine REF Links. Display them direclty in your Wordpress! Earn money easily on every purchase your visitors make!
Author: hwk
Version: 0.7.1.1
Author URI: http://hwk.fr
Licence: GPLv2
*/

if(!defined('ABSPATH'))
  die('You are not allowed to call this page directly.');

defined( 'WP_GGMA_PLUGIN_ABS_PATH' ) || define( 'WP_GGMA_PLUGIN_ABS_PATH', plugin_dir_path( __FILE__ ) );

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( !class_exists('Unirest') )
	require( WP_GGMA_PLUGIN_ABS_PATH . 'library/Unirest.php');

if( !class_exists('simple_html_dom_node') )
	require( WP_GGMA_PLUGIN_ABS_PATH . 'library/simple_html_dom.php');

require( WP_GGMA_PLUGIN_ABS_PATH . 'inc/functions.php');
require( WP_GGMA_PLUGIN_ABS_PATH . 'inc/hooks.php');
require( WP_GGMA_PLUGIN_ABS_PATH . 'inc/post-types.php');
require( WP_GGMA_PLUGIN_ABS_PATH . 'inc/ajax.php');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action('admin_menu', 'wpggma_admin_menu');
function wpggma_admin_menu(){

	$wpggma_Activate = 	get_option('wpggma_Activate');
	if(!$wpggma_Activate){
		
		add_menu_page( 
			'WP G2A Affiliate',
			'WP G2A Affiliate',
			'manage_options',
			'wp-g2a-goldmine',
			'wpggma_admin_page_activate',
			'dashicons-admin-links',
			99
		);
		
		$settings = add_submenu_page('wp-g2a-goldmine', 'WP G2A Goldmine: Activate', 'Settings', 'manage_options', 'wp-g2a-goldmine', 'wpggma_admin_page_activate');
		add_action( 'admin_print_styles-' . $settings, 'wpggma_admin_css' );
		add_action( 'admin_print_scripts-' . $settings, 'wpggma_admin_js' );
		
		
	}else{
		
		add_menu_page( 
			'WP G2A Affiliate',
			'WP G2A Affiliate',
			'manage_options',
			'wp-g2a-goldmine',
			'wpggma_admin_page_links',
			'dashicons-admin-links',
			99
		);
		
		$links = add_submenu_page('wp-g2a-goldmine', 'WP G2A Goldmine: Links', 'Links', 'manage_options', 'wp-g2a-goldmine', 'wpggma_admin_page_links');
		add_action( 'admin_print_styles-' . $links, 'wpggma_admin_css' );
		add_action( 'admin_print_scripts-' . $links, 'wpggma_admin_js' );
		
		$settings = add_submenu_page('wp-g2a-goldmine', 'WP G2A Goldmine: Settings', 'Settings', 'manage_options', 'wp-g2a-goldmine-settings', 'wpggma_admin_page_settings');
		add_action( 'admin_print_styles-' . $settings, 'wpggma_admin_css' );
		add_action( 'admin_print_scripts-' . $settings, 'wpggma_admin_js' );
		
		$import = add_submenu_page('wp-g2a-goldmine', 'WP G2A Goldmine: Bulk Import', 'Import', 'manage_options', 'wp-g2a-goldmine-import', 'wpggma_admin_page_import');
		add_action( 'admin_print_styles-' . $import, 'wpggma_admin_css' );
		add_action( 'admin_print_scripts-' . $import, 'wpggma_admin_js' );
		
	}
}

function wpggma_admin_css(){
	wp_enqueue_style( 'stylesheet_admin', plugins_url('/css/admin.css', __FILE__) );
	wp_enqueue_style( 'linedtextarea', plugins_url('/css/jquery.numberedtextarea.css', __FILE__) );
}

function wpggma_admin_js(){
	wp_enqueue_script( 'jquery-functions', plugins_url('/js/admin.js', __FILE__), array( 'jquery' )  );
	wp_enqueue_script( 'jquery-linedtextarea', plugins_url('/js/jquery.numberedtextarea.js', __FILE__), array( 'jquery' )  );
}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wpggma_admin_plugins_link' );
function wpggma_admin_plugins_link($links){
	
	$wpggma_Activate = 	get_option('wpggma_Activate');
	if(!$wpggma_Activate){
		$slug = 'wp-g2a-goldmine';
		$name = 'Setup';
		
	}else{
		$slug = 'wp-g2a-goldmine-settings';
		$name = 'Settings';
		
	}
	
	$mylinks = array(
		'<a href="' . admin_url( 'admin.php?page=' . $slug ) . '">'.$name.'</a>',
	);
	return array_merge($links, $mylinks);
}

add_action( 'admin_notices', 'wpggma_admin_plugins_activation_notice' );
function wpggma_admin_plugins_activation_notice(){
	$screen = get_current_screen();
    if($screen->parent_base != 'wp-g2a-goldmine' && !get_option('wpggma_Activate')){ ?>
        <div class="updated notice">
            <p><strong>WP G2A Goldmine Affiliate Ready!</strong> Please <a href="/wp-admin/admin.php?page=wp-g2a-goldmine">Setup the plugin here</a>.</p>
        </div>
	<?php
    }
}

add_action( 'admin_init', 'wpggma_admin_settings' );
function wpggma_admin_settings(){
	register_setting( 'wpggma-settings-group', 'wpggma_RewriteFlush' );
	register_setting( 'wpggma-settings-group', 'wpggma_RewriteBaseSlug' );
	register_setting( 'wpggma-settings-group', 'wpggma_RewriteGameSlug' );
	register_setting( 'wpggma-settings-group', 'wpggma_SyncG2A' );
	
	if(get_option('wpggma_RewriteBaseSlug') === false || get_option('wpggma_RewriteBaseSlug') == ''){
		update_option('wpggma_RewriteBaseSlug', 'out');
	}
	
	if(get_option('wpggma_RewriteGameSlug') === false || get_option('wpggma_RewriteGameSlug') == ''){
		$host = parse_url(get_site_url(), PHP_URL_HOST);
		$host = preg_split('/(?=\.[^.]+$)/', $host);
		$host = sanitize_title(str_ireplace('www.', '', $host[0]));
		update_option('wpggma_RewriteGameSlug', $host);
	}
	
	$wpggma_Activate = 			get_option('wpggma_Activate');
	$wpggma_RewriteBaseSlug = 	get_option('wpggma_RewriteBaseSlug');
	$wpggma_RewriteGameSlug = 	get_option('wpggma_RewriteGameSlug');
	$wpggma_SyncG2A = 			get_option('wpggma_SyncG2A');

}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// LINKS
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function wpggma_admin_page_activate() {
$wpggma_Activate = get_option('wpggma_Activate');
?>

<div class="wrap" style="max-width:1530px;">
	<h1></h1>
	<div class="postbox" style="padding:150px 0;">
		<div class="text-center">
			<div style="margin-bottom:30px;">
				<a href="https://www.g2a.com/goldmine/join/us/Z49U47G6T" target="_blank" class="wpggma_ActivateLink">
					<img src="<?php echo plugins_url('/images/g2a.png', __FILE__ ); ?>" class="img-responsive" style="max-width:450px; display:inline-block" />
				</a>
			</div>
			
			<div style="margin-bottom:7px;">
				<h1>Welcome to the WP G2A Goldmine Affiliate!</h1>
			</div>
			
			<div style="margin-bottom:35px;">
				You need a G2A Goldmine Account and must be logged in to continue.
			</div>
			
			<div style="margin-bottom:10px;">
				<a href="https://www.g2a.com/goldmine/join/us/Z49U47G6T" target="_blank" class="button button-primary wpggma_ActivateLink" style="padding:7px 15px; height:auto;">Create a Free Account</a>
			</div>
			<div style="color:#aaa;">
				or <a href="https://www.g2a.com/goldmine/join/us/Z49U47G6T" target="_blank" class="wpggma_ActivateLink">login here</a>
			</div>
			
		</div>
	</div>
</div>
	
<?php }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// LINKS
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function wpggma_admin_page_links() {
$wpggma_Activate = 			get_option('wpggma_Activate');
$wpggma_RewriteBaseSlug = 	get_option('wpggma_RewriteBaseSlug');
$wpggma_RewriteGameSlug = 	get_option('wpggma_RewriteGameSlug');
$wpggma_SyncG2A = 			get_option('wpggma_SyncG2A');
add_thickbox();
?>

<div class="wrap" style="max-width:1530px;">

	<h1>WP G2A Goldmine Affiliate Plugin</h1>
	<div class="pull-right" style="margin-top:25px;">
	<a href="https://www.g2a.com/goldmine/join/us/Z49U47G6T" target="_blank" class="button">
		<i class="dashicons dashicons-external"></i> G2A Goldmine
	</a>
	</div>
	
	<?php
	$wpggma_links_args = array(
		'post_type'   		=> 'wpggma_links',
		'posts_per_page' 	=> -1,
		'fields'			=> 'ids'
	);
	$wpggma_links_query = 	new WP_Query($wpggma_links_args);
	$wpggma_links = 		$wpggma_links_query->posts;
	?>
	
	<h2 class="nav-tab-wrapper nav-tabs">
		<a href="<?php menu_page_url('wp-g2a-goldmine'); ?>" class="nav-tab nav-tab-active <?php echo (!$wpggma_Activate) ? 'hide': ''; ?>">Links<?php if(count($wpggma_links) > 0){ ?> <span class="label label-success wpggma_links_counter" style="vertical-align: top;margin-left: 5px;margin-top: 3px;position: relative; display: inline-block;line-height: 14px;"><?php echo count($wpggma_links); ?></span><?php } ?></a>
		<a href="<?php menu_page_url('wp-g2a-goldmine-settings'); ?>" class="nav-tab">Settings</a>
		<a href="<?php menu_page_url('wp-g2a-goldmine-import'); ?>" class="nav-tab <?php echo (!$wpggma_Activate) ? 'hide': ''; ?>">Bulk Import</a>
	</h2>
	
	<div id="modal_window" style="display:none;">
	
		<div tabindex="0" style="position:relative; display:block;">
			<div class="media-modal wp-core-ui">
			
				<button type="button" class="button-link media-modal-close" onclick="tb_remove()">
					<span class="media-modal-icon">
						<span class="screen-reader-text">Close media panel</span>
					</span>
				</button>
				
				<div class="media-modal-content">
					<div class="media-frame mode-select wp-core-ui hide-menu" id="__wp-uploader-id-0">
						
						<div class="media-frame-title">
							<h1>Search G2A</h1>
						</div>
						
						<div class="media-frame-content" style="top:50px; bottom:0;">
							<div class="attachments-browser" style="overflow:auto;">
							
								<div style="background:#fcfcfc;">
									<div class="wpggma_search_error"></div>
								</div>
					
								<div class="wpggma_search_results" style="height:100%; width:100%; position:relative; background:#fcfcfc;"></div>
								
							</div>
							
						</div>
					
					</div>
				</div>
			
			</div>
		</div>
		
	</div>
	
	<div id="poststuff">
	<div id="post-body" class="metabox-holder">
		<div id="post-body-content">
			
			<div class="nav-tab-panel">
			<div class="row">
				<div class="col-lg-8">
					<?php
					$wpggma_links_args = array(
						'post_type'   		=> 'wpggma_links',
						'posts_per_page' 	=> -1,
						'fields'			=> 'ids'
					);
					$wpggma_links_query = 	new WP_Query($wpggma_links_args);
					$wpggma_links = 		$wpggma_links_query->posts;
					?>

					<table class="postbox wpggma_links widefat striped" style="margin-top:0;">
						<thead>
							<tr>
								<th class="manage-column">
								</th>
								<th class="manage-column">
									<span>Title</span>
								</th>
								<th class="manage-column">
									<div class="text-center">ID</div>
								</th>
								<th class="manage-column">
									<div class="text-center"><a href="javascript:void();" class="wpggma_ReSyncAll">ReSync. All</a></div>
								</th>
							</tr>
						</thead>
						
						<tbody>
						
						<?php
						if(empty($wpggma_links)){ ?>
						
							<tr valign="middle" class="" style="border-bottom:1px solid #eee;" data-id="0">
								<td colspan="5" style="text-align:center; padding:309px 0;background:#fcfcfc;color: #aaa;font-size: 22px;">
									<div style="margin-bottom:7px;color:#333;">No G2A Link to show</div>
									<div style="font-size:13px;">Search and add one! :)</div>
								</td>
							</tr>
							
						<?php 
						}else{
							
							foreach($wpggma_links as $wp_link_id){
							
								$link = array();
								$link['name'] = 	get_the_title($wp_link_id);
								$link['url'] = 		get_post_meta($wp_link_id, 'wpggma_link_url', true);
								$link['image'] = 	get_post_meta($wp_link_id, 'wpggma_link_image', true);
								$link['price'] = 	get_post_meta($wp_link_id, 'wpggma_link_price', true);
								
								echo wpggma_table_row($wp_link_id, $link);
							
							}
							
						} ?>
						</tbody>
						
					</table>
					
				</div>
				
				<div class="col-lg-4">
					<div class="postbox">
						<h2 class="hndle"><span>Search G2A Database</span></h2>
						<div class="inside">
							
							<form class="wpggma_search">
							
								<table class="form-table">
									<tbody>
									
										<tr valign="top">
										<th scope="row" class="settinglabel"><label>Query:</label></th>
										<td class="settingfield">
											<input type="text" class="regular-text" name="wpggma_search_link" value="" placeholder="For Honor, Fifa 17, Battlefield etc..." style="vertical-align:middle; width:100%;" />
										</td>
										</tr>
										
										<tr valign="top">
										<th scope="row" class="settinglabel"><label>Platform:</label></th>
										<td class="settingfield">
											<select name="wpggma_search_link_platform" style="vertical-align:middle; width:150px;">
												<option value="0">All</option>
												<option value="16">Origin</option>
												<option value="17">Steam</option>
												<option value="115">Xbox</option>
												<option value="118">PSN</option>
												<option value="117">Apple</option>
												<option value="100">Gameforge</option>
												<option value="18">Battlenet</option>
												<option value="24">Uplay</option>
												<option value="547">GOG</option>
												<option value="2016">HTC Vive</option>
												<option value="724">Oculus Rift</option>
											</select>
										</td>
										</tr>
										
										<tr valign="top">
										<th scope="row" class="settinglabel"><label>Sorting:</label></th>
										<td class="settingfield">
											<select name="wpggma_search_link_sort" style="vertical-align:middle; width:150px;">
												<option value="popularity+desc">Most Popular</option>
												<option value="popularity+asc">Least Popular</option>
												<option value="added+desc">Date Recent</option>
												<option value="added+asc">Date Oldest</option>
												<option value="price+asc">Price Lowest</option>
												<option value="price+desc">Price Highest</option>
												<option value="name+asc">Name A-Z</option>
												<option value="name+desc">Name Z-A</option>
											</select>
										</td>
										</tr>
										
										<tr valign="top">
										<th scope="row" class="settinglabel"><label>Results:</label></th>
										<td class="settingfield">
											<select name="wpggma_search_link_rows" style="vertical-align:middle; width:150px;">
												<option value="12">12</option>
												<option value="24">24</option>
											</select>
										</td>
										</tr>
										
										<tr valign="top">
										<th scope="row" class="settinglabel"></th>
										<td class="settingfield">
											<button class="button button-primary" style="vertical-align:middle;">
												<i class="dashicons dashicons-v-middle dashicons-search"></i> Search G2A
											</button>
										</td>
										</tr>
									
									</tbody>
								</table>
							</form>

						</div>
						<div class="panel-footer" style="background:#f8f8f8;">
							<div style="color:#999;">
								<p style="margin:0; margin-bottom:4px; font-size:12px;">Advanced Query: <kbd>direct:https://www.g2a.com/m4a1-s-flashback.html</kbd></p>
								<p style="margin:0; font-size:12px;">Can be a <a href="https://www.g2a.com/shop/sitemap/categories" target="_blank">Category</a> or a <a href="https://www.g2a.com/weeklysale" target="_blank">Custom URL</a>.</p>
							</div>
						</div>
					</div>
					
					<div class="postbox">
						<h2 class="hndle"><span>Available Shortcodes</span></h2>
						<div class="inside">
							<table class="form-table">

								<tr valign="top">
								<th scope="row" class="settinglabel"><label>Link: Title</label></th>
								<td class="settingfield">
									<kbd>[G2A id="LINK_ID_HERE" display="title"]</kbd>
								</td>
								</tr>
								
								<tr valign="top">
								<th scope="row" class="settinglabel"><label>Link: Image URL</label></th>
								<td class="settingfield">
									<kbd>[G2A id="LINK_ID_HERE" display="image_url"]</kbd>
								</td>
								</tr>
								
								<tr valign="top">
								<th scope="row" class="settinglabel"><label>Link: Image tag</label></th>
								<td class="settingfield">
									<kbd>[G2A id="LINK_ID_HERE" display="image"]</kbd>
								</td>
								</tr>
								
								<tr valign="top">
								<th scope="row" class="settinglabel"><label>Link: Price</label></th>
								<td class="settingfield">
									<kbd>[G2A id="LINK_ID_HERE" display="price"]</kbd>
								</td>
								</tr>
								
								<tr valign="top">
								<th scope="row" class="settinglabel"><label>Link: URL</label></th>
								<td class="settingfield">
									<kbd>[G2A id="LINK_ID_HERE" display="url"]</kbd>
								</td>
								</tr>
								
							</table>
						</div>
					</div>
					
					
				</div>
			</div>
			</div>
				
		</div>
	</div>
	</div>

</div><!-- /end #wrap -->

<?php }


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// SETTINGS
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function wpggma_admin_page_settings() {
$wpggma_Activate = 			get_option('wpggma_Activate');
$wpggma_RewriteBaseSlug = 	get_option('wpggma_RewriteBaseSlug');
$wpggma_RewriteGameSlug = 	get_option('wpggma_RewriteGameSlug');
$wpggma_SyncG2A = 			get_option('wpggma_SyncG2A');
?>

<div class="wrap" style="max-width:1530px;">

	<h1>WP G2A Goldmine Affiliate Plugin</h1>
	<div class="pull-right" style="margin-top:25px;">
	<a href="https://www.g2a.com/goldmine/join/us/Z49U47G6T" target="_blank" class="button">
		<i class="dashicons dashicons-external"></i> G2A Goldmine
	</a>
	</div>
	
	<?php
	$wpggma_links_args = array(
		'post_type'   		=> 'wpggma_links',
		'posts_per_page' 	=> -1,
		'fields'			=> 'ids'
	);
	$wpggma_links_query = 	new WP_Query($wpggma_links_args);
	$wpggma_links = 		$wpggma_links_query->posts;
	?>
	
	<h2 class="nav-tab-wrapper nav-tabs">
		<a href="<?php menu_page_url('wp-g2a-goldmine'); ?>" class="nav-tab <?php echo (!$wpggma_Activate) ? 'hide': ''; ?>">Links<?php if(count($wpggma_links) > 0){ ?> <span class="label label-success wpggma_links_counter" style="vertical-align: top;margin-left: 5px;margin-top: 3px;position: relative; display: inline-block;line-height: 14px;"><?php echo count($wpggma_links); ?></span><?php } ?></a>
		<a href="<?php menu_page_url('wp-g2a-goldmine-settings'); ?>" class="nav-tab nav-tab-active">Settings</a>
		<a href="<?php menu_page_url('wp-g2a-goldmine-import'); ?>" class="nav-tab <?php echo (!$wpggma_Activate) ? 'hide': ''; ?>">Bulk Import</a>
	</h2>
	
	<div id="poststuff">
	<div id="post-body" class="metabox-holder">
		<div id="post-body-content">
			
			<div class="nav-tab-panel">
			<form method="post" action="options.php">
				<?php settings_fields( 'wpggma-settings-group' ); ?>
				<?php do_settings_sections( 'wpggma-settings-group' ); ?>
				
				<div class="row">
					<div class="col-lg-8">
								
						<div class="postbox">
							<h2 class="hndle"><span>Settings</span></h2>
							<div class="inside">
								<table class="form-table">

									<tr valign="top" class="wpggma_ActivateWrapper">
									<th scope="row" class="settinglabel"><label>G2A Goldmine <span class="text-danger">*</span></label></th>
									<td class="settingfield">
										<a href="https://www.g2a.com/goldmine/join/us/Z49U47G6T" target="_blank" class="<?php echo (!$wpggma_Activate) ? 'wpggma_ActivateLink': ''; ?> <?php echo (!$wpggma_Activate) ? 'button-primary': 'button'; ?>">
											<i class="dashicons dashicons-external"></i> G2A Goldmine
										</a>
									</td>
									</tr>

									<tr valign="top">
									<th scope="row" class="settinglabel"><label>WP Redirect Slug <span class="text-danger">*</span></label></th>
									<td class="settingfield">
										<input type="hidden" name="wpggma_RewriteFlush" value="1" />
										<input type="text" name="wpggma_RewriteBaseSlug" class="regular-text" value="<?php echo esc_attr( get_option('wpggma_RewriteBaseSlug') ); ?>" placeholder="" 
										<?php echo (!$wpggma_Activate) ? 'disabled': ''; ?> required />
										<?php if(!$wpggma_Activate){ ?><p class="wpggma_ActivateLink_text">Please login to G2A Goldmine First.</p><?php } ?>
										<p class="<?php echo (!$wpggma_Activate) ? 'hide': ''; ?>">Base slug for outgoing links. Cannot be blank & must be unique.</p>
									</td>
									</tr>
									
									<tr valign="top">
									<th scope="row" class="settinglabel"><label>G2A REF Slug <span class="text-danger">*</span></label></th>
									<td class="settingfield">
										<input type="text" name="wpggma_RewriteGameSlug" class="regular-text" value="<?php echo esc_attr( get_option('wpggma_RewriteGameSlug') ); ?>" placeholder="" 
										<?php echo (!$wpggma_Activate) ? 'disabled': ''; ?> required />
										<?php if(!$wpggma_Activate){ ?><p class="wpggma_ActivateLink_text">Please login to G2A Goldmine First.</p><?php } ?>
										<div class="<?php echo (!$wpggma_Activate) ? 'hide': ''; ?>">
										<p>Base slug for G2A links. Cannot be blank & must be unique to your website.</p>
										<p>Wordpress URL: 
											<code>
												<?php 
												echo get_site_url() . '/';
												echo '<span class="wpggma_RewriteBaseSlug_mirror">';
												echo 	esc_attr( get_option('wpggma_RewriteBaseSlug') );
												echo '</span>';
												echo '/';
												echo '<span class="wpggma_RewriteGameSlug_mirror">';
												echo 	esc_attr( get_option('wpggma_RewriteGameSlug') );
												echo '</span>-game-name-2451';
												echo user_trailingslashit('');
												?>
											</code>
										</p>
										
										<p>Redirect to URL: 
											<code>
												<?php 
												echo 'https://www.g2a.com/r/';
												echo '<span class="wpggma_RewriteGameSlug_mirror">';
												echo 	esc_attr( get_option('wpggma_RewriteGameSlug') );
												echo '</span>-game-name-2451';
												?>
											</code>
										</p>
										</div>
									</td>
									</tr>

									<tr valign="top" class="<?php echo (!$wpggma_Activate) ? 'hide': ''; ?>">
									<th scope="row" class="settinglabel"><label>G2A Synchronization</label></th>
									<td class="settingfield">
										<div style="margin-top:5px;">Enabled</div>
										<ul>
											<li style="list-style:square inside; font-size:11px; color:#777; line-height:14px; margin:4px 0 0;">Automatically add new links to your G2A Account.</li>
											<li style="list-style:square inside; font-size:11px; color:#777; line-height:14px; margin:4px 0 0;">You must be logged to your G2A Account.</li>
											<li style="list-style:square inside; font-size:11px; color:#777; line-height:14px; margin:4px 0 0;">Does not synchronize G2A links limit.</li>
											<li style="list-style:square inside; font-size:11px; color:#777; line-height:14px; margin:4px 0 0;">Does not synchronize deleted links.</li>
										</ul>
										
									</td>
									</tr>

								</table>
							</div>	
						</div>
						
						<div class="postbox <?php echo (!$wpggma_Activate) ? 'hide': ''; ?>">
							<div class="inside">
								<?php submit_button(); ?>
							</div>
						</div>

					</div>
					
					<div class="col-lg-4">
						<div class="postbox">
							<h2 class="hndle"><span>Informations</span></h2>
							<div class="inside">
							<p>Welcome to WP G2A Goldmine Affiliate plugin!</p>
							<p>This plugin will let you manage your G2A Goldmine links and display them in your Wordpress allowing you to earn money on every purchase your visitors make! Before begenning, here are some facts you need to know:</p>
							
							<ul>
								<li style="list-style:square inside;">There is no G2A API.</li>
								<li style="list-style:square inside;">G2A has a limit of 100 REF links per Goldmine account.</li>
								<li style="list-style:square inside;">Most of G2A tasks have to be done manually (Delete, Edit etc...).</li>
							</ul>
							
							<p><strong><u>HOWEVER:</u></strong></p>
							<p>This plugin has a feature called "G2A Synchronization"</p>
							
							<ul>
								<li style="list-style:square inside;">Login to your G2A Goldmine Account in an another window.</li>
								<li style="list-style:square inside;">Every link created here will be automatically added to your G2A Goldmine!</li>
								<li style="list-style:square inside;">Unfortunately it cannot track the links limit or deletion. Those remain manual.</li>
								<li style="list-style:square inside;">Always keep your G2A Account opened to confirm new links. Enjoy!</li>
							</ul>
							
							</div>
						</div>
					</div>
				</div>
			</form>
			</div>
				
		</div>
	</div>
	</div>

</div><!-- /end #wrap -->

<?php }


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPORT
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function wpggma_admin_page_import() {
$wpggma_Activate = 			get_option('wpggma_Activate');
$wpggma_RewriteBaseSlug = 	get_option('wpggma_RewriteBaseSlug');
$wpggma_RewriteGameSlug = 	get_option('wpggma_RewriteGameSlug');
$wpggma_SyncG2A = 			get_option('wpggma_SyncG2A');
?>

<div class="wrap" style="max-width:1530px;">

	<h1>WP G2A Goldmine Affiliate Plugin</h1>
	<div class="pull-right" style="margin-top:25px;">
	<a href="https://www.g2a.com/goldmine/join/us/Z49U47G6T" target="_blank" class="button">
		<i class="dashicons dashicons-external"></i> G2A Goldmine
	</a>
	</div>
	
	<?php
	$wpggma_links_args = array(
		'post_type'   		=> 'wpggma_links',
		'posts_per_page' 	=> -1,
		'fields'			=> 'ids'
	);
	$wpggma_links_query = 	new WP_Query($wpggma_links_args);
	$wpggma_links = 		$wpggma_links_query->posts;
	?>
	
	<h2 class="nav-tab-wrapper nav-tabs">
		<a href="<?php menu_page_url('wp-g2a-goldmine'); ?>" class="nav-tab <?php echo (!$wpggma_Activate) ? 'hide': ''; ?>">Links<?php if(count($wpggma_links) > 0){ ?> <span class="label label-success wpggma_links_counter" style="vertical-align: top;margin-left: 5px;margin-top: 3px;position: relative; display: inline-block;line-height: 14px;"><?php echo count($wpggma_links); ?></span><?php } ?></a>
		<a href="<?php menu_page_url('wp-g2a-goldmine-settings'); ?>" class="nav-tab">Settings</a>
		<a href="<?php menu_page_url('wp-g2a-goldmine-import'); ?>" class="nav-tab nav-tab-active <?php echo (!$wpggma_Activate) ? 'hide': ''; ?>">Bulk Import</a>
	</h2>
	
	<div id="poststuff">
	<div id="post-body" class="metabox-holder">
		<div id="post-body-content">
			
			<div class="nav-tab-panel">
			<div class="row">
			
				<div class="col-lg-8">
					
					<form class="wpggma_tool_parse">
					
					<div class="postbox">
						<h2 class="hndle"><span>Bulk G2A Goldmine Links Importer</span></h2>
						<div class="inside">
						
						
							<table class="form-table">

								<tr valign="top">
								<th scope="row" class="settinglabel"><label>G2A Source Code <span class="text-danger">*</span></label></th>
								<td class="settingfield">
									
									<div class="well well-sm" style="margin:0;">
										<textarea name="wpggma_tool_parse_textarea" style="width:100%; height:200px; padding:12px;" placeholder="&lt;table class=&quot;normal-table reflinks-table&quot;&gt; ... &lt;/table&gt;" required></textarea>
									</div>
								</td>
								</tr>
								
								<tr valign="top">
								<th scope="row" class="settinglabel"><label>Process Logs</label></th>
								<td class="settingfield">
									<pre class="wpggma_tool_parse_logs" style="min-width:100%; width:100%; height:262px; padding:12px; color:#555; margin:0;"><div>---------------------------------------</div><div>[<span class="currentDate"></span>] <em>Waiting for source code.</em></div><div>---------------------------------------</div></pre>
								</td>
								</tr>
								
							</table>
							
						
						</div>
					</div>
					
					<div class="postbox ">
						<div class="inside">
							<p class="submit">
								<button class="button button-primary">Import Links</button>
								<button class="button wpggma_tool_parse_logs_clear">Clear logs</button>
							</p>
						</div>
					</div>
					
					</form>
				</div>
				
				<div class="col-lg-4">
					
					<div class="postbox">
						<h2 class="hndle"><span>Informations</span></h2>
						<div class="inside">
						
							<p style="margin-top:0;">This tool will find any existing G2A Goldmine links and synchronize them with the Plugin. This mean that all your actual G2A Links will be supported! The only way to do so is to copy/paste the source code of your <a href="https://www.g2a.com/goldmine/reflinks/" target="_blank">G2A Goldmine REF Links page</a>.</p>
							
							<p><strong>This tool is 100% safe</strong>, as there is absolutely no personal data processed.</p>
							
							<ul>
								<li style="list-style:square inside;">Go to your <a href="https://www.g2a.com/goldmine/reflinks/" target="_blank">G2A Goldmine REF Links page</a></li>
								<li style="list-style:square inside;"><kbd>CTRL+U</kbd> to show the source code.</li>
								<li style="list-style:square inside;"><kbd>CTRL+F</kbd> and look for <kbd>&lt;table class=&quot;normal-table reflinks-table&quot;&gt;</kbd>.</li>
								<li style="list-style:square inside;">Copy everything until <kbd>&lt;/table&gt;</kbd>.</li>
								<li style="list-style:square inside;">Paste it in the right box.</li>
								<li style="list-style:square inside;">You can also copy/paste the whole page source, it will still work!</kbd></li>
							</ul>
							
							<hr />
							
							<div class="row text-center">
								<div class="col-md-6">
									<div class="panel panel-default">
										<div class="panel-body" style="padding:10px;">
											<a href="<?php echo plugins_url('/images/help/g2a-parse-links.jpg', __FILE__ ); ?>" target="_blank">
												<div style="margin-bottom:7px;"><img src="<?php echo plugins_url('/images/help/g2a-parse-links.jpg', __FILE__ ); ?>" class="img-responsive" /></div>
												Enlarge Preview
											</a>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="panel panel-default">
										<div class="panel-body" style="padding:10px;">
											<a href="<?php echo plugins_url('/images/help/g2a-parse-links-source.jpg', __FILE__ ); ?>" target="_blank">
												<div style="margin-bottom:7px;"><img src="<?php echo plugins_url('/images/help/g2a-parse-links-source.jpg', __FILE__ ); ?>" class="img-responsive" /></div>
												Enlarge Preview
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
									
					</div>
				</div>
				
			</div>
			</div>
				
		</div>
	</div>
	</div>

</div><!-- /end #wrap -->

<?php }