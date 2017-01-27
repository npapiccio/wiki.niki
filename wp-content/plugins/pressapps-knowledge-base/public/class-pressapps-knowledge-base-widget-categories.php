<?php 
if(!class_exists("SkeletWidgetWalkerCategories")){
    class SkeletWidgetWalkerCategories{

        public $widget_option_settings = array();

        function __construct($args,$instance){

			global $pakb;

			$display_count 			= $instance['count'];

			$args['hide_empty']		= 1;
			$args['order'] 			= $instance['order'];
			$args['orderby'] 		= $instance['orderby'];
			$args['number'] 		= $instance['number'];
			$args['parent'] 		= 0;

			$terms = get_terms( 'knowledgebase_category', $args );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {

			    echo '<ul class="pakb-list">';

			    foreach ( $terms as $term ) {
					if ( $pakb->get( 'icon_cat' ) ) {
						$term_meta = $pakb->get_taxonomy('knowledgebase_category',$term->term_id);
						$icon = '<i class="' . $term_meta['icon'] . '"></i> ';
					} else {
						$icon = '';
					}
					if ( $display_count ) {
						$count = ' (' . $term->count . ')';
					} else {
						$count = '';
					}
			    	echo '<li>' . $icon . '<a href="' . get_term_link( $term ) . '" title="' . sprintf( __( 'View all %s articles', 'pressapps-knowledge-base' ), $term->name ) . '">' . $term->name . $count . '</a></li>';
			    }

			    echo '</ul>';
			}

        }
    }
}

