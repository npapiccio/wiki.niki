<?php 
if(!class_exists("SkeletWidgetWalkerArticles")){
    class SkeletWidgetWalkerArticles{

        public $widget_option_settings = array();

        function __construct($args,$instance){

			global $post, $pakb_helper;

			$args['post_type'] 		= 'knowledgebase';
			$args['post_status'] 	= 'publish';
			$args['orderby'] 		= $instance['orderby'];
			$args['order'] 			= $instance['order'];
			$args['posts_per_page'] = $instance['posts_per_page'];

			if ( $instance['filter'] == 'category' ) {
				$args['tax_query'] 		= array(
					array(
						'taxonomy'         => 'knowledgebase_category',
						'field'            => 'ID',
						'terms'            => $instance['id'],
						'include_children' => true
					)
				);
			} elseif ( $instance['filter'] == 'tag' ) {
				$args['tax_query'] 		= array(
					array(
						'taxonomy'         => 'knowledgebase_tags',
						'field'            => 'ID',
						'terms'            => array( $instance['id'] ),
						'include_children' => true
					)
				);
			}

			$items = new WP_Query( $args );

			if ( 0 == $items->found_posts ) {

				_e( 'There are no knowledge base articles.', 'pressapps-knowledge-base' );

			} else {

				include( plugin_dir_path( __FILE__ ) . 'partials/pressapps-knowledge-base-list-article.php' );

			}

			wp_reset_postdata();

        }
    }
}



