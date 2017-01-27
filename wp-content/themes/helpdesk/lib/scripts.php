<?php
/**
 * Scripts and stylesheets
 */
global $helpdesk;

function pa_scripts() {
  /**
   * The build task in Grunt renames production assets with a hash
   * Read the asset names from assets-manifest.json
   */
  global $helpdesk;

  $assets = array(
    'css'       => '/assets/css/main.css',
    'print'  => '/assets/css/print.css',
    'js'        => '/assets/js/scripts.js',
    'modernizr' => '/assets/vendor/modernizr/modernizr.js',
    'fitvids' => '/assets/vendor/fitvids/jquery.fitvids.js',
  );

  wp_enqueue_style('roots_css', get_template_directory_uri() . $assets['css'], false, null);
  //if (is_single() && $helpdesk['print']) {
    //wp_enqueue_style('print_css', get_template_directory_uri() . $assets['print'], false, null, 'print');
  //}
  if (is_single() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
  }

  wp_enqueue_script('modernizr', get_template_directory_uri() . $assets['modernizr'], array(), null, true);
  wp_enqueue_script('jquery');
  wp_enqueue_script('roots_js', get_template_directory_uri() . $assets['js'], array(), null, true);
  wp_enqueue_script('fitvids', get_template_directory_uri() . $assets['fitvids'], array(), null, true);
}
add_action('wp_enqueue_scripts', 'pa_scripts', 100);

/**
 * Admin scripts
 */
function pa_admin_scripts() {
  wp_enqueue_style('admin_css', get_template_directory_uri() . '/assets/css/admin.css', false, null);
  wp_enqueue_script( 'admin_js', get_template_directory_uri() . '/assets/js/admin.js', array( 'jquery' ));
}
add_action( 'admin_enqueue_scripts', 'pa_admin_scripts' );

/**
 * Google Analytics snippet from HTML5 Boilerplate
 *
 * Cookie domain is 'auto' configured. See: http://goo.gl/VUCHKM
 */
function pa_google_analytics() { ?>
<script>
  <?php if (WP_ENV === 'production') : ?>
    (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
    function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
    e=o.createElement(i);r=o.getElementsByTagName(i)[0];
    e.src='//www.google-analytics.com/analytics.js';
    r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
  <?php else : ?>
    function ga() {
      console.log('GoogleAnalytics: ' + [].slice.call(arguments));
    }
  <?php endif; ?>
  ga('create','<?php echo GOOGLE_ANALYTICS_ID; ?>','auto');ga('send','pageview');
</script>

<?php }
if (GOOGLE_ANALYTICS_ID && (WP_ENV !== 'production' || !current_user_can('manage_options'))) {
  add_action('wp_footer', 'pa_google_analytics', 20);
}
