<?php
/*
Plugin Name: List Related Attachments
Text Domain: list-related-attachments-widget
Plugin URI: http://plugins.twinpictures.de/plugins/list-related-attachments/
Description: Display a filtered list of all related attachments linked to the current post or page
Version: 2.1.3
Author: twinpictures, baden03
Author URI: http://twinpictures.de/
License: GPL2
*/

/**
 * Class WP_Plugin_LRA
 * @package WP_plugin
 * @category WordPress Plugins
 */

class WP_Plugin_LRA {

	/**
	 * Plugin vars
	 * @var string
	 */
	var $plugin_name = 'List Related Attachments';
	var $version = '2.1.3';
	var $domain = 'lra';

	/**
	 * Options page
	 * @var string
	 */
	var $plguin_options_page_title = 'List Related Attachments Options';
	var $plugin_options_menue_title = 'List Related Attach';
	var $plugin_options_slug = 'lra-optons';

	/**
	 * Name of the options
	 * @var string
	 */
	var $options_name = 'WP_LRA_options';

	/**
	 * @var array
	 */
	var $options = array(
		'custom_css' => '',
	);

	/**
	 * PHP5 constructor
	 */
	function __construct() {
		// set option values
		$this->_set_options();

		// load text domain for translations
		load_plugin_textdomain( 'list-related-attachments-widget' );

		// add actions
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'plugin_actions' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action('wp_head', array( $this, 'plugin_head_inject' ) );

		// add shortcode
		add_shortcode('list-related-attach', array($this, 'shortcode'));

		// Add shortcode support for widgets
		add_filter('widget_text', 'do_shortcode');
	}

	//plugin header inject
	function plugin_head_inject(){
		// custom css
		if( !empty( $this->options['custom_css'] ) ){
			echo "\n<style>\n";
			echo $this->options['custom_css'];
			echo "\n</style>\n";
		}
	}

	/**
	 * Callback admin_menu
	 */
	function admin_menu() {
		if ( function_exists( 'add_options_page' ) AND current_user_can( 'manage_options' ) ) {
			// add options page
			$options_page = add_options_page($this->plguin_options_page_title, $this->plugin_options_menue_title, 'manage_options', $this->plugin_options_slug, array( $this, 'options_page' ));
		}
	}

	/**
	 * Callback admin_init
	 */
	function admin_init() {
		// register settings
		register_setting( $this->domain, $this->options_name );
	}

	/**
	 * Callback shortcode
	 */
	function shortcode($atts, $content = null){
		global $wp_query;
        //if(!empty($wp_query->post->ID)):
			extract(shortcode_atts(array(
					    'type' => 'application',
					    'count' => -1,
					    'orderby' => 'date',
					    'order' => 'DESC',
					    'show' => 'title',
					    'target' => 'self',
					    'link_to' => 'file',
						'parent_id' => $wp_query->post->ID,
			), $atts));

			$args = array(
				'post_type' => 'attachment',
				'post_mime_type' => $type,
				'numberposts' => $count,
				'orderby' => $orderby,
				'order' => $order,
				'post_parent' => $parent_id,
			);

			$attachments = get_posts( $args );
			if ($attachments) {
			    $lra = '<ul class = "list-related-attach '.$show.'">';
			    $link_target = '';
			    if($target != 'self'){
				    $link_target = 'target="_blank"';
			    }
			    foreach ($attachments as $attachment) {
				    //let's add swill's sexy mime type class, shall we?
				    $mime_parts = explode("/", $attachment->post_mime_type);
				    $mime_class = 'class="mime-'.$mime_parts[count($mime_parts)-1].'"';
				    $descr = 'post_title';
				    //replace title
				    $display_str = str_replace('title', $attachment->post_title, $show);
				    //replace caption
				    $display_str = str_replace('caption', $attachment->post_excerpt, $display_str);
				    //replace description
				    $display_str = str_replace('description', $attachment->post_content, $display_str);
				    if($link_to == 'file'){
					$lra .= '<li '.$mime_class.'><a href="'.wp_get_attachment_url($attachment->ID).'" '.$link_target.'>'.$display_str.'</a></li>';
				    }
				    else{
					$lra .= '<li '.$mime_class.'><a href="'.get_attachment_link($attachment->ID).'" '.$link_target.'>'.$display_str.'</a></li>';
				    }
			    }
			    wp_reset_postdata();
			    $lra .= '</ul>';
			}
			return $lra;
		//endif;
	}

