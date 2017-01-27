<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Setting global variables.
 *
 */
global $pakb, $pakb_helper, $pakb_loop; 
$meta = 			$pakb->get( 'meta' );
$meta_display = 	$pakb->get( 'meta_display' );
?>

<?php $pakb_helper->the_search(); ?>
<?php $pakb_loop->the_breadcrumbs(); ?>

<?php
while ( $pakb_loop->have_posts() ) : $pakb_loop->the_post();
	do_action( 'pakb_single_loop' ); // action inside the loop for single page ?>
	<article class="pakb-single">
		<?php if ( !empty($meta_display) && $meta_display == 1 ) { ?>
			<div class="pakb-meta">
				<?php if ( !empty($meta) && in_array( 'updated', $meta ) ) { ?>
					<time class="updated published" datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php printf( __( 'Last Updated: %s ago', 'pressapps-knowledge-base' ), human_time_diff( get_the_modified_date( 'U' ), current_time( 'timestamp' ) ) ); ?></time> <?php } ?><?php if ( !empty($meta) && in_array( 'category', $meta ) ) { ?>
					<?php $pakb_loop->the_category(); ?>
				<?php } ?>
				<?php if ( !empty($meta) && in_array( 'tags', $meta ) ) { ?>
					<?php $pakb_loop->the_tags(); ?>
				<?php } ?>
			</div>
		<?php } ?>
		<div class="pakb-content">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail();
			}
			?>
			<?php $pakb_loop->the_content(); ?>
		</div>
		<?php if ( !empty($meta_display) && $meta_display == 2 ) { ?>
			<div class="pakb-meta">
				<?php if ( !empty($meta) && in_array( 'updated', $meta ) ) { ?>
					<time class="updated published" datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php printf( __( 'Last Updated: %s ago', 'pressapps-knowledge-base' ), human_time_diff( get_the_modified_date( 'U' ), current_time( 'timestamp' ) ) ); ?></time> <?php } ?><?php if ( !empty($meta) && in_array( 'category', $meta ) ) { ?>
					<?php $pakb_loop->the_category(); ?>
				<?php } ?>
				<?php if ( !empty($meta) && in_array( 'tags', $meta ) ) { ?>
					<?php $pakb_loop->the_tags(); ?>
				<?php } ?>
			</div>
		<?php } ?>
		<?php
		if ( $pakb->get( 'voting' ) != 0 && ! $pakb_loop->post_password_required() ) {
			$this->the_votes();
		} ?>
		<?php
		if ( $pakb->get( 'related_articles' ) ) {
			$pakb_helper->display_related_articles( $pakb_loop->get_the_ID() );
		}
		?>
		<?php 
		if ( $pakb->get( 'comments' ) ) {
			comments_template(); 
		}
		?>

	</article>
	<?php
endwhile;
?>