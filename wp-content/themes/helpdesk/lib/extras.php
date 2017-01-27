<?php
global $helpdesk;

/**
 * Clean up the_excerpt()
 */
function pa_excerpt_more($more) {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'pressapps') . '</a>';
}
add_filter('excerpt_more', 'pa_excerpt_more');

/**
 * Custom excerpt lenghth
 */
function pa_excerpt($excerpt_length = 55, $echo = true) {
         
  $text = '';
  global $post;
  $text = ($post->post_excerpt) ? $post->post_excerpt : get_the_content('');
  $text = strip_shortcodes( $text );
  $text = apply_filters('the_content', $text);
  $text = str_replace(']]>', ']]&gt;', $text);
  $text = strip_tags($text);
       
  $excerpt_more = ' ' . '';
  $words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
  if ( count($words) > $excerpt_length ) {
    array_pop($words);
    $text = implode(' ', $words);
    $text = $text . $excerpt_more;
  } else {
    $text = implode(' ', $words);
  }
  if($echo)
    echo apply_filters('the_content', $text);
  else
    return $text;
}
 
function get_pa_excerpt($excerpt_length = 55, $echo = false) {
 return pa_excerpt($excerpt_length, $id, $echo);
}

/**
 * Manage output of wp_title()
 */
function pa_wp_title($title) {
  if (is_feed()) {
    return $title;
  }

  $title .= get_bloginfo('name');

  return $title;
}
add_filter('wp_title', 'pa_wp_title', 10);

/**
 * Redirects search results from /?s=query to /search/query/, converts %20 to +
 *
 * @link http://txfx.net/wordpress-plugins/nice-search/
 * 
 * You can enable/disable this feature in functions.php (or lib/config.php if you're using Roots):
 * add_theme_support('soil-nice-search');
 */
function pa_nice_search_redirect() {
  global $wp_rewrite;
  if (!isset($wp_rewrite) || !is_object($wp_rewrite) || !$wp_rewrite->using_permalinks()) {
    return;
  }
  $search_base = $wp_rewrite->search_base;
  if (is_search() && !is_admin() && strpos($_SERVER['REQUEST_URI'], "/{$search_base}/") === false) {
    wp_redirect(home_url("/{$search_base}/" . urlencode(get_query_var('s'))));
    exit();
  }
}

if ((isset($helpdesk['nice_search'])?$helpdesk['nice_search']:FALSE)) {
  add_action('template_redirect', 'pa_nice_search_redirect');
}

/**
 * Hide sidebar on one column layout
 */
add_filter('roots/display_sidebar', 'pa_sidebars');

function pa_sidebars($sidebar) {
  global $helpdesk;
/*
  if (is_category() && $helpdesk['layout_category'] == '1') {
    return false;
  } elseif (is_singular('post') && $helpdesk['layout_single'] == '1') {
    return false;
  } else
*/
  if ($helpdesk['layout'] == '1') {
    return false;
  }
  return $sidebar;
}

/**
 * Left sidebar
 */
function pa_left_sidebar($sidebar = FALSE) {
  global $helpdesk;
/*
  if (is_category() && $helpdesk['layout_category'] == '2') {
    return true;
  } elseif (is_singular('post') && $helpdesk['layout_single'] == '2') {
    return true;
  } else
*/
  if ($helpdesk['layout'] == '2') {
    return true;
  }
}

/**
 * Add custom favicon to head
 */
function pa_add_favicon(){ 
  global $helpdesk;
  ?>
  <!-- Custom Favicons -->
  <link rel="shortcut icon" href="<?php echo $helpdesk['favicon']['url']; ?>"/>
  <?php }
add_action('wp_head','pa_add_favicon');

/**
 * Pagination
 */
