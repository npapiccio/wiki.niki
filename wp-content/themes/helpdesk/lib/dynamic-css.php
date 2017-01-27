<?php
/**
 * Output dynamic CSS at bottom of HEAD
 */
add_action('wp_head','pa_output_css');

function pa_output_css() {
  global $helpdesk;

  $output = '';
                          
  $output .= '.navbar-default .navbar-nav > li > a { color: ' . $helpdesk['navbar_link_color']['regular'] . '; }';
  $output .= '.navbar-default .navbar-nav > .active > a, .navbar-default .navbar-nav > .active > a:hover, .navbar-default .navbar-nav > .active > a:focus, .navbar-default .navbar-nav > li > a:hover { color: ' . $helpdesk['navbar_link_color']['hover'] . '; }';
  
  $output .= '.dropdown-menu > .active > a, .dropdown-menu > .active > a:hover, .dropdown-menu > .active > a:focus, .dropdown-menu > li > a:hover { background-color: ' . $helpdesk['navbar_link_color']['hover'] . '; }';

  $output .= 'section .box i, section .box h3, .sidebar h3 { color: ' . $helpdesk['primary_color']['regular'] . '; }';
  $output .= '.btn-primary { background-color: ' . $helpdesk['primary_color']['regular'] . '; border-color: ' . $helpdesk['primary_color']['regular'] . '}';
  $output .= '.btn-primary:hover { background-color: ' . $helpdesk['primary_color']['hover'] . '; border-color: ' . $helpdesk['primary_color']['hover'] . '}';
  $output .= '.pagination > .active > a, .pagination > .active > span, .pagination > .active > a:hover, .pagination > .active > span:hover, .pagination > .active > a:focus, .pagination > .active > span:focus { background-color: ' . $helpdesk['primary_color']['regular'] . '; border-color: ' . $helpdesk['primary_color']['regular'] . '}';
  $output .= '.pagination > li > a, .pagination > li > a:hover { color: ' . $helpdesk['primary_color']['regular'] . ';}';
    
  if (pa_left_sidebar()) {
    $output .= ' @media (min-width: 768px) { .sidebar-primary .main { float: right; } }';
  }

  $output .= $helpdesk['custom_css'];

  if ( ! empty( $output ) ) {
      echo '<style type="text/css" id="helpdesk-css">' . $output . '</style>';
  }

}