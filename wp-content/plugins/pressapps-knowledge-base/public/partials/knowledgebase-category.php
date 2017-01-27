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

if ( $pakb->get( 'columns' ) ) {
	$columns = $pakb->get( 'columns' );
} else {
	$columns = 2;
}

$description = $pakb_loop->get_cat_description();

if ( $pakb->get( 'icon_cat' ) ) {
	$tax_meta = $pakb->get_taxonomy('knowledgebase_category',$pakb_loop->get_cat_id());
	$icon = $tax_meta['icon'];
	//$icon = '<i class="si-folder4"></i> ';
} else {
	$icon = '';
}
?>

<?php if ( $pakb->get( 'kb_page_layout' ) == 2 ) { ?>
	<div class="<?php echo esc_attr( 'pakb-col-' . $columns ); ?>">
		<a class="pakb-box" href="<?php echo esc_url( $pakb_loop->get_cat_link() ); ?>">
			<?php if ( !empty($icon) ) { ?>
				<p class="pakb-box-icon"><?php echo '<i class="' . $icon . '"></i> '; ?></p>
			<?php } ?>
			<h2><?php $pakb_loop->the_cat_name(); ?></h2>
			<?php if ( !empty( $description ) ) { ?>
				<p class="pakb-box-desc"><?php $pakb_loop->the_cat_description(); ?></p>
			<?php } ?>
			<?php if ( $pakb->get( 'view_all' ) ) { ?><p class="pakb-view-all"><?php _e( 'View All', 'pressapps-knowledge-base'); ?><?php echo ( $pakb_loop->is_view_all_count_enabled() ? $pakb_loop->get_the_cat_count(' ','') : '' ); ?></p><?php } ?>
		</a>
	</div>
<?php } else { ?>
	<div class="<?php echo esc_attr( 'pakb-col-' . $columns ); ?>">
		<h2><?php echo '<i class="' . $icon . '"></i> '; ?><a href="<?php echo esc_url( $pakb_loop->get_cat_link() ); ?>"><?php $pakb_loop->the_cat_name(); ?> <?php echo ( $pakb_loop->is_cat_count_enabled() ? $pakb_loop->get_the_cat_count('(',')') : '' ); ?></a></h2>
		<ul class="pakb-list">
			<?php
			while ($pakb_loop->subcat_have_posts() ) {
				$pakb_loop->subcat_the_post();
				do_action( 'pakb_category_loop' ); // action inside the loop for category page
				?>
				<li><i class="<?php echo esc_attr( $pakb_helper->the_icon() ); ?>"></i> <a href="<?php echo esc_url( $pakb_loop->subcat_get_the_permalink() ); ?>"><?php $pakb_loop->subcat_the_title(); ?></a></li>
				<?php
			}
			?>
		</ul>
		<?php if ( $pakb->get( 'view_all' ) ) { ?><p class="pakb-view-all"><a href="<?php $pakb_loop->the_cat_link(); ?>"><?php _e( 'View All', 'pressapps-knowledge-base'); ?><?php echo ( $pakb_loop->is_view_all_count_enabled() ? $pakb_loop->get_the_cat_count(' ','') : '' ); ?></a></p><?php } ?>
	</div>
<?php } ?>
