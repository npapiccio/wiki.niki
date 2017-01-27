<?php

class PAKB_Helper {

/**
 * Gets the icon.
 *
 * @since 1.0.0
 * @return string
 */
	public function the_icon() {
		if ( get_post_format() == 'video' ) {
			return 'si-file-video';
		} elseif ( get_post_format() == 'image' ) {
			return 'si-file-picture';
		} else {
			return 'si-file-text2';
		}
	}

	/**
	 * Gets the icon with a parameter.
	 *
	 * @since 1.0.0
	 *
	 * @param $post_id - ID of the post
	 *
	 * @return string
	 */
	public function get_the_icon( $post_id ) {
		if ( get_post_format( $post_id ) == 'video' ) {
			return 'si-file-video';
		} elseif ( get_post_format( $post_id ) == 'image' ) {
			return 'si-file-picture';
		} else {
			return 'si-file-text2';
		}
	}

	/**
	 * Search function.
	 *
	 * @since 1.0.0
	 */
	public function the_search() {
		global $pakb, $pakb_loop;

		$search_ptxt          = trim( strip_tags( $pakb->get( 'searchbox_placeholder' ) ) );
		$search_enabled       = false;

		//search option
		$layout_main = $pakb->get('layout_main');

		if (array_key_exists('search', $layout_main['enabled'])) {
			$search_main = true;
		} else {
			$search_main = false;
		}
		$search_archive = $this->filtered_string( $pakb->get( 'search_archive' ) );
		$search_single  = $this->filtered_string( $pakb->get( 'search_single' ) );

		if ( $pakb->get( 'search_enable' ) ) {

			if ( $pakb_loop->is_archive() ) {

				if ( ( $search_archive && ! is_post_type_archive( 'knowledgebase' ) ) || ( is_search() && $search_main ) ) {
					$search_enabled = true;
				}

			} elseif ( $pakb_loop->is_single() ) {

				if ( $search_single ) {
					$search_enabled = true;
				}

			} else {

				if ( $search_main ) {
					$search_enabled = true;
				}

			}

		}

		if ( $search_enabled ) { ?>
			<div class="pakb-header">
				<form role="search" method="post" id="kbsearchform" action="<?php echo home_url( '/' ); ?>">
					<div class="pakb-search">
						<input type="text" value="<?php if ( is_search() ) { echo get_search_query(); } ?>" name="s" placeholder="<?php echo ( ! empty( $search_ptxt ) ) ? $search_ptxt : ''; ?>" id="kb-s" class="<?php echo ( $pakb->get( 'live_search' ) ) ? 'autosuggest' : ''; ?><?php echo ( !$pakb->get( 'search_btn' ) ) ? ' pakb-search-icon' : ''; ?>"/><?php if ( $pakb->get( 'search_btn' ) ) { ?><span><input type="submit" id="searchsubmit" value="<?php echo esc_attr__( 'Search', 'pressapps-knowledge-base' ); ?>"/></span><?php } ?>
						<input type="hidden" name="post_type" value="knowledgebase"/>
						<?php wp_nonce_field( 'knowedgebase-search', 'search_nonce', false ); ?>
					</div>
				</form>
			</div>
		<?php
		}
	}

	/**
	 * Includes the file.
	 *
	 * @since 1.0.0
	 *
	 * @param $filename
	 *
	 * @return string
	 */
	public function load_file( $filename ) {
		ob_start();
		include $filename;

		return ob_get_clean();
	}

	/**
	 * Getting template files.
	 *
	 * @since 1.0.0
	 *
	 * @param string $case
	 *
	 * @return string
	 */
	public function get_template_files( $case = 'single' ) {

		$default_path = plugin_dir_path( dirname( __FILE__ ) ) . 'partials/';
		$theme_path   = get_stylesheet_directory() . '/pakb/';

		switch ( $case ) {
			case 'search':
				$filename = 'knowledgebase-search.php';
				break;
			case 'archive':
				$filename = 'knowledgebase-archive.php';
				break;
			case 'single':
			default :
				$filename = 'knowledgebase-single.php';
				break;
			case 'category':
				$filename = 'knowledgebase-category.php';
				break;
			case 'knowledgebase':
				$filename = 'knowledgebase.php';
				break;
		}

		$default_file = $default_path . $filename;
		$theme_file   = $theme_path . $filename;

		return ( ( file_exists( $theme_file ) ) ? $theme_file : $default_file );
	}

