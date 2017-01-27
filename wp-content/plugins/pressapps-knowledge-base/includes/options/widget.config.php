<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

$options       = array();

$options[]            = array(
	'name'              => 'knowledge_base_categories',
	'title'             => __( 'Knowledge Base Categories', 'pressapps-knowledge-base' ),
	'settings'          => array(

		array(
		  'name'          => 'title',
		  'control'       => array(
		    'label'       => __( 'Title', 'pressapps-knowledge-base' ),
		    'type'        => 'text',
		  ),
		),

		array(
		  'name'          => 'number',
		  'default' 	  => '10',
		  'control'       => array(
		    'label'       => __( 'Number of Categories', 'pressapps-knowledge-base' ),
		    'type'        => 'number',
		  ),
		),

		array(
		  'name'          => 'orderby',
		  'default'       => 'name',
		  'control'       => array(
		    'label'       => __( 'Order By', 'pressapps-knowledge-base' ),
		    'type'        => 'radio',
		    'options'     => array(
		      'name'      	=> __( 'Title', 'pressapps-knowledge-base' ),
		      'term_group'  => __( 'Reorder', 'pressapps-knowledge-base' ),
		    )
		  ),
		),

		array(
		  'name'          => 'order',
		  'default'       => 'ASC',
		  'control'       => array(
		    'label'       => __( 'Order', 'pressapps-knowledge-base' ),
		    'type'        => 'radio',
		    'options'     => array(
		      'ASC'       	=> __( 'Ascending', 'pressapps-knowledge-base' ),
		      'DESC'      	=> __( 'Descending', 'pressapps-knowledge-base' ),
		    )
		  ),
		),

		array(
		  'name'          => 'count',
		  'default'       => 0,
		  'control'       => array(
		    'label'       => __( 'Display Article Count', 'pressapps-knowledge-base' ),
		    'type'        => 'radio',
		    'options'     => array(
		      1			=> __( 'Yes', 'pressapps-knowledge-base' ),
		      0			=> __( 'No', 'pressapps-knowledge-base' ),
		    )
		  ),
		),

    ),

	"frontend_tpl" => array(
		"walker_class" => array(
		    "name"   => "SkeletWidgetWalkerCategories",
		    "path"   => plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . "public/class-pressapps-knowledge-base-widget-categories.php",
		)
	)
 
);

$options[]            = array(
	'name'              => 'knowledge_base_articles',
	'title'             => __( 'Knowledge Base Articles', 'pressapps-knowledge-base' ),
	'settings'          => array(

		array(
		  'name'          => 'title',
		  'control'       => array(
		    'label'       => __( 'Title', 'pressapps-knowledge-base' ),
		    'type'        => 'text',
		  ),
		),

		array(
		  'name'          => 'posts_per_page',
		  'default' 	  => '10',
		  'control'       => array(
		    'label'       => __( 'Number of Articles', 'pressapps-knowledge-base' ),
		    'type'        => 'number',
		  ),
		),

		array(
		  'name'          => 'orderby',
		  'default'       => 'date',
		  'control'       => array(
		    'label'       => __( 'Order By', 'pressapps-knowledge-base' ),
		    'type'        => 'radio',
		    'options'     => array(
		      'date'		=> __( 'Date', 'pressapps-knowledge-base' ),
		      'title'      	=> __( 'Title', 'pressapps-knowledge-base' ),
		      'menu_order'  => __( 'Reorder', 'pressapps-knowledge-base' ),
		    )
		  ),
		),

		array(
		  'name'          => 'order',
		  'default'       => 'DESC',
		  'control'       => array(
		    'label'       => __( 'Order', 'pressapps-knowledge-base' ),
		    'type'        => 'radio',
		    'options'     => array(
		      'ASC'      	=> __( 'Ascending', 'pressapps-knowledge-base' ),
		      'DESC'      	=> __( 'Descending', 'pressapps-knowledge-base' ),
		    )
		  ),
		),

		array(
		  'name'          => 'filter',
		  'control'       => array(
		    'label'       => __( 'Filter', 'pressapps-knowledge-base' ),
		    'type'        => 'select',
		    'options'     => array(
		      ''          => __( 'None', 'pressapps-knowledge-base' ),
		      'category'    => __( 'Filter by Category', 'pressapps-knowledge-base' ),
		      'tag'      	=> __( 'Filter by Tag', 'pressapps-knowledge-base' ),
		    )
		  ),
		  
		),

		array(
		  'name'          => 'id',
		  'control'       => array(
		    'label'       => __( 'Category or Tag ID', 'pressapps-knowledge-base' ),
		    'type'        => 'text',
		  ),
		),

    ),

	"frontend_tpl" => array(
		"walker_class" => array(
		    "name"   => "SkeletWidgetWalkerArticles",
		    "path"   => plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . "public/class-pressapps-knowledge-base-widget-articles.php",
		)
	)
 
);

$options[]            = array(
	'name'              => 'knowledge_base_search',
	'title'             => __( 'Knowledge Base Search', 'pressapps-knowledge-base' ),
	'settings'          => array(

		array(
		  'name'          => 'title',
		  'control'       => array(
		    'label'       => __( 'Title', 'pressapps-knowledge-base' ),
		    'type'        => 'text',
		  ),
		),

    ),

	"frontend_tpl" => array(
		"walker_class" => array(
		    "name"   => "SkeletWidgetWalkerSearch",
		    "path"   => plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . "public/class-pressapps-knowledge-base-widget-search.php",

		)
	)
 
);
SkeletFramework_Widget::instance($options);