function page_navi($before = '', $after = '') {
  global $wpdb, $wp_query;
  $request = $wp_query->request;
  $posts_per_page = intval(get_query_var('posts_per_page'));
  $paged = intval(get_query_var('paged'));
  $numposts = $wp_query->found_posts;
  $max_page = $wp_query->max_num_pages;
  if ( $numposts <= $posts_per_page ) { return; }
  if(empty($paged) || $paged == 0) {
    $paged = 1;
  }
  $pages_to_show = 7;
  $pages_to_show_minus_1 = $pages_to_show-1;
  $half_page_start = floor($pages_to_show_minus_1/2);
  $half_page_end = ceil($pages_to_show_minus_1/2);
  $start_page = $paged - $half_page_start;
  if($start_page <= 0) {
    $start_page = 1;
  }
  $end_page = $paged + $half_page_end;
  if(($end_page - $start_page) != $pages_to_show_minus_1) {
    $end_page = $start_page + $pages_to_show_minus_1;
  }
  if($end_page > $max_page) {
    $start_page = $max_page - $pages_to_show_minus_1;
    $end_page = $max_page;
  }
  if($start_page <= 0) {
    $start_page = 1;
  }
    
  echo $before.'<nav class="text-center"><ul class="pagination pagination-sm">'."";
    
  $prevposts = get_previous_posts_link('&larr;');
  if($prevposts) { echo '<li>' . $prevposts  . '</li>'; }
  
  for($i = $start_page; $i  <= $end_page; $i++) {
    if($i == $paged) {
      echo '<li class="active"><a href="#">'.$i.'</a></li>';
    } else {
      echo '<li><a href="'.get_pagenum_link($i).'">'.$i.'</a></li>';
    }
  }
  echo '<li class="">';
  next_posts_link('&rarr;');
  echo '</li>';
  echo '</ul>'.$after."</nav>";
}

/**
 * Styled elements
 */
function pa_style_ol() {
  global $helpdesk;
  $class = '';
  if ($helpdesk['style_ol']) {
    $class = ' style-ol';
  }
  echo $class;
}

/**
 * Comments
 */
function pa_comments() {
  comment_form();
}

/**
 * Return an array of the social links the user has entered.
 * This is simply a helper function for other functions.
 */
function pa_social_links() {
  global $helpdesk;
  // An array of the available networks
  $networks   = array();

  // Started on the new stuff, not done yet.
  $networks[] = array( 'url' => $helpdesk['dribbble_link'],     'icon' => 'dribbble',   'fullname' => 'Dribbble' );
  $networks[] = array( 'url' => $helpdesk['facebook_link'],     'icon' => 'facebook',   'fullname' => 'Facebook' );
  $networks[] = array( 'url' => $helpdesk['flickr_link'],       'icon' => 'flickr',     'fullname' => 'Flickr' );
  $networks[] = array( 'url' => $helpdesk['github_link'],       'icon' => 'github',     'fullname' => 'GitHub' );
  $networks[] = array( 'url' => $helpdesk['google_plus_link'],  'icon' => 'googleplus', 'fullname' => 'Google+' );
  $networks[] = array( 'url' => $helpdesk['email_link'],    'icon' => 'mail',  'fullname' => 'Email' );
  $networks[] = array( 'url' => $helpdesk['linkedin_link'],     'icon' => 'linkedin',   'fullname' => 'LinkedIn' );
  $networks[] = array( 'url' => $helpdesk['pinterest_link'],    'icon' => 'pinterest',  'fullname' => 'Pinterest' );
  $networks[] = array( 'url' => $helpdesk['picassa_link'],       'icon' => 'picassa',     'fullname' => 'Picassa' );
  $networks[] = array( 'url' => $helpdesk['rss_link'],          'icon' => 'feed',        'fullname' => 'RSS' );
  $networks[] = array( 'url' => $helpdesk['skype_link'],        'icon' => 'skype',      'fullname' => 'Skype' );
  $networks[] = array( 'url' => $helpdesk['soundcloud_link'],   'icon' => 'soundcloud', 'fullname' => 'SoundCloud' );
  $networks[] = array( 'url' => $helpdesk['stackoverflow_link'],   'icon' => 'stackoverflow', 'fullname' => 'Stack Overflow' );
  $networks[] = array( 'url' => $helpdesk['wordpress_link'],       'icon' => 'wordpress',     'fullname' => 'WordPress' );
  $networks[] = array( 'url' => $helpdesk['twitter_link'],      'icon' => 'twitter',    'fullname' => 'Twitter' );
  $networks[] = array( 'url' => $helpdesk['vimeo_link'],        'icon' => 'vimeo',      'fullname' => 'Vimeo' );
  $networks[] = array( 'url' => $helpdesk['youtube_link'],      'icon' => 'youtube',    'fullname' => 'YouTube' );

  return $networks;
}

function get_social_bar() {
  global $helpdesk;
  $networks = pa_social_links();
  $social = $helpdesk['footer_social'];
  $html = '';
  if ( $social && ! is_null( $networks ) && count( $networks ) > 0 ) {
    $html .= '<div class="footer-social">';

    foreach ( $networks as $network ) {
      // Check if the social network URL has been defined
      if ( isset( $network['url'] ) && ! empty( $network['url'] ) && strlen( $network['url'] ) > 7 ) {
        $html .= '<a href="' . $network['url'] . '" title="' . $network['fullname'] . '"><i class="paso-' . $network['icon'] . '"></i></a>';
      }
    }

    $html .= '</div>';
    return $html;
  }
}