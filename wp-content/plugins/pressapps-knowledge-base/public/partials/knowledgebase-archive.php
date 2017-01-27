<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * Setting global variables.
 *
 */
global $pakb, $pakb_helper, $pakb_loop; ?>

<?php $pakb_helper->the_search(); ?>
<?php $pakb_loop->the_breadcrumbs(); ?>

<?php
    while( $pakb_loop->have_posts() ) : $pakb_loop->the_post();
        do_action( 'pakb_archive_loop' ); // action inside the loop for archive page
?>
    <article id="<?php echo esc_attr( 'kb-' . $pakb_loop->get_the_ID() ); ?>" class="pakb-archive"><a href="<?php echo esc_url( $pakb_loop->get_the_permalink() ); ?>"><i class="<?php echo esc_attr( $pakb_helper->the_icon() ); ?>"></i> <?php $pakb_loop->the_title(); ?><?php //$pakb_helper->vote_ui(); ?></a></article>
<?php
    endwhile;
?>