	// Add link to options page from plugin list
	function plugin_actions($links) {
		$new_links = array();
		$new_links[] = '<a href="options-general.php?page='.$this->plugin_options_slug.'">' . __('Settings', 'list-related-attachments-widget') . '</a>';
		return array_merge($new_links, $links);
	}

	/**
	 * Admin options page
	 */
	function options_page() {
		$like_it_arr = array(
						__('really tied the room together', 'list-related-attachments-widget'),
						__('made you feel all warm and fuzzy on the inside', 'list-related-attachments-widget'),
						__('restored your faith in humanity... even if only for a fleeting second', 'list-related-attachments-widget'),
						__('rocked your world', 'provided a positive vision of future living', 'list-related-attachments-widget'),
						__('inspired you to commit a random act of kindness', 'list-related-attachments-widget'),
						__('encouraged more regular flossing of the teeth', 'list-related-attachments-widget'),
						__('helped organize your life in the small ways that matter', 'list-related-attachments-widget'),
						__('saved your minutes--if not tens of minutes--writing your own solution', 'list-related-attachments-widget'),
						__('brightened your day... or darkened if if you are trying to sleep in', 'list-related-attachments-widget'),
						__('caused you to dance a little jig of joy and joyousness', 'list-related-attachments-widget'),
						__('inspired you to tweet a little @twinpictues social love', 'list-related-attachments-widget'),
						__('tasted great, while also being less filling', 'list-related-attachments-widget'),
						__('caused you to shout: "everybody spread love, give me some mo!"', 'list-related-attachments-widget'),
						__('helped you keep the funk alive', 'list-related-attachments-widget'),
						__('<a href="http://www.youtube.com/watch?v=dvQ28F5fOdU" target="_blank">soften hands while you do dishes</a>', 'list-related-attachments-widget'),
						__('helped that little old lady <a href="http://www.youtube.com/watch?v=Ug75diEyiA0" target="_blank">find the beef</a>', 'list-related-attachments-widget')
					);
	$rand_key = array_rand($like_it_arr);
	$like_it = $like_it_arr[$rand_key];
	?>
		<div class="wrap">
			<h2><?php echo $this->plugin_name; ?></h2>
		</div>

		<div class="postbox-container metabox-holder meta-box-sortables" style="width: 69%">
			<div style="margin:0 5px;">
				<div class="postbox">
					<div class="handlediv" title="<?php _e( 'Click to toggle', 'list-related-attachments-widget' ) ?>"><br/></div>
					<h3 class="handle"><?php _e( 'List Related Attachments Settings', 'list-related-attachments-widget' ) ?></h3>
					<div class="inside">
						<form method="post" action="options.php">
							<?php
								settings_fields( $this->domain );
								$this->_set_options();
								$options = $this->options;
							?>

							<fieldset class="options">
								<table class="form-table">
								<tr>
									<th><?php _e( 'Custom Style', 'list-related-attachments-widget' ) ?>:</th>
									<td><label><textarea id="<?php echo $this->options_name ?>[custom_css]" name="<?php echo $this->options_name ?>[custom_css]" style="width: 100%; height: 150px;"><?php echo $options['custom_css']; ?></textarea>
										<br /><span class="description"><?php _e( 'Custom CSS style for <em>ultimate flexibility</em>', 'list-related-attachments-widget' ) ?></span></label>
									</td>
								</tr>

								</table>
							</fieldset>

							<p class="submit" style="margin-bottom: 20px;">
								<input class="button-primary" type="submit" value="<?php _e( 'Save Changes', 'list-related-attachments-widget' ) ?>" style="float: right;" />
							</p>
					</div>
				</div>
			</div>
		</div>

		<div class="postbox-container side metabox-holder meta-box-sortables" style="width:29%;">
			<div style="margin:0 5px;">
				<div class="postbox">
					<div class="handlediv" title="<?php _e( 'Click to toggle', 'list-related-attachments-widget' ) ?>"><br/></div>
					<h3 class="handle"><?php _e( 'About', 'list-related-attachments-widget' ) ?></h3>
					<div class="inside">
						<h4><?php echo $this->plugin_name; ?> <?php _e('Version', 'list-related-attachments-widget'); ?> <?php echo $this->version; ?></h4>
						<p><?php printf( __('List Related Attachments is a sidebar widget and shortcode that will display a filtered, sorted and ordered list of all related attachments linked to current post or page. The widget options are: title, number of attachments to display, type of attachment to display by mime/type, order by value, order direction and what should be displayed (attachment title, caption or description).  A %scomplete listing of shortcode options and attribute demos%s are available that delight and inform. What\'s more, %sexcellent and free community support%s is available. Oh, one more thing: The plugin can be translated into any language using our %scommunity translation tool%s.', 'list-related-attachments-widget') ,'<a href="http://plugins.twinpictures.de/plugins/list-related-attachments/documentation/">','</a>', '<a href="http://wordpress.org/support/plugin/list-related-attachments-widget">', '</a>', '<a href="http://translate.twinpictures.de/projects/list-related-attachments">', '</a>') ?></p>
						<ul>
							<li>
								<?php printf( __( '%sDetailed documentation%s, complete with working demonstrations of all shortcode attributes, is available for your instructional enjoyment.', 'list-related-attachments-widget'), '<a href="http://plugins.twinpictures.de/plugins/list-related-attachments/documentation/" target="_blank">', '</a>'); ?>
							</li>
							<li><?php printf( __('If this plugin %s, please consider %ssharing your story%s with others.', 'list-related-attachments-widget'), $like_it, '<a href="http://www.facebook.com/twinpictures" target="_blank">', '</a>' ) ?></li>
							<li><?php printf( __('Your %sreviews%s, %sbug-reports, feedback%s and %scocktail recipes%s are always welcomed.', 'list-related-attachments-widget'), '<a href="http://wordpress.org/support/view/plugin-reviews/list-related-attachments-widget">', '</a>', '<a href="http://wordpress.org/support/plugin/list-related-attachments-widget">', '</a>', '<a href="http://www.facebook.com/twinpictures">', '</a>'); ?></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	<?php
	}

