<?php

$redux_opt_name = "helpdesk";

if ( !function_exists( "redux_add_metaboxes" ) ):
    function redux_add_metaboxes($metaboxes) {

    $boxLayout = array();
    $boxLayout[] = array(
        'icon_class' => 'icon-large',
        'icon' => 'el-icon-home',
        'fields' => array(
            array(
                'desc'      => __( 'Select main content and sidebar arrangement.', 'redux-framework-demo' ),
                'id'        => 'layout',
                'type'      => 'image_select',
                'customizer'=> array(),
                'options'   => array( 
                1           => ReduxFramework::$_url . 'assets/img/1c.png',
                2           => ReduxFramework::$_url . 'assets/img/2cl.png',
                3           => ReduxFramework::$_url . 'assets/img/2cr.png',
                )
            ),
            array(
                'id' => 'sidebar',
                'title' => __( 'Sidebar', 'fusion-framework' ),
                'desc' => 'Select custom sidebar, if left blank default "Primary" sidebar is used. You can create custom sidebars under Appearance > Widgets.',
                'type' => 'select',
                'required' => array('layout','>','1'),       
                'data' => 'sidebars',
                'default' => 'None',
            ),
        )
    );
  
    $metaboxes[] = array(
        'id' => 'layout',
        'post_types' => array('page'),
        'position' => 'side', // normal, advanced, side
        'priority' => 'high', // high, core, default, low
        'sections' => $boxLayout
    );
/*
    $homeTemplate = array();
    $homeTemplate[] = array(
        'title' => __('Hero Section', 'redux-framework-demo'),
        'icon_class' => 'icon-large',
        'icon' => 'el-icon-minus',
        'fields' => array(
            array(
                'id'       => 'hero_bg',
                'type'     => 'background',
                'output'   => array( '.section-hero' ),
                'title'    => __( 'Background', 'redux-framework-demo' ),
                'default'  => array(
                    'background-color' => '#00aff0',
                ),
            ),
            array(
                'id'        => 'hero_bg_overlay',
                'type'      => 'color_rgba',
                'title'     => 'Background Overlay',
                'output'    => array('.section-hero:before'),
                'transparent'   => false,
                'mode'      => 'background-color'
            ),
            array(
                'id'       => 'hero_padding',
                'type'     => 'spacing',
                'output'   => array( '.section-hero' ),
                'mode'     => 'padding',
                'right'         => false,
                'left'          => false,
                'units'         => 'px',
                'title'    => __( 'Padding', 'redux-framework-demo' ),
                'default'  => array(
                    'padding-top'    => '90px',
                    'padding-bottom' => '90px',
                )
            ),
            array(
                'id'       => 'hero_text',
                'type'     => 'color',
                'title'    => __( 'Text Color', 'redux-framework-demo' ),
                'default'  => '#ffffff',
                'transparent' => false,
                'validate' => 'color',
                'output'    => array(
                    'color' => '.section-hero h1, .section-hero h4'
                ),
            ),
            array(
                'id'       => 'subtitle',
                'type'     => 'text',
                'title'    => __( 'Subtitle', 'redux-framework-demo' ),
            ),
            array(
                'id' => 'headline_search',
                'type' => 'button_set',
                'title'       => __( 'Search', 'shoestrap' ),
                'desc'        => __( 'Display a search form in the hero section.', 'shoestrap' ),
                'options'   => array(
                    '0' => 'Disabled',
                    '1' => 'WP Search',
                    '2' => 'Live Search',
                ),
            ),
            array(
                'id'       => 'top_searches',
                'type'     => 'switch',
                'title'    => __( 'Top Searched Terms', 'redux-framework-demo' ),
                'desc'     => __( 'Display top searched terms under the search field.', 'redux-framework-demo' ),
                'default'  => 1
            ),
            array(
                'id'       => 'top_searches_title',
                'type'     => 'text',
                'title'    => __( 'Title', 'redux-framework-demo' ),
                'required'    => array('top_searches','=',array('1')),
                'default'  => 'Common searches:',
            ),
            array(
                'id' => 'top_searches_period',
                'type' => 'button_set',
                'title'       => __( 'Search Period', 'shoestrap' ),
                'desc'        => __( 'Display top searches for a selected time period.', 'shoestrap' ),
                'required'    => array('top_searches','=',array('1')),
                'options'   => array(
                    '1' => '1 Day',
                    '7' => '1 Week',
                    '30' => '1 Month',
                    '999999' => 'All Time',
                ),
                'default'  => '999999',
            ),
            array(
                'id'      => 'top_searches_terms',
                'type'    => 'spinner',
                'title'   => __( 'Number of Terms', 'redux-framework-demo' ),
                'desc'    => __( 'Select how many search terms to display.', 'redux-framework-demo' ),
                'required'    => array('top_searches','=',array('1')),
                'default' => '4',
                'min'     => '1',
                'step'    => '1',
                'max'     => '20',
            ),
        )
    );

    $metaboxes[] = array(
        'id' => 'home-metabox',
        'title' => __('Sections Options', 'pressapps'),
        'post_types' => array('page'),
        'page_template' => array('template-home.php'),
        'position' => 'normal', // normal, advanced, side
        'priority' => 'core', // high, core, default, low
        'sections' => $homeTemplate
    );
*/
    $contactTemplate = array();
    $contactTemplate[] = array(
        'fields' => array(
            array(
                'id'        => 'contact_email',
                'type'      => 'text',
                'title'     => __('Contact Form Email Address', 'redux-framework-demo'),
                'desc'      => __('Enter the email address where want to receive emails from the contact form or leave blank to use default admin email.', 'redux-framework-demo'),
                'validate'  => 'email',
                'default'   => '',
            ),
            array(
                'id'        => 'contact_subject',
                'type'      => 'text',
                'title'     => __('Contact Form Subject', 'redux-framework-demo'),
                'desc'      => __('Enter the subject for the contact form or leave blank to use default subject.', 'redux-framework-demo'),
                'default'   => '',
            ),
        )
    );
      
    $metaboxes[] = array(
        'id' => 'contact-metabox',
        'title' => __('Contact Form Options', 'pressapps'),
        'post_types' => array('page'),
        'page_template' => array('template-contact.php'),
        'position' => 'normal', 
        'priority' => 'core', 
        'sections' => $contactTemplate
    );

    // Kind of overkill, but ahh well.  ;)
    //$metaboxes = apply_filters( 'your_custom_redux_metabox_filter_here', $metaboxes );

    return $metaboxes;
  }
  add_action('redux/metaboxes/'.$redux_opt_name.'/boxes', 'redux_add_metaboxes');
endif;

// The loader will load all of the extensions automatically based on your $redux_opt_name
require_once(dirname(__FILE__).'/loader.php');
