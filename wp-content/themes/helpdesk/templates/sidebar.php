<?php
global $helpdesk;
$sidebar = redux_post_meta( 'helpdesk', $post->ID, 'sidebar' );
if (is_page() && isset( $sidebar ) && $sidebar != '') {
	dynamic_sidebar($helpdesk['sidebar']);
} else {
	dynamic_sidebar('sidebar-primary');
}
?>
