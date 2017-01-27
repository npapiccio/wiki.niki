<?php
/**
 * Welcome Screen Class
 */
class integral_welcome {

	/**
	 * Constructor for the welcome screen
	 */
	public function __construct() {

		/* create dashbord page */
		add_action( 'admin_menu', array( $this, 'integral_welcome_register_menu' ) );

		/* activation notice */
		add_action( 'admin_init', array( $this, 'integral_activation_admin_notice' ) );

		/* enqueue script and style for welcome screen */
		add_action( 'admin_enqueue_scripts', array( $this, 'integral_welcome_style_and_scripts' ) );

		/* enqueue script for customizer */
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'integral_welcome_scripts_for_customizer' ) );

		/* load welcome screen */
		add_action( 'integral_welcome', array( $this, 'integral_welcome_getting_started' ) );
		add_action('admin_init',array($this,'dismiss_welcome'),1);
		
		/* ajax callback for dismissable required actions */
		add_action( 'wp_ajax_integral_dismiss_required_action', array( $this, 'integral_dismiss_required_action_callback') );
		add_action( 'wp_ajax_nopriv_integral_dismiss_required_action', array($this, 'integral_dismiss_required_action_callback') );

	}

	/**
	 * Creates the dashboard page
	 * @see  add_theme_page()
	 */
	public function integral_welcome_register_menu() {
		add_theme_page( __( 'Setup Integral', 'integral' ), __( 'Setup Integral', 'integral' ), 'activate_plugins', 'integral-welcome', array( $this, 'integral_welcome_screen' ) );
	}

	/**
	 * Adds an admin notice upon successful activation.
	 * @since 1.8.2.4
	 */
	public function integral_activation_admin_notice() {
		global $current_user;

		if ( is_admin()) {

			$current_theme = wp_get_theme();
			$welcome_dismissed = get_user_meta($current_user->ID,'integral_welcome_admin_notice');

			if($current_theme->get('Name')== "Integral" && !$welcome_dismissed){
				add_action( 'admin_notices', array( $this, 'integral_welcome_admin_notice' ), 99 );
			}

		}
	}
	function dismiss_welcome() {
		global $current_user;
		$user_id = $current_user->ID;

		if ( isset($_GET['integral_welcome_dismiss']) && $_GET['integral_welcome_dismiss'] == '1' ) {
			add_user_meta($user_id, 'integral_welcome_admin_notice', 'true', true);
		}
	}
	/**
	 * Display an admin notice linking to the welcome screen
	 */
	public function integral_welcome_admin_notice() {

		$dismiss_url = '<a href="' . esc_url( wp_nonce_url( add_query_arg( 'integral_welcome_dismiss', '1' ) ) ) . '" class="button" target="_parent">' . __('Dismiss this notice','integral') . '</a>';
		?>
			<div class="notice notice-info">
				<h2><?php _e( 'Welcome! Thank you for choosing Integral! ', 'integral' ); ?></h2>
                <p><?php echo sprintf( esc_html__( 'Begin by visiting our %swelcome page%s', 'integral' ), '<a href="' . esc_url( admin_url( 'themes.php?page=integral-welcome' ) ) . '">', '</a>' ); ?> <?php _e( 'to setup Integral and start customizing your site.', 'integral' ); ?></p>
				<p><a href="<?php echo esc_url( admin_url( 'themes.php?page=integral-welcome' ) ); ?>" class="button button-primary" style="text-decoration: none;"><?php _e( 'Setup Integral', 'integral' ); ?></a> <?php echo $dismiss_url ?> </p>
				<p><a target="_blank" href="<?php echo esc_url('https://www.themely.com/themes/integral/'); ?>" class="button button-primary button-hero" style="text-decoration: none;"><?php _e( 'Upgrade to Integral PRO!', 'integral' ); ?></a></p>
				<p></p>
			</div>
		<?php
	}

	/**
	 * Load welcome screen css and javascript
	 */
	public function integral_welcome_style_and_scripts( $hook_suffix ) {

		if ( 'appearance_page_integral-welcome' == $hook_suffix ) {
			wp_enqueue_style( 'integral-welcome-screen-css', get_template_directory_uri() . '/inc/welcome/css/welcome.css' );

			global $integral_required_actions;

			$nr_actions_required = 0;

			/* get number of required actions */
			if( get_option('integral_show_required_actions') ):
				$integral_show_required_actions = get_option('integral_show_required_actions');
			else:
				$integral_show_required_actions = array();
			endif;

			if( !empty($integral_required_actions) ):
				foreach( $integral_required_actions as $integral_required_action_value ):
					if(( !isset( $integral_required_action_value['check'] ) || ( isset( $integral_required_action_value['check'] ) && ( $integral_required_action_value['check'] == false ) ) ) && ((isset($integral_show_required_actions[$integral_required_action_value['id']]) && ($integral_show_required_actions[$integral_required_action_value['id']] == true)) || !isset($integral_show_required_actions[$integral_required_action_value['id']]) )) :
						$nr_actions_required++;
					endif;
				endforeach;
			endif;

			wp_localize_script( 'integral-welcome-screen-js', 'IntegralWelcomeScreenObject', array(
				'nr_actions_required' => $nr_actions_required,
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'template_directory' => get_template_directory_uri(),
				'no_required_actions_text' => __( 'Hooray! There are no required actions for you right now.','integral' )
			) );
		}
	}

	/**
	 * Load scripts for customizer page
	 */
	public function integral_welcome_scripts_for_customizer() {

		global $integral_required_actions;

		$nr_actions_required = 0;

		/* get number of required actions */
		if( get_option('integral_show_required_actions') ):
			$integral_show_required_actions = get_option('integral_show_required_actions');
		else:
			$integral_show_required_actions = array();
		endif;

		if( !empty($integral_required_actions) ):
			foreach( $integral_required_actions as $integral_required_action_value ):
				if(( !isset( $integral_required_action_value['check'] ) || ( isset( $integral_required_action_value['check'] ) && ( $integral_required_action_value['check'] == false ) ) ) && ((isset($integral_show_required_actions[$integral_required_action_value['id']]) && ($integral_show_required_actions[$integral_required_action_value['id']] == true)) || !isset($integral_show_required_actions[$integral_required_action_value['id']]) )) :
					$nr_actions_required++;
				endif;
			endforeach;
		endif;

		wp_localize_script( 'integral-welcome-screen-customizer-js', 'IntegralWelcomeScreenCustomizerObject', array(
			'nr_actions_required' => $nr_actions_required,
			'aboutpage' => esc_url( admin_url( 'themes.php?page=integral-welcome#actions_required' ) ),
			'customizerpage' => esc_url( admin_url( 'customize.php#actions_required' ) ),
			'themeinfo' => __('View Theme Info','integral'),
		) );
	}

	/**
	 * Dismiss required actions
	 */
	public function integral_dismiss_required_action_callback() {

		global $integral_required_actions;

		$integral_dismiss_id = (isset($_GET['dismiss_id'])) ? $_GET['dismiss_id'] : 0;

		echo $integral_dismiss_id; /* this is needed and it's the id of the dismissable required action */

		if( !empty($integral_dismiss_id) ):

			/* if the option exists, update the record for the specified id */
			if( get_option('integral_show_required_actions') ):

				$integral_show_required_actions = get_option('integral_show_required_actions');

				$integral_show_required_actions[$integral_dismiss_id] = false;

				update_option( 'integral_show_required_actions',$integral_show_required_actions );

			/* create the new option,with false for the specified id */
			else:

				$integral_show_required_actions_new = array();

				if( !empty($integral_required_actions) ):

					foreach( $integral_required_actions as $integral_required_action ):

						if( $integral_required_action['id'] == $integral_dismiss_id ):
							$integral_show_required_actions_new[$integral_required_action['id']] = false;
						else:
							$integral_show_required_actions_new[$integral_required_action['id']] = true;
						endif;

					endforeach;

				update_option( 'integral_show_required_actions', $integral_show_required_actions_new );

				endif;

			endif;

		endif;

		die(); // this is required to return a proper result
	}


	/**
	 * Welcome screen content
	 */
	public function integral_welcome_screen() {

		get_template_part( ABSPATH . 'wp-load.php' );
		get_template_part( ABSPATH . 'wp-admin/admin.php' );
		get_template_part( ABSPATH . 'wp-admin/admin-header.php' );
		?>
        
        <div class="wrap about-wrap theme-welcome">
            <h1><?php esc_html_e('Welcome to Integral - Version 1.2.8.4', 'integral'); ?></h1>
            <div class="about-text"><?php esc_html_e('Integral is a powerful one-page theme for professionals, agencies and businesses. Its strength lies in displaying content on a single page in a simple and elegant manner. It\'s super easy to customize and allows you to create a stunning website in minutes.', 'integral'); ?></div>
            <a class="wp-badge" href="<?php echo esc_url('https://www.themely.com/'); ?>" target="_blank"><span><?php esc_html_e('Visit Website', 'integral'); ?></span></a>
            <div class="clearfix"></div>
            <h2 class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-active"><?php esc_html_e('Get Started', 'integral'); ?></a>
            </h2>
            <div class="info-tab-content">
                <div class="left">
                    <div>
                    	<p><?php esc_html_e('Follow the steps below to setup Integral and begin customizing your website.', 'integral'); ?></p>
                        <h3><?php esc_html_e('Step 1 - Install Plugins', 'integral'); ?></h3>
                        <ol>
                            <li><?php esc_html_e('Install', 'integral'); ?> <a target="_blank" href="<?php echo esc_url('https://wordpress.org/plugins/redux-framework/'); ?>"><?php esc_html_e('Redux Framework', 'integral'); ?></a> <?php esc_html_e('plugin', 'integral'); ?></li>
                            <li><?php esc_html_e('Install', 'integral'); ?> <a target="_blank" href="<?php echo esc_url('https://wordpress.org/plugins/contact-form-7/'); ?>"><?php esc_html_e('Contact Form 7', 'integral'); ?></a> <?php esc_html_e('plugin', 'integral'); ?></li>
                            <li><?php esc_html_e('Install', 'integral'); ?> <a target="_blank" href="<?php echo esc_url('https://wordpress.org/plugins/theme-demo-import/'); ?>"><?php esc_html_e('Theme Demo Import', 'integral'); ?></a> <?php esc_html_e('plugin', 'integral'); ?></li>
                        </ol>
                        <p>
                            <a class="button button-secondary" href="<?php echo esc_url('themes.php?page=tgmpa-install-plugins'); ?>"><?php esc_html_e('Install Plugins', 'integral'); ?></a>
                        </p>
                    </div>
                    <div>
                        <h3><?php esc_html_e('Step 2 - Configure Homepage Layout', 'integral'); ?></h3>
                        <ol>
                            <li><?php esc_html_e('Create or edit a page, and assign it the One-Page Template from the Page Attributes section.', 'integral'); ?></li>
                            <li><?php esc_html_e('Go to Settings > Reading and set "Front page displays" to "A static page".', 'integral'); ?></li>
                            <li><?php esc_html_e('Select the page you just assigned the One-page Template to as "Front page" and then choose another page as "Posts page" to serve your blog posts.', 'integral'); ?></li>
                        </ol>
                        <p><a class="button button-secondary" target="_blank" href="<?php echo esc_url('http://support.themely.com/knowledgebase/integral-configure-homepage-layout/'); ?>"><?php esc_html_e('Homepage Configuration Instructions (with video)', 'integral'); ?></a></p>
                    </div>
                    <div>
                        <h3><?php esc_html_e('Step 3 - Import Demo Content (OPTIONAL)', 'integral'); ?></h3>
                        <p><?php esc_html_e('Make your site look like our live demo; import all demo pages, posts and widgets.', 'integral'); ?>
                        <br /><?php esc_html_e('Live demo:', 'integral'); ?> <a target="_blank" href="<?php echo esc_url('http://demo.themely.com/integral/'); ?>">http://demo.themely.com/integral/</a><br /><i><?php esc_html_e('(Make sure you have already installed the Theme Demo Import Plugin)', 'integral'); ?></i></p>
                        <p><a class="button button-secondary" href="<?php echo esc_url('themes.php?page=theme-demo-import'); ?>"><?php esc_html_e('Import Demo Content', 'integral'); ?></a></p>
                    </div>
                    <div>
                        <h3><?php esc_html_e('Customize Your Website', 'integral'); ?></h3>
                        <p class="about"><?php esc_html_e('Click the button below to configure theme settings and start customizing your site.', 'integral'); ?></p>
                        <p>
                            <a class="button button-primary button-hero" href="<?php echo esc_url('admin.php?page=Integral&tab=1'); ?>"><?php esc_html_e('Integral Options', 'integral'); ?></a>
                        </p>
                    </div>
                    <div>
                        <h3><?php esc_html_e('Theme Support', 'integral'); ?></h3>
                        <p class="about"><?php esc_html_e('Need help? Support for Integral is conducted through our support ticket system.', 'integral'); ?></p>
                        <ul class="ul-square">
                            <li><a target="_blank" href="<?php echo esc_url('http://support.themely.com/forums/'); ?>"><?php esc_html_e('Support Forum', 'integral'); ?></a></li>
                            <li><a target="_blank" href="<?php echo esc_url('http://support.themely.com/section/integral/'); ?>"><?php esc_html_e('Theme Documentation', 'integral'); ?></a></li>
                        </ul>
                        <p><a class="button button-secondary" target="_blank" href="<?php echo esc_url('http://support.themely.com/forums/'); ?>"><?php esc_html_e('Create a support ticket', 'integral'); ?></a></p>
                    </div>
                </div>
                <div class="right">
                    <div class="upgrade">
                        <h3><?php esc_html_e('Upgrade to Integral PRO!', 'integral'); ?></h3>
                        <p class="about"><?php esc_html_e('Unlock all theme features!', 'integral'); ?> <a target="_blank" href="<?php echo esc_url('http://demo.themely.com/integral/'); ?>"><?php esc_html_e('View the live demo', 'integral'); ?></a></p>
                        <p class="red"><strong><?php esc_html_e('Save 10% with coupon code', 'integral'); ?> <span class="border-red"><?php esc_html_e('THEMELY10', 'integral'); ?></span></strong></p>
                        <ul class="ul-square">
                            <li><?php esc_html_e('Easily change font type, color and size for all sections (no coding required)', 'integral'); ?></li>
                            <li><?php esc_html_e('Change order of homepage sections (drag & drop)', 'integral'); ?></li>
                            <li><?php esc_html_e('Easily change size and color of icons (no coding required)', 'integral'); ?></li>
                            <li><?php esc_html_e('UNLOCK Photo & Video Slider in Welcome Section', 'integral'); ?></li>
                            <li><?php esc_html_e('UNLOCK Content Animations', 'integral'); ?></li>
                            <li><?php esc_html_e('UNLOCK Brands Section', 'integral'); ?></li>
                            <li><?php esc_html_e('UNLOCK Projects Grid Section (with lightbox popup)', 'integral'); ?></li>
                            <li><?php esc_html_e('UNLOCK Pricing Tables Section', 'integral'); ?></li>
                            <li><?php esc_html_e('UNLOCK Call To Action 2 Section', 'integral'); ?></li>
                            <li><?php esc_html_e('UNLOCK Stats Section', 'integral'); ?></li>
                            <li><?php esc_html_e('UNLOCK Tweets Section', 'integral'); ?></li>
                            <li><?php esc_html_e('UNLOCK Social sharing on posts (Facebook, Twitter, Linkedin, Reddit, etc)', 'integral'); ?></li>
                            <li><?php esc_html_e('MORE Theme Customization Options', 'integral'); ?></li>
                            <li><?php esc_html_e('MORE Widget Areas', 'integral'); ?></li>
                            <li><?php esc_html_e('MORE Custom Widgets', 'integral'); ?></li>
                            <li><?php esc_html_e('ENHANCED Options Panel', 'integral'); ?></li>
                            <li><?php esc_html_e('FREE Child Theme', 'integral'); ?></li>
                            <li><?php esc_html_e('No restrictions!', 'integral'); ?></li>
                            <li><?php esc_html_e('Priority support', 'integral'); ?></li>
                            <li><?php esc_html_e('Regular theme updates', 'integral'); ?></li>
                        </ul>
                        <p>
                            <a class="button button-primary button-hero" target="_blank" href="<?php echo esc_url('https://www.themely.com/themes/integral/'); ?>"><?php esc_html_e('UPGRADE NOW', 'integral'); ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
	}

}

$GLOBALS['integral_Welcome'] = new integral_Welcome();