	/**
	 * Set options from save values or defaults
	 */
	function _set_options() {
		// set options
		$saved_options = get_option( $this->options_name );

		// backwards compatible (old values)
		if ( empty( $saved_options ) ) {
			$saved_options = get_option( $this->domain . 'options' );
		}

		// set all options
		if ( ! empty( $saved_options ) ) {
			foreach ( $this->options AS $key => $option ) {
				$this->options[ $key ] = ( empty( $saved_options[ $key ] ) ) ? '' : $saved_options[ $key ];
			}
		}
	}

} // end class WP_Plugin_Template

/**
 * Create instance
 */
$WP_Plugin_LRA = new WP_Plugin_LRA;


//Widget
class LRA_Widget extends WP_Widget {
    /** constructor */
	function __construct() {
		$widget_ops = array(
			'classname'		=> 'LRA_Widget',
			'description'	=> __( 'Display a filtered list of all related attachments linked to the current post or page', 'list-related-attachments-widget' )
		);

		parent::__construct( 'LRA_Widget', __( 'List Related Attachments', 'list-related-attachments-widget' ), $widget_ops );

	}

    /** Widget */
    function widget($args, $instance) {

	global $wp_query;
	extract($args);
	if(!empty($wp_query->post->ID)):
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$count = empty($instance['count']) ? -1 : $instance['count'];
		$type = empty($instance['type']) ? 'application' : apply_filters('widget_type', $instance['type']);
		$orderby = empty($instance['orderby']) ? 'date' : apply_filters('widget_orderby', $instance['orderby']);
		$order = empty($instance['order']) ? 'DESC' : apply_filters('widget_order', $instance['order']);
		$display = empty($instance['display']) ? 'title' : apply_filters('widget_display', $instance['display']);
		$target = empty($instance['target']) ? 'self' : apply_filters('widget_target', $instance['target']);
		$link_to = empty($instance['link_to']) ? 'file' : apply_filters('widget_target', $instance['link_to']);

		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' => $type,
			'numberposts' => $count,
			'orderby' => $orderby,
			'order' => $order,
			'post_parent' => $wp_query->post->ID
		);