	/**
	 * Overriding variable.
	 *
	 * @since 1.0.0
	 */
	public function override_is_var() {
		global $wp_query;

		$wp_query->is_tax               = false;
		$wp_query->is_archive           = false;
		$wp_query->is_search            = false;
		$wp_query->is_single            = false;
		$wp_query->is_post_type_archive = false;

		$wp_query->is_404 = false;

		$wp_query->is_singular = true;
		$wp_query->is_page     = true;
	}

	/**
	 * Template function for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function page_template( $template ) {
		global $pakb;

		$kb_page = get_post( $pakb->get( 'kb_page' ) );

		$id       = $kb_page->ID;
		$template = get_page_template_slug( $kb_page->ID );
		$pagename = $kb_page->post_name;

		$templates = array();

		if ( $template && 0 === validate_file( $template ) ) {
			$templates[] = $template;
		}

		//check if skelet option page template has been set and will use that template
		if ( $pakb->get( 'kb_template' ) && $pakb->get( 'kb_template' ) !== 'page.php' ) {
			$template_name = str_replace( '.php', '', basename( $pakb->get( 'kb_template' ) ) );

			$templates[] = $pakb->get( 'kb_template' );

			return get_query_template( $template_name, $templates );

		} else {
			if ( $pagename ) {
				$templates[] = "page-$pagename.php";
			}
			if ( $id ) {
				$templates[] = "page-$id.php";
			}

			$templates[] = 'page.php';

			return get_query_template( 'page', $templates );
		}



	}

	/**
	 * Post data function.
	 *
	 * @since 1.0.0
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function get_dummy_post_data( $args ) {

		return array_merge( array(
			'ID'                    => 0,
			'post_status'           => 'publish',
			'post_author'           => 0,
			'post_parent'           => 0,
			'post_type'             => 'page',
			'post_date'             => 0,
			'post_date_gmt'         => 0,
			'post_modified'         => 0,
			'post_modified_gmt'     => 0,
			'post_content'          => '',
			'post_title'            => '',
			'post_excerpt'          => '',
			'post_content_filtered' => '',
			'post_mime_type'        => '',
			'post_password'         => '',
			'post_name'             => '',
			'guid'                  => '',
			'menu_order'            => 0,
			'pinged'                => '',
			'to_ping'               => '',
			'ping_status'           => '',
			'comment_status'        => 'closed',
			'comment_count'         => 0,
			'filter'                => 'raw',
		), $args );
	}

	/**
	 * Function for casting votes.
	 *
	 * @since 1.0.0
	 *
	 * @param bool|false $is_ajax
	 */
	public function the_votes( $is_ajax = false ) {

		global $post, $pakb;
		$votes_like        = (int) get_post_meta( $post->ID, '_votes_likes', true );
		$votes_dislike     = (int) get_post_meta( $post->ID, '_votes_dislikes', true );
		$voted_like        = sprintf( _n( '%s person found this helpful', '%s people found this helpful', $votes_like, 'pressapps-knowledge-base' ), $votes_like );
		$voted_dislike     = sprintf( _n( '%s person did not find this helpful', '%s people did not find this helpful', $votes_dislike, 'pressapps-knowledge-base' ), $votes_dislike );
		$vote_like_link    = __( "I found this helpful", 'pressapps-knowledge-base' );
		$vote_dislike_link = __( "I did not find this helpful", 'pressapps-knowledge-base' );
		$cookie_vote_count = '';

		if ( isset( $_COOKIE['vote_count'] ) ) {
			$cookie_vote_count = @unserialize( base64_decode( $_COOKIE['vote_count'] ) );
		}

		if ( ! is_array( $cookie_vote_count ) && isset( $cookie_vote_count ) ) {
			$cookie_vote_count = array();
		}

		echo( ( $is_ajax ) ? '' : '<div class="votes">' );
		if ( is_user_logged_in() || $pakb->get( 'voting' ) == 1 ) :

			if ( is_user_logged_in() ) {
				$vote_count = (array) get_user_meta( get_current_user_id(), 'vote_count', true );
			} else {
				$vote_count = $cookie_vote_count;
			}

			if ( ! in_array( $post->ID, $vote_count ) ) {

				echo '<a title="' . esc_attr( $vote_like_link ) . '" class="pakb-like-btn pakb-tooltip" href="#" onclick="return false" post_id="' . esc_attr( $post->ID ) . '"><i class="'. esc_attr( $pakb->get( 'vote_up_icon' ) ) .'"></i> <span class="count">' . esc_html( $votes_like ) . '</span></a>';
				if ( $pakb->get( 'vote_dislike' ) ) {
					echo '<a title="' . esc_attr( $vote_dislike_link ) . '" class="pakb-dislike-btn pakb-tooltip" href="#" onclick="return false" post_id="' . esc_attr( $post->ID ) . '"><i class="'. esc_attr( $pakb->get( 'vote_down_icon' ) ) .'"></i> <span class="count">' . esc_html( $votes_dislike ) . '</span></a>';
				}
			} else {
				// already voted
				echo '<p title="' . esc_attr( $voted_like ) . '" class="pakb-like-btn pakb-tooltip"><i class="'. esc_attr( $pakb->get( 'vote_up_icon' ) ) .'"></i> <span class="count">' . esc_html( $votes_like ) . '</span></p>';
				if ( $pakb->get( 'vote_dislike' ) ) {
					echo '<p title="' . esc_attr( $voted_dislike ) . '" class="pakb-dislike-btn pakb-tooltip"><i class="'. esc_attr( $pakb->get( 'vote_down_icon' ) ) .'"></i> <span class="count">' . esc_html( $votes_dislike ) . '</span></p>';
				}
			}

		else :
			// not logged in
			echo '<p title="' . esc_attr( $voted_like ) . '" class="pakb-like-btn pakb-tooltip"><i class="'. esc_attr( $pakb->get( 'vote_up_icon' ) ) .'"></i> <span class="count">' . esc_html( $votes_like ) . '</span></p>';
			if ( $pakb->get( 'vote_dislike' ) ) {
				echo '<p title="' . esc_attr( $voted_dislike ) . '" class="pakb-dislike-btn pakb-tooltip"><i class="'. esc_attr( $pakb->get( 'vote_down_icon' ) ) .'"></i> <span class="count">' . esc_html( $votes_dislike ) . '</span></p>';
			}
		endif;
		echo( ( $is_ajax ) ? '' : '</div>' );
	}

