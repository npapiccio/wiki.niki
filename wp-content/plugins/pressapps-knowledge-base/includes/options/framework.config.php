<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.

/**
 * Options Page settings
 * @var $settings
 */
$settings = array(
	'header_title' => __( 'Knowledge Base', 'pressapps-knowledge-base' ),
	'menu_title'   => __( 'Knowledge Base', 'pressapps-knowledge-base' ),
	'menu_type'    => 'add_submenu_page',
	'menu_slug'    => 'pressapps-knowledge-base',
	'ajax_save'    => false,
);

/**
 * Options sections & fields
 * @var $options
 */
$options = array();

/**
 * General Tab Section & options
 */
$options[] = array(
	'name'   => 'kb-general',
	'title'  => __( 'General', 'pressapps-knowledge-base' ),
	'icon'   => 'si-cog3',
	'fields' => array(
		array(
			'id'      => 'kb_template',
			'type'    => 'select',
			'title'   => __( 'Page Template', 'pressapps-knowledge-base' ),
			'options' => array_merge( array( 'page.php' => 'Default Template' ), wp_get_theme()->get_page_templates() ),
			'default' => 'page.php'
		),
		array(
			'id'      => 'kb_slug',
			'type'    => 'text',
			'title'   => __( 'Article Slug', 'pressapps-knowledge-base' ),
			'default' => 'knowledgebase',
		),
		array(
			'id'      => 'kbcat_slug',
			'type'    => 'text',
			'title'   => __( 'Category Slug', 'pressapps-knowledge-base' ),
			'default' => 'kb',
		),
		array(
			'type'    => 'notice',
			'class'   => 'danger',
			'content' => 'Important: Main page, article and category slugs must all be unnique!',
		),
		array(
			'id'    => 'breadcrumbs',
			'type'  => 'switcher',
			'title' => __( 'Breadcrumbs', 'pressapps-knowledge-base' )
		),
		array(
			'id'         => 'breadcrumb_text',
			'type'       => 'text',
			'title'      => __( 'Breadcrumb Text', 'pressapps-knowledge-base' ),
			'default'    => __( 'Knowledge Base', 'pressapps-knowledge-base' ),
			'dependency' => array( 'pakb_breadcrumbs', '==', 'true' )
		),
		array(
			'id'      => 'reorder',
			'type'    => 'radio',
			'title'   => __( 'Reorder', 'pressapps-knowledge-base' ),
			'options' => array(
				'default'        => __( 'Default', 'pressapps-knowledge-base' ),
				'reorder'        => __( 'Reorder', 'pressapps-knowledge-base' ),
				'alphabetically' => __( 'Alphabetically', 'pressapps-knowledge-base' )
			),
			'default' => 'default'
		),
	)
);

/**
 * Main page options
 */
$options[] = array(
	'name'   => 'kb-main',
	'title'  => __( 'Main Page', 'pressapps-knowledge-base' ),
	'icon'   => 'si-menu7',
	'fields' => array(
		array(
			'id'             => 'kb_page',
			'type'           => 'select',
			'title'          => __( 'Main Knowledge Base Page', 'pressapps-knowledge-base' ),
			'options'        => 'pages',
			'default_option' => 'Select a page'
		),
		array(
			'id'      => 'columns',
			'type'    => 'image_select',
			'title'   => __( 'Columns', 'pressapps-knowledge-base' ),
			'options' => array(
				'2' => plugin_dir_url( dirname( __FILE__ ) ) . 'img/2col.png',
				'3' => plugin_dir_url( dirname( __FILE__ ) ) . 'img/3col.png',
				'4' => plugin_dir_url( dirname( __FILE__ ) ) . 'img/4col.png',
			),
			'default' => '2col',
		),
		array(
			'id'             => 'kb_page_layout',
			'type'           => 'image_select',
			'title'          => __( 'Knowledge Base Layout', 'pressapps-knowledge-base' ),
			'options' => array(
				1 => plugin_dir_url( dirname( __FILE__ ) ) . 'img/lists.png',
				2 => plugin_dir_url( dirname( __FILE__ ) ) . 'img/boxes.png',
			),
			'default' => 1,
		),
		array(
			'id'         => 'icon_cat',
			'type'       => 'switcher',
			'title'      => __( 'Category Icon', 'pressapps-knowledge-base' ),
			'default'    => true,
		),
		array(
			'id'    => 'category_count',
			'type'  => 'switcher',
			'title' => __( 'Category Count', 'pressapps-knowledge-base' ),
			'default'    => true,
			'dependency' => array( 'pakb_kb_page_layout_1', '==', 'true' )
		),		
		array(
			'id'    => 'view_all',
			'type'  => 'switcher',
			'title' => __( 'View All Link', 'pressapps-knowledge-base' ),
			'default'    => true,
		),
		array(
			'id'    => 'view_all_count',
			'type'  => 'switcher',
			'title' => __( 'View All Count', 'pressapps-knowledge-base' ),
			'default'    => true,
			'dependency' => array( 'pakb_view_all', '==', 'true' )
		),
		array(
			'id'      => 'posts_per_cat',
			'type'    => 'number',
			'title'   => __( 'Articles Per Category', 'pressapps-knowledge-base' ),
			'default' => '5',
			'dependency' => array( 'pakb_kb_page_layout_1', '==', 'true' )
		),
		array(
			'id'      => 'box_icon_size',
			'type'    => 'number',
			'title'   => __( 'Icon Size', 'pressapps-knowledge-base' ),
			'default' => '48',
			'after'	  => 'px',
			'dependency' => array( 'pakb_kb_page_layout_2|pakb_icon_cat', '==|==', 'true|true' )
		),
		array(
			'id'      => 'cat_size',
			'type'    => 'number',
			'title'   => __( 'Category Font Size', 'pressapps-knowledge-base' ),
			'default' => '26',
			'after'	  => 'px',
		),
		array(
			'id'             => 'layout_main',
			'type'           => 'sorter',
			'title'          => __( 'Page Layout', 'pressapps-knowledge-base' ),
			'default'        => array(
				'enabled'		=> array(
					'search'	=> __( 'Search Bar', 'pressapps-knowledge-base' ),
					'main'		=> __( 'Knowledge Base', 'pressapps-knowledge-base' ),
				),
				'disabled'     => array(
					'content'	=> __( 'Page Content', 'pressapps-knowledge-base' ),
					'sidebar'	=> __( 'Sidebar', 'pressapps-knowledge-base' ),
				),
			),
			'enabled_title'  => __( 'Enabled', 'pressapps-knowledge-base' ),
			'disabled_title' => __( 'Disabled', 'pressapps-knowledge-base' ),
  		),
	)
);

