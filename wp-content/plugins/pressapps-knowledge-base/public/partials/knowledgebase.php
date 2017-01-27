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

<?php
$i         = 0;
$skip      = true;
$page_link = get_page_link();

if ( $pakb->get( 'columns' ) ) {
	$columns = $pakb->get( 'columns' );
} else {
	$columns = 2;
}

if ( $pakb->get( 'kb_page_layout' ) == 2 ) {
	$class = 'pakb-boxes';
} else {
	$class = 'pakb-lists';
}

?>

<?php //$pakb_helper->the_search(); ?>

<?php $pakb_loop->the_breadcrumbs(); ?>

<?php 
$layout_main = $pakb->get('layout_main');
$layout = $layout_main['enabled'];

if ($layout): foreach ($layout as $key=>$value) {
 
    switch($key) {

        case 'search':
        	$pakb_helper->the_search();
	        break;

        case 'content':
			if ( $pakb_loop->is_kbpage() ) {
				while ( have_posts() ) : the_post();
					echo '<div class="pakb-main-content">';
					the_content();
					echo '</div>';
				endwhile;
			}
	        break;
 
        case 'main':
        	?>
			<div class="pakb-main <?php echo $class; ?>">

				<?php
				foreach ( $pakb_loop->get_cats() as $cat ){
				$pakb_loop->setup_cat( $cat );
				if ( ! $pakb_loop->subcat_have_posts() ) {
					continue;
				}
				?>

				<?php
				if ( $i ++ % $columns == 0 && $skip ){
				?>
				<div class="pakb-row">
					<?php
					}
					$skip = true;
					?>

					<?php

					$pakb_loop->print_the_cat();
					?>

					<?php
					if ( $i % $columns == 0 ) {
						echo '</div>';
					}
					?>

					<?php
					}
					?>

				<?php
				if ( $i % $columns != 0 ) {
					echo "</div>";
				}
				?>

			</div>
        	<?php
	        break;
 
        case 'sidebar':
        	if ( $pakb_loop->is_kbpage() ) {
        	?>
			<div class="pakb-sidebar pakb-sidebar-home">
				<div class="pakb-row">
					<?php dynamic_sidebar('pakb-main'); ?>
				</div>
			</div>
			<?php
			}
	        break;
  
    }
 
}
endif;

?>