<?php

// ----------------------------------------------------------------
// wpGGMA: Games - Post Type
// ----------------------------------------------------------------
add_action( 'init', 'wpggma_post_type_links', 0 );
function wpggma_post_type_links() {

	$labels = array(
		'name'                => 'G2A Links',
		'singular_name'       => 'G2A Link',
		'menu_name'           => 'G2A Links',
		'parent_item_colon'   => 'Parent G2A Link:',
		'all_items'           => 'G2A Links',
		'view_item'           => 'View G2A Link',
		'add_new_item'        => 'Add G2A Link',
		'add_new'             => 'Add G2A Link',
		'edit_item'           => 'Edit G2A Link',
		'update_item'         => 'Update G2A Link',
		'search_items'        => 'Search G2A Link',
		'not_found'           => 'G2A Link Not found',
		'not_found_in_trash'  => 'Not found in Trash',
	);
	$args = array(
		'label'               => 'link',
		'description'         => 'G2A Links Post Type',
		'labels'              => $labels,
		'supports'            => array( 'title', 'custom-fields' ),
		'taxonomies'          => array(),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'show_in_nav_menus'   => false,
		//'show_in_menu'        => true,
		//'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => '',
		'can_export'          => false,
		'has_archive'         => false,
		//'rewrite'             => array('slug' => esc_attr(get_option('wpggma_RewriteBaseSlug')), 'with_front' => true),
		'rewrite'             => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
	);
	register_post_type( 'wpggma_links', $args );

}

add_action( 'admin_head', 'wpggma_post_type_links_icon' );
function wpggma_post_type_links_icon(){ ?>
<style>
	#adminmenu .menu-icon-wpggma_links div.wp-menu-image:before {
		content: "\f103";
	}
</style>
<?php }

add_action('init', 'wpggma_post_type_links_rewrite');
function wpggma_post_type_links_rewrite(){
    add_rewrite_rule(esc_attr(get_option('wpggma_RewriteBaseSlug')) . '/'.esc_attr(get_option('wpggma_RewriteGameSlug')).'-([^/]+)-([0-9]+)/?$', 'index.php?post_type=wpggma_links&p=$matches[2]', 'top');
}

add_filter('post_type_link', 'wpggma_post_type_links_link', 10, 2);
function wpggma_post_type_links_link($url, $post){
    if (get_post_type($post) == 'wpggma_links') {
		return home_url(esc_attr(get_option('wpggma_RewriteBaseSlug')) . '/' . user_trailingslashit( esc_attr(get_option('wpggma_RewriteGameSlug')) . '-' . $post->post_name . '-' . $post->ID));
    }
    return $url;
}

function wpggma_post_type_links_template(){
    if(get_post_type() == 'wpggma_links' && is_single()){
	
	global $post;
	$redirect = wpggma_get_ref_link($post->ID, true);
	$check_cref = get_post_meta($post->ID, 'wpggma_link_cref', true);
	if($check_cref)
		$redirect = 'https://www.g2a.com/r/' . $check_cref;
	?>
<html>
<head>
<style>
body{
margin:0;
padding:150px;
font-family: sans-serif;
overflow-wrap: break-word;
color: #333;
font-size: 14px;
line-height: 1.42857;
}
</style>
<meta name="robots" content="noindex,nofollow">
<meta http-equiv="refresh" content="1;url=<?php echo $redirect; ?>" />
<title>Redirecting...</title>
</head>
<body>
</body>
</html>
	<?php
	exit();}
}
add_action( 'template_redirect', 'wpggma_post_type_links_template' );