/**
 * Single Tab Section & options
 */
$options[] = array(
	'name'   => 'kb-single',
	'title'  => __( 'Single Page', 'pressapps-knowledge-base' ),
	'icon'   => 'si-file-empty',
	'fields' => array(
		array(
			'id'      => 'meta_display',
			'type'    => 'radio',
			'title'   => __( 'Display Meta Info', 'pressapps-knowledge-base' ),
			'options' => array(
				0 => __( 'Disabled', 'pressapps-knowledge-base' ),
				1 => __( 'Top', 'pressapps-knowledge-base' ),
				2 => __( 'Bottom', 'pressapps-knowledge-base' ),
			),
			'default' => 2,
		),
		array(
			'id'       => 'meta',
			'type'     => 'checkbox',
			'title'    => 'Article Meta Top',
			'options'  => array(
				'updated'	=> 'Updated',
				'category'	=> 'Category',
				'tags'		=> 'Tags',
			),
			'default'  => array( 'category', 'updated', 'tags' ),
			'dependency' => array( 'pakb_meta_display_0', '!=', 'true' )
		),	
		array(
			'id'    => 'comments',
			'type'  => 'switcher',
			'title' => __( 'Comments', 'pressapps-knowledge-base' ),
			'info'  => __( 'Requires theme support', 'pressapps-knowledge-base' ),
		),
		array(
			'id'    => 'related_articles',
			'type'  => 'switcher',
			'title' => __( 'Related Articles', 'pressapps-knowledge-base' )
		)
	)
);

/**
 * Search Tab Section & options
 */
$options[] = array(
	'name'   => 'kb-search',
	'title'  => __( 'Search', 'pressapps-knowledge-base' ),
	'icon'   => 'si-search',
	'fields' => array(
		array(
			'id'      => 'search_enable',
			'type'    => 'switcher',
			'title'   => __( 'Search', 'pressapps-knowledge-base' ),
			'default' => true
		),
		array(
			'id'         => 'searchbox_placeholder',
			'type'       => 'text',
			'title'      => __( 'Placeholder', 'pressapps-knowledge-base' ),
			'default'    => 'Search Knowledge Base',
			'dependency' => array( 'pakb_search_enable', '==', 'true' )
		),
		array(
			'id'         => 'search_btn',
			'type'       => 'switcher',
			'title'      => __( 'Display Search Button', 'pressapps-knowledge-base' ),
			'default'    => true,
			'dependency' => array( 'pakb_search_enable', '==', 'true' )
		),
		array(
			'id'         => 'search_archive',
			'type'       => 'switcher',
			'title'      => __( 'Display Search on Archive Page', 'pressapps-knowledge-base' ),
			'default'    => true,
			'dependency' => array( 'pakb_search_enable', '==', 'true' )
		),
		array(
			'id'         => 'search_single',
			'type'       => 'switcher',
			'title'      => __( 'Display Search on Single Page', 'pressapps-knowledge-base' ),
			'default'    => true,
			'dependency' => array( 'pakb_search_enable', '==', 'true' )
		),
		/*
		array(
			'id'    => 'search_kb_tag',
			'type'  => 'switcher',
			'title' => __( 'Search KB tags', 'pressapps-knowledge-base' ),
			'default' => true,
			'dependency' => array( 'pakb_search_enable', '==', 'true' )
		),
		*/
		array(
			'id'         => 'live_search',
			'type'       => 'switcher',
			'title'      => __( 'Live Search', 'pressapps-knowledge-base' ),
			'default'    => true,
			'dependency' => array( 'pakb_search_enable', '==', 'true' ),
		),
		array(
			'id'         => 'search_show_cat',
			'type'       => 'switcher',
			'title'      => __( 'Display Categories in Search Results', 'pressapps-knowledge-base' ),
			'default'    => true,
			'dependency' => array( 'pakb_search_enable|pakb_live_search', '==|==', 'true|true' )
		)
	)
);

