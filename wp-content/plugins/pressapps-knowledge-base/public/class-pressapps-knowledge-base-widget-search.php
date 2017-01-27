<?php 
if(!class_exists("SkeletWidgetWalkerSearch")){
    class SkeletWidgetWalkerSearch{

        function __construct($args,$instance){

			global $pakb;
			
			$search_ptxt = trim( strip_tags( $pakb->get( 'searchbox_placeholder' ) ) );
			
			if ( $pakb->get( 'kb_page' ) ) {
				$page_link = get_permalink( $pakb->get( 'kb_page' ) );
			} else {
				echo '<p>' . __( 'Knowledge Base page not set under PressApps > Knowledge Base', 'pressapps-knowledge-base' ) . '</p>';
			}

			?>
			<form role="search" method="get" id="kbsearchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="text" placeholder="<?php echo ( ! empty( $search_ptxt ) ) ? $search_ptxt : ''; ?>" value="<?php echo esc_attr( is_search() ? get_search_query() : '' ); ?>" name="s" id="s"/>
				<input type="hidden" name="post_type" value="knowledgebase"/>
			</form>
			<?php
		}

    }
}
