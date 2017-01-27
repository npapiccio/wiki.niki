<?php
/**
 * Theme initial setup and constants
 */
function pa_theme_setup() {
  // Make theme available for translation
  load_theme_textdomain('pressapps', get_template_directory() . '/lang');

  // Register wp_nav_menu() menus
  register_nav_menus(array(
    'primary_navigation_left' => __('Primary Navigation Left', 'pressapps'),
    'primary_navigation_right' => __('Primary Navigation Right', 'pressapps'),
    'footer_navigation' => __('Footer Navigation', 'pressapps'),
  ));

  // Add post thumbnails
  add_theme_support('post-thumbnails');

  // Add post formats
  add_theme_support('post-formats', array('image', 'video', 'audio'));

  // Add HTML5 markup for captions
  add_theme_support('html5', array('caption', 'comment-form', 'comment-list'));
  add_theme_support( 'automatic-feed-links' );
  
  // Tell the TinyMCE editor to use a custom stylesheet
  add_editor_style('/assets/css/editor-style.css');
}
add_action('after_setup_theme', 'pa_theme_setup');

/**
 * Register sidebars
 */
function pa_sidebars_init() {
  register_sidebar(array(
    'name'          => __('Primary', 'pressapps'),
    'id'            => 'sidebar-primary',
    'before_widget' => '<div class="widget %1$s %2$s">',
    'after_widget'  => '</div>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>',
  ));

  register_sidebar(array(
    'name'          => __('Home Page', 'pressapps'),
    'id'            => 'sidebar-home',
    'before_widget' => '<div class="widget %1$s %2$s'. pa_count_widgets( 'sidebar-home' ) .' half-gutter-col"><div class="widget-inner">',
    'after_widget'  => '</div></div>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>',
  ));

  register_sidebar(array(
    'name'          => __('Footer', 'pressapps'),
    'id'            => 'sidebar-footer',
    'before_widget' => '<div class="widget %1$s %2$s'. pa_count_widgets( 'sidebar-footer' ) .'">',
    'after_widget'  => '</div>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>',
  ));

}
add_action('widgets_init', 'pa_sidebars_init');

/**
 * Count number of widgets in a sidebar
 */
function pa_count_widgets( $sidebar_id, $count = FALSE ) {
  // If loading from front page, consult $_wp_sidebars_widgets rather than options
  // to see if wp_convert_widget_settings() has made manipulations in memory.
  global $_wp_sidebars_widgets;
  if ( empty( $_wp_sidebars_widgets ) ) :
    $_wp_sidebars_widgets = get_option( 'sidebars_widgets', array() );
  endif;

  $sidebars_widgets_count = $_wp_sidebars_widgets;

  if ( isset( $sidebars_widgets_count[ $sidebar_id ] ) && count( $sidebars_widgets_count[ $sidebar_id ] ) > 0 ) :
    $widget_count = count( $sidebars_widgets_count[ $sidebar_id ] );
    $col = ceil(12 / $widget_count);
    $widget_classes = ' col-sm-' . $col;
    if ($count) {
      return $widget_count;
    } else {
      return $widget_classes;
    }
  endif;
}