/**
 * Voting Tab Section & options
 */
$options[] = array(
	'name'   => 'kb-voting',
	'title'  => __( 'Voting', 'pressapps-knowledge-base' ),
	'icon'   => 'si-thumbs-up2',
	'fields' => array(
		array(
			'id'      => 'voting',
			'type'    => 'radio',
			'title'   => __( 'Voting', 'pressapps-knowledge-base' ),
			'options' => array(
				'0' => __( 'Disabled', 'pressapps-knowledge-base' ),
				'1' => __( 'Public Voting', 'pressapps-knowledge-base' ),
				'2' => __( 'Logged In Users Only', 'pressapps-knowledge-base' ),
			),
			'default' => '0',
		),
		array(
			'id'         => 'vote_dislike',
			'type'       => 'switcher',
			'title'      => __( 'Display Dislike Button', 'pressapps-knowledge-base' ),
			'default'    => true,
			'dependency' => array( 'pakb_voting_0', '!=', 'true' )
		),
		array(
			'id'         => 'vote_up_icon',
			'type'       => 'icon',
			'title'      => __( 'Vote Up Icon', 'pressapps-knowledge-base' ),
			'default'    => 'si-checkmark3',
			'dependency' => array( 'pakb_voting_0', '!=', 'true' )
		),
		array(
			'id'         => 'vote_down_icon',
			'type'       => 'icon',
			'title'      => __( 'Vote Down Icon', 'pressapps-knowledge-base' ),
			'default'    => 'si-cross2',
			'dependency' => array( 'pakb_voting_0|pakb_vote_dislike', '!=|==', 'true|true' )
		),
		array(
			'id'           => 'vote_reset_all',
			'type'         => 'button',
			'title'        => __( 'Reset All Votes', 'pressapps-knowledge-base' ),
			'button_title' => __( 'Reset All Votes', 'pressapps-knowledge-base' ),
			'dependency'   => array( 'pakb_voting_0', '!=', 'true' )
		),
	)
);

/**
 * Style Tab & Options fields
 */
$options[] = array(
	'name'   => 'kb-style',
	'title'  => __( 'Styling', 'pressapps-knowledge-base' ),
	'icon'   => 'si-brush',
	'fields' => array(
		array(
			'id'      => 'link_color',
			'type'    => 'color_picker',
			'title'   => __( 'Primary Link Color', 'pressapps-knowledge-base' ),
			'default' => '#03A9F4',
		),
		array(
			'id'      => 'cat_color',
			'type'    => 'color_picker',
			'title'   => __( 'Category Link Color', 'pressapps-knowledge-base' ),
			'default' => '#03A9F4',
			'info'  => __( 'Main page category links.', 'pressapps-knowledge-base' ),
		),
		array(
			'id'      => 'sec_link_color',
			'type'    => 'color_picker',
			'title'   => __( 'Secondary Link Color', 'pressapps-knowledge-base' ),
			'default' => '#A9AAAB',
			'info'  => __( 'Breadcrumbs, article meta, view all links.', 'pressapps-knowledge-base' ),
		),
		array(
			'id'      => 'list_link_color',
			'type'    => 'color_picker',
			'title'   => __( 'List Item Color', 'pressapps-knowledge-base' ),
			'default' => '#444444',
			'info'  => __( 'Main page and widget list items.', 'pressapps-knowledge-base' ),
		),
		array(
			'id'    => 'custom_css',
			'type'  => 'textarea',
			'title' => __( 'Custom CSS', 'pressapps-knowledge-base' ),
			'info'  => __( 'You can add and override stylesheets here.', 'pressapps-knowledge-base' ),
		)
	)
);

// Register Framework page settings and options fields 
SkeletFramework::instance( $settings, $options );