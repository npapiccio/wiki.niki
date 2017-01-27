<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Setting global variables.
 *
 */
global $pakb, $pakb_helper, $pakb_loop;  ?>

<?php $pakb_helper->the_search(); ?>
<?php $pakb_loop->the_breadcrumbs(); ?>
<?php

	while ( $pakb_loop->have_posts() ) : $pakb_loop->the_post();
		//will skip if the post type is not knowledgebase
		if ( get_post_type( $pakb_loop->get_the_ID() ) !== "knowledgebase" ) {
			continue;
		}
		do_action( 'pakb_search_loop' ); // action inside the loop for search ?>
		<article id="<?php echo esc_attr( 'kb-' . $pakb_loop->get_the_ID() ); ?>" class="pakb-archive"><a href="<?php $pakb_loop->the_permalink(); ?>"><i class="<?php echo esc_attr( $pakb_helper->the_icon() ); ?>"></i><?php $pakb_loop->the_title(); ?></a></article>
		<?php
	endwhile;
?>
<?php if ( ! $pakb_loop->have_posts() ) : ?>
	<div class="pakb-alert pakb-alert-warning">
		<?php _e( 'Sorry, no results were found.', 'pressapps-knowledge-base' ); ?>
	</div>
<?php endif; ?>

