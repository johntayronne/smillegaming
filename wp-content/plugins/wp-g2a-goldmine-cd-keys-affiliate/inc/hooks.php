<?php

// wpGGMA: Flush Rewrite on Update
add_action('admin_init', 'wpggma_filter_flush_rewrite');
function wpggma_filter_flush_rewrite() {
    if ( get_option('wpggma_RewriteFlush') == 1 ){
        flush_rewrite_rules();
        update_option('wpggma_RewriteFlush', 0);
    }
}

// wpGGMA: Sanitize Settings
add_filter( 'pre_update_option_wpggma_RewriteBaseSlug', 'wpggma_sanitize_slug', 10, 2 );
add_filter( 'pre_update_option_wpggma_RewriteGameSlug', 'wpggma_sanitize_slug', 10, 2 );
function wpggma_sanitize_slug( $new_value, $old_value ) {
	$new_value = sanitize_title( $new_value );
	return $new_value;
}

// wpGGMA: Admin Menu Current
add_action('admin_footer', 'wpggma_admin_menu_current');
function wpggma_admin_menu_current() {
	global $post;
	
	if(get_post_type() == 'wpggma_links'){
		echo <<<HTML
			<script type="text/javascript">
			jQuery(document).ready( function($) {
				$('#toplevel_page_wp-g2a-goldmine').addClass('current wp-has-current-submenu wp-menu-open');
				$('#toplevel_page_wp-g2a-goldmine li.wp-first-item').addClass('current');
			});     
			</script>
HTML;
	}

}