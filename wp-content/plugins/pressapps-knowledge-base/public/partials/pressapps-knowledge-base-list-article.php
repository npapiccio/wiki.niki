<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://pressapps.co
 * @since      1.0.0
 *
 * @package    Pressapps_Knowledge_Base
 * @subpackage Pressapps_Knowledge_Base/public/partials
 */

global $pakb, $pakb_helper;

//$options = get_option( 'now_hiring_options' );
	//include( plugin_dir_path( __FILE__ ) . 'now-hiring-public-display-single-' . esc_attr( $options['layout'] ) . '.php' );
?>

<ul class="pakb-list">
	<?php foreach ( $items->posts as $item ) { ?>
		<li>
			<i class="<?php echo esc_attr( $pakb_helper->get_the_icon( $item->ID ) ); ?>"></i> <a href="<?php echo get_permalink( $item->ID ); ?>"><?php echo esc_attr( $item->post_title ); ?></a>
		</li>
	<?php } ?>
</ul>