	/**
	 * Filter function to fixed Array to string conversion notice
	 *
	 * @param  string $string
	 *
	 * @param string  $default
	 *
	 * @return string
	 */
	public function filtered_string( $string, $default = "" ) {
		if ( is_string( $string ) && strtolower( $string ) === 'array' ) {
			empty( $default ) ? $string = "" : $string = $default;
		} elseif ( is_array( $string ) ) {
			empty( $default ) ? $string = "" : $string = $default;
		}

		return $string;
	}

	/**
	 * Display related articles on single post
	 *
	 * @param $id
	 */
	public function display_related_articles( $id ) {

		$taxonomy   = 'knowledgebase_category';
		$post_terms = wp_get_post_terms( $id, $taxonomy );
		$post_array = array();

		if ( ! is_wp_error( $post_terms ) ) {

			foreach ( $post_terms as $post_term ) {
				$args = $args = array(
					'post_type'      => 'knowledgebase',
					'posts_per_page' => 6,
					'tax_query'      => array(
						array(
							'taxonomy' => $taxonomy,
							'field'    => 'term_id',
							'terms'    => $post_term->term_id
						)
					)
				);
				$post_array_objects = get_posts( $args );
				foreach ( $post_array_objects as $post_array_object ) {
					$post_array[] = $post_array_object->ID;
				}
			}
			printf( '<h2>%s</h2>', __( 'Related Articles', 'pressapps-knowledge-base' ) );

			//will check if the post id exist in the array and will remove
			$array_key = array_search( $id, $post_array );

			if ( $array_key ) {
				unset( $post_array[ $array_key ] );
				$post_array = array_values( $post_array );
			}

			echo '<ul class="pakb-list pakb-related">';
			foreach ( $post_array as $index => $post_id ) {
				//will skip or is greater than 6
				$post_object = get_post( $post_id ); ?>
				<li>
				<?php
				printf( '<i class="%s"></i> <a href="%s">%s</a>',esc_attr( $this->get_the_icon( $post_object->ID ) ), get_permalink( $post_object->ID ), esc_html( $post_object->post_title ) ); ?>
				</li>
			<?php }
			echo '</ul>';
		}
	}

	/**
	 * Helper function to check on the reorder option and return a specific orderby
	 *
	 * @param bool|false $is_category
	 *
	 * @return string
	 */
	public function reorder_option( $is_category = false ) {
		global $pakb;

		switch ( $pakb->get( 'reorder' ) ) {
			case 'default':
				$orderby = 'date';
				break;
			case 'reorder':
				$orderby = ( $is_category ) ? 'term_group' : 'menu_order';
				break;
			case 'alphabetically':
				$orderby = ( $is_category ) ? 'name' : 'title';
				break;
			default:
				$orderby = 'date';
				break;
		}

		return $orderby;
	}

}