		$attachments = get_posts($args);
		if ($attachments) {
			echo $before_widget;
			if($title){
				echo $before_title . $title . $after_title;
			}
			echo '<ul>';
			$link_target = '';
			if($target != 'self'){
				$link_target = 'target="_blank"';
			}
			foreach ($attachments as $attachment) {
				$mime_parts = explode("/", $attachment-> post_mime_type);
				$mime_class = 'class="mime-'.$mime_parts[count($mime_parts)-1].'"';
				//replace title
				$display_str = str_replace('title', $attachment->post_title, $display);
				//replace caption
				$display_str = str_replace('caption', $attachment->post_excerpt, $display_str);
				//replace description
				$display_str = str_replace('description', $attachment->post_content, $display_str);
				if($link_to == 'file'){
					echo '<li '.$mime_class.'><a href="'.wp_get_attachment_url($attachment->ID).'" '.$link_target.'>'.$display_str.'</a></li>';
				}
				else{
					echo '<li '.$mime_class.'><a href="'.get_attachment_link($attachment->ID).'" '.$link_target.'>'.$display_str.'</a></li>';
				}

			}
			echo '</ul>'.$after_widget;
		}
	endif;
    }

    /** Update **/
    function update($new_instance, $old_instance) {
		$instance = array_merge($old_instance, $new_instance);
		$instance['count'] = $new_instance['count'];
		return $instance;
    }

    /** Form **/
    function form($instance) {

	$title = empty($instance['title']) ? '' : stripslashes($instance['title']);
	$count = empty($instance['count']) ? '' : $instance['count'];
	$type = empty($instance['type']) ? 'application' : stripslashes($instance['type']);
	$orderby = empty($instance['orderby']) ? 'date' : stripslashes($instance['orderby']);
	$order = empty($instance['order']) ? 'DESC' : stripslashes($instance['order']);
	$display = empty($instance['display']) ? 'title' : stripslashes($instance['display']);
	$target = empty($instance['target']) ? 'self' : stripslashes($instance['target']);
	$link_to = empty($instance['link_to']) ? 'file' : stripslashes($instance['link_to']);
	?>

	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'list-related-attachments-widget'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Number of attachments to display:', 'list-related-attachments-widget'); ?></label> <input class="widefat" style="width: 50px;" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="number" value="<?php echo esc_attr($count); ?>" /></p>
	<p><label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Attachment Mime/Type', 'list-related-attachments-widget'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>" type="text" value="<?php echo $type; ?>" /></label>
	<a class="description" href="http://en.wikipedia.org/wiki/MIME_type#List_of_common_media_types" target="_blank"><?php _e('list of common mime/types', 'list-related-attachments-widget'); ?></a></p>
	<p><label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order By', 'list-related-attachments-widget'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>" type="text" value="<?php echo $orderby; ?>" /></label>
	<a class="description" href="http://codex.wordpress.org/Function_Reference/query_posts#Orderby_Parameters" target="_blank"><?php _e('list of orderby values', 'list-related-attachments-widget'); ?></a></p>
	<p><label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order Direction', 'list-related-attachments-widget'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>" type="text" value="<?php echo $order; ?>" /></label>
	<span class="description"><?php _e('Valid values: ASC or DESC', 'list-related-attachments-widget'); ?></span></p>
	<p><label for="<?php echo $this->get_field_id('display'); ?>"><?php _e('Display:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('display'); ?>" name="<?php echo $this->get_field_name('display'); ?>" type="text" value="<?php echo $display; ?>" /></label>
	<span class="description"><?php _e('title, caption and/or descripton', 'list-related-attachments-widget'); ?></span></p>
	<p><?php _e('Target', 'list-related-attachments-widget'); ?>: <select name="<?php echo $this->get_field_name('target'); ?>" id="<?php echo $this->get_field_name('target'); ?>">
	<?php
	    $option_arr = array('self', 'blank');
		foreach($option_arr AS $opt){
			$selected = '';
			if($target == $opt){
				$selected = 'SELECTED';
			}
			echo '<option value="'.$opt.'" '.$selected.'>'.$opt.'</option>';
		}
	?>
	</select></p>
	<p><?php _e('Link To', 'list-related-attachments-widget'); ?>: <select name="<?php echo $this->get_field_name('link_to'); ?>" id="<?php echo $this->get_field_name('link_to'); ?>">
	<?php
		//included as vars so they can be picked up by the translation filter... there must be a better way to do this.
		$trans_file = __('file', 'list-related-attachments-widget');
		$trans_attach = __('attachment_page', 'list-related-attachments-widget');

		$option_arr = array('file', 'attachment_page');
		foreach($option_arr AS $opt){
			$selected = '';
			if($target == $opt){
				$selected = 'SELECTED';
			}
			echo '<option value="'.$opt.'" '.$selected.'>'.__($opt, 'list-related-attachments-widget').'</option>';
		}
	?>
	</select></p>
	<?php
    }
} // class LRA_Widget

// register LRA_Widget
function lra_register_widget() {
	register_widget( 'LRA_Widget' );
}
add_action( 'widgets_init', 'lra_register_widget' );

?>
