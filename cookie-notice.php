<?php
/*
Plugin Name: Cookie Notice
Description: Cookie Notice allows you to elegantly inform users that your site uses cookies and helps you comply with the EU GDPR cookie law and CCPA regulations.
Version: 1.3.2
Author: dFactory
Author URI: http://www.dfactory.eu/
Plugin URI: http://www.dfactory.eu/plugins/cookie-notice/
License: MIT License
License URI: http://opensource.org/licenses/MIT
Text Domain: cookie-notice
Domain Path: /languages

Cookie Notice
Copyright (C) 2013-2020, Digital Factory - info@digitalfactory.pl

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Cookie Notice class.
 *
 * @class Cookie_Notice
 * @version	1.3.2
 */
class Cookie_Notice {

	/**
	 * @var $defaults
	 */
	private $defaults = array(
		'general' => array(
			'position'				=> 'bottom',
			'message_text'			=> '',
			'css_style'				=> 'bootstrap',
			'css_class'				=> '',
			'accept_text'			=> '',
			'refuse_text'			=> '',
			'refuse_opt'			=> false,
			'refuse_code'			=> '',
			'refuse_code_head'		=> '',
			'revoke_cookies'		=> false,
			'revoke_cookies_opt'	=> 'automatic',
			'revoke_message_text'	=> '',
			'revoke_text'			=> '',
			'redirection'			=> false,
			'see_more'				=> false,
			'link_target'			=> '_blank',
			'link_position'			=> 'banner',
			'time'					=> 'month',
			'time_rejected'			=> 'month',
			'hide_effect'			=> 'fade',
			'on_scroll'				=> false,
			'on_scroll_offset'		=> 100,
			'on_click'				=> false,
			'colors' => array(
				'text'			=> '#fff',
				'bar'			=> '#000',
				'bar_opacity'	=> 100
			),
			'see_more_opt' => array(
				'text'		=> '',
				'link_type'	=> 'page',
				'id'		=> 0,
				'link'		=> '',
				'sync'		=> false
			),
			'script_placement'			=> 'header',
			'coronabar'					=> false, // disabled by default
			'coronabar_cases'			=> true,
			'coronabar_texts'			=> false,
			'coronabar_text_strings'	=> array(
				'headline' => 'Spread the message. Stop the virus.',
				'step_one_title' => 'Hands', // string
				'step_one_desc' => 'Wash often', // string
				'step_two_title' => 'Elbow', // string
				'step_two_desc' => 'Cough into', // string
				'step_three_title' => 'Face', // string
				'step_three_desc' => 'Don\'t touch', // string
				'step_four_title' => 'Space', // string
				'step_four_desc' => 'Avoid crowds', // string
				'step_five_title' => 'Home', // string
				'step_five_desc' => 'Stay inside', // string
				'confirmed' => 'Confirmed', // string
				'recovered' => 'Recovered', // string
			),
			'translate'					=> true,
			'deactivation_delete'		=> false,
			'update_version'			=> 3,
			'update_notice'				=> true,
			'update_delay_date'			=> 0
		),
		'version'	=> '1.3.2'
	);
	private $positions = array();
	private $styles = array();
	private $choices = array();
	private $links = array();
	private $link_targets = array();
	private $link_positions = array();
	private $colors = array();
	private $options = array();
	private $effects = array();
	private $times = array();
	private $notices = array();
	private $script_placements = array();
	
	private static $_instance;

	private function __clone() {}
	private function __wakeup() {}

	/**
	 * Main plugin instance.
	 * 
	 * @return object
	 */
	public static function instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self();

			add_action( 'plugins_loaded', array( self::$_instance, 'load_textdomain' ) );

			self::$_instance->includes();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		
		// get options
		$options = get_option( 'cookie_notice_options', $this->defaults['general'] );

		// check legacy parameters
		$options = $this->check_legacy_params( $options, array( 'refuse_opt', 'on_scroll', 'on_click', 'deactivation_delete', 'see_more' ) );

		// merge old options with new ones
		$this->options = array(
			'general' => $this->multi_array_merge( $this->defaults['general'], $options )
		);

		if ( ! isset( $this->options['general']['see_more_opt']['sync'] ) )
			$this->options['general']['see_more_opt']['sync'] = $this->defaults['general']['see_more_opt']['sync'];

		// actions
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'init', array( $this, 'wpsc_add_cookie' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'update_notice' ) );
		add_action( 'admin_init', array( $this, 'check_version' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu_options' ) );
		add_action( 'after_setup_theme', array( $this, 'load_defaults' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'wp_head', array( $this, 'wp_print_header_scripts' ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'wp_print_footer_scripts' ) );
		add_action( 'wp_footer', array( $this, 'add_cookie_notice' ), 1000 );
		add_action( 'wp_footer', array( $this, 'wp_footer_corona' ), 1001 );
		add_action( 'wp_ajax_cn_dismiss_notice', array( $this, 'ajax_dismiss_admin_notice' ) );
		add_action( 'wp_ajax_cn_save_cases', array( $this, 'ajax_save_cases' ) );
		add_action( 'wp_ajax_nopriv_cn_save_cases', array( $this, 'ajax_save_cases' ) );

		// filters
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		add_filter( 'body_class', array( $this, 'change_body_class' ) );
	}
	
	/**
	 * Include required files
	 *
	 * @return void
	 */
	private function includes() {
		include_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' );
	}

	/**
	 * Load plugin defaults
	 */
	public function load_defaults() {
		$this->positions = array(
			'top'	 			=> __( 'Top', 'cookie-notice' ),
			'bottom' 			=> __( 'Bottom', 'cookie-notice' )
		);

		$this->styles = array(
			'none'		 		=> __( 'None', 'cookie-notice' ),
			'wp-default' 		=> __( 'Light', 'cookie-notice' ),
			'bootstrap'	 		=> __( 'Dark', 'cookie-notice' )
		);
		
		$this->revoke_opts = array(
			'automatic'	 		=> __( 'Automatic', 'cookie-notice' ),
			'manual' 			=> __( 'Manual', 'cookie-notice' )
		);

		$this->links = array(
			'page'	 			=> __( 'Page link', 'cookie-notice' ),
			'custom' 			=> __( 'Custom link', 'cookie-notice' )
		);

		$this->link_targets = array(
			'_blank',
			'_self'
		);

		$this->link_positions = array(
			'banner'			=> __( 'Banner', 'cookie-notice' ),
			'message' 			=> __( 'Message', 'cookie-notice' )
		);

		$this->colors = array(
			'text'	 			=> __( 'Text color', 'cookie-notice' ),
			'bar'	 			=> __( 'Bar color', 'cookie-notice' ),
		);

		$this->times = apply_filters(
			'cn_cookie_expiry',
			array(
				'hour'				=> array( __( 'An hour', 'cookie-notice' ), 3600 ),
				'day'		 		=> array( __( '1 day', 'cookie-notice' ), 86400 ),
				'week'		 		=> array( __( '1 week', 'cookie-notice' ), 604800 ),
				'month'		 		=> array( __( '1 month', 'cookie-notice' ), 2592000 ),
				'3months'	 		=> array( __( '3 months', 'cookie-notice' ), 7862400 ),
				'6months'	 		=> array( __( '6 months', 'cookie-notice' ), 15811200 ),
				'year'		 		=> array( __( '1 year', 'cookie-notice' ), 31536000 ),
				'infinity'	 		=> array( __( 'infinity', 'cookie-notice' ), 2147483647 )
			)
		);

		$this->effects = array(
			'none'	 			=> __( 'None', 'cookie-notice' ),
			'fade'	 			=> __( 'Fade', 'cookie-notice' ),
			'slide'	 			=> __( 'Slide', 'cookie-notice' )
		);

		$this->script_placements = array(
			'header' 			=> __( 'Header', 'cookie-notice' ),
			'footer' 			=> __( 'Footer', 'cookie-notice' ),
		);
		
		// set default text strings
		$this->defaults['general']['message_text'] = __( 'We use cookies to ensure that we give you the best experience on our website. If you continue to use this site we will assume that you are happy with it.', 'cookie-notice' );
		$this->defaults['general']['accept_text'] = __( 'Ok', 'cookie-notice' );
		$this->defaults['general']['refuse_text'] = __( 'No', 'cookie-notice' );
		$this->defaults['general']['revoke_message_text'] = __( 'You can revoke your consent any time using the Revoke consent button.', 'cookie-notice' );
		$this->defaults['general']['revoke_text'] = __( 'Revoke consent', 'cookie-notice' );
		$this->defaults['general']['see_more_opt']['text'] = __( 'Privacy policy', 'cookie-notice' );
		
		// set default coronabar text strings
		$this->defaults['general']['coronabar_text_strings']['headline'] = __( 'Spread the message. Stop the virus.', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['step_one_title'] = __( 'Hands', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['step_one_desc'] = __( 'Wash often', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['step_two_title'] = __( 'Elbow', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['step_two_desc'] = __( 'Cough into', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['step_three_title'] = __( 'Face', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['step_three_desc'] = __( 'Don\'t touch', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['step_four_title'] = __( 'Space', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['step_four_desc'] = __( 'Avoid crowds', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['step_five_title'] = __( 'Home', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['step_five_desc'] = __( 'Stay inside', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['confirmed'] = __( 'Confirmed', 'cookie-notice' );
		$this->defaults['general']['coronabar_text_strings']['recovered'] = __( 'Recovered', 'cookie-notice' );

		// set translation strings on plugin activation
		if ( $this->options['general']['translate'] === true ) {
			$this->options['general']['translate'] = false;

			$this->options['general']['message_text'] = $this->defaults['general']['message_text'];
			$this->options['general']['accept_text'] = $this->defaults['general']['accept_text'];
			$this->options['general']['refuse_text'] = $this->defaults['general']['refuse_text'];
			$this->options['general']['revoke_message_text'] = $this->defaults['general']['revoke_message_text'];
			$this->options['general']['revoke_text'] = $this->defaults['general']['revoke_text'];
			$this->options['general']['see_more_opt']['text'] = $this->defaults['general']['see_more_opt']['text'];

			// coronabar strings
			foreach ( $this->defaults['general']['coronabar_text_strings'] as $key => $label ) {
				$this->options['general']['coronabar_text_strings'][$key] = $this->defaults['general']['coronabar_text_strings'][$key];
			}

			update_option( 'cookie_notice_options', $this->options['general'] );
		}

		// WPML >= 3.2
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
			$this->register_wpml_strings();
		// WPML and Polylang compatibility
		} elseif ( function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'Cookie Notice', 'Message in the notice', $this->options['general']['message_text'] );
			icl_register_string( 'Cookie Notice', 'Button text', $this->options['general']['accept_text'] );
			icl_register_string( 'Cookie Notice', 'Refuse button text', $this->options['general']['refuse_text'] );
			icl_register_string( 'Cookie Notice', 'Revoke message text', $this->options['general']['revoke_message_text'] );
			icl_register_string( 'Cookie Notice', 'Revoke button text', $this->options['general']['revoke_text'] );
			icl_register_string( 'Cookie Notice', 'Privacy policy text', $this->options['general']['see_more_opt']['text'] );
			icl_register_string( 'Cookie Notice', 'Custom link', $this->options['general']['see_more_opt']['link'] );
			
			// coronabar strings
			foreach ( $this->defaults['general']['coronabar_text_strings'] as $key => $label ) {
				icl_register_string( 'Cookie Notice', $key, $this->options['general']['coronabar_text_strings'][$key] );
			}
		}
	}
	
	/**
	 * Check plugin version.
	 * 
	 * @return void
	 */
	public function check_version() {
		if ( ! current_user_can( 'install_plugins' ) )
			return;

		// get current database version
		$current_db_version = get_option( 'cookie_notice_version', '1.0.0' );

		// new version?
		if ( version_compare( $current_db_version, $this->defaults['version'], '<' ) ) {
			// updates plugin version
			update_option( 'cookie_notice_version', $this->defaults['version'], false );
		}
	}

	/**
	 * Update notice.
	 * 
	 * @return void
	 */
	public function update_notice() {
		if ( ! current_user_can( 'install_plugins' ) )
			return;
		
		// test only
		// $this->options['general'] = wp_parse_args( array( 'update_version' => 2, 'update_notice' => true ), $this->options['general'] );
		// update_option( 'cookie_notice_options', $this->options['general'] );
		
		$current_update = 4;
		
		if ( $this->options['general']['update_version'] < $current_update ) {
			// check version, if update version is lower than plugin version, set update notice to true
			$this->options['general'] = wp_parse_args( array( 'update_version' => $current_update, 'update_notice' => true ), $this->options['general'] );

			update_option( 'cookie_notice_options', $this->options['general'] );
		}
		
		// display current version notice
		if ( $this->options['general']['update_notice'] === true && $this->options['general']['coronabar'] ) {
			// include notice js and css, only if needed
			wp_enqueue_script(
				'cookie-notice-admin-notice', plugins_url( 'js/admin-notice' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', __FILE__ ), array( 'jquery' ), $this->defaults['version']
			);
			wp_localize_script(
				'cookie-notice-admin-notice', 'cnArgsNotice', array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'cn_dismiss_notice' )
				)
			);
			wp_enqueue_style( 'cookie-notice-admin-notice', plugins_url( 'css/admin-notice' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.css', __FILE__ ) );
			
			$notice_html = 
			'<div class="cn-notice-text">' .
				'<p>' . __( "Thank you for using the <strong>Corona Banner</strong> and spreading the word about steps to stop the Coronavirus pandemia.", 'cookie-notice' ) . '<br />' . 
				sprintf( __( 'Feel free to customize or disable it anytime in the <a href="%s">Cookie Notice settings</a>.', 'cookie-notice' ), admin_url( 'options-general.php?page=cookie-notice' ) ) . '</p>' .
			'</div>';
		
			$this->add_notice( $notice_html, 'notice is-dismissible', 'div' );
		}
	}

	/**
	 * Add admin notices.
	 * 
	 * @param string $html
	 * @param string $status
	 * @param bool $paragraph
	 */
	private function add_notice( $html = '', $status = 'error', $container = '' ) {
		$this->notices[] = array(
			'html' 		=> $html,
			'status' 	=> $status,
			'container' => ( ! empty( $container ) && in_array( $container, array( 'p', 'div' ) ) ? $container : '' )
		);

		add_action( 'admin_notices', array( $this, 'display_notice'), 0 );
	}

	/**
	 * Print admin notices.
	 * 
	 * @return mixed
	 */
	public function display_notice() {
		foreach( $this->notices as $notice ) {
			echo '
			<div id="cn-admin-notice" class="cn-notice ' . $notice['status'] . '">
				' . ( ! empty( $notice['container'] ) ? '<' . $notice['container'] . ' class="cn-notice-container">' : '' ) . '
				' . $notice['html'] . '
				' . ( ! empty( $notice['container'] ) ? '</' . $notice['container'] . ' class="cn-notice-container">' : '' ) . '
			</div>';
		}
	}

	/**
	 * Dismiss admin notice.
	 */
	public function ajax_dismiss_admin_notice() {
		if ( ! current_user_can( 'install_plugins' ) )
			return;

		if ( wp_verify_nonce( esc_attr( $_REQUEST['nonce'] ), 'cn_dismiss_notice' ) ) {
			$notice_action = empty( $_REQUEST['notice_action'] ) || $_REQUEST['notice_action'] === 'dismiss' ? 'dismiss' : esc_attr( $_REQUEST['notice_action'] );

			switch ( $notice_action ) {
				// delay notice
				case 'delay':
					// set delay period to 1 week from now
					$this->options['general'] = wp_parse_args( array( 'update_delay_date' => time() + 1209600 ), $this->options['general'] );
					update_option( 'cookie_notice_options', $this->options['general'] );
					break;
				
				// delay notice
				case 'approve':
					// enable coronabar
					$this->options['general'] = wp_parse_args( array( 'coronabar' => true), $this->options['general'] );
					// hide notice
					$this->options['general'] = wp_parse_args( array( 'update_notice' => false ), $this->options['general'] );
					$this->options['general'] = wp_parse_args( array( 'update_delay_date' => 0 ), $this->options['general'] );
					// update options
					update_option( 'cookie_notice_options', $this->options['general'] );
					break;

				// hide notice
				default:
					$this->options['general'] = wp_parse_args( array( 'update_notice' => false ), $this->options['general'] );
					$this->options['general'] = wp_parse_args( array( 'update_delay_date' => 0 ), $this->options['general'] );

					update_option( 'cookie_notice_options', $this->options['general'] );
			}
		}

		exit;
	}
	
	/**
	 * Save corona cases locally for caching.
	 */
	public function ajax_save_cases() {
		// check if the bar is enabled
		if ( ! $this->options['general']['coronabar'] )
			return;
		
		if ( ! wp_verify_nonce( esc_attr( $_REQUEST['nonce'] ), 'cn_save_cases' ) )
			return;
		
		$json_data = ! empty( $_REQUEST['data'] ) ? esc_attr( $_REQUEST['data'] ) : false;
		$cases_data = array();

		if ( ! empty( $json_data ) )
			$cases_data = json_decode( stripslashes( html_entity_decode( $json_data ) ), true );
		
		// save data
		if ( $cases_data && is_array( $cases_data ) )
			set_transient( 'cookie_notice_coronadata', $cases_data, 12 * HOUR_IN_SECONDS );
	}

	/**
	 * Add new body classes.
	 *
	 * @param array $classes Body classes
	 * @return array
	 */
	public function change_body_class( $classes ) {
		if ( is_admin() )
			return $classes;

		if ( $this->cookies_set() ) {
			$classes[] = 'cookies-set';

			if ( $this->cookies_accepted() )
				$classes[] = 'cookies-accepted';
			else
				$classes[] = 'cookies-refused';
		} else
			$classes[] = 'cookies-not-set';

		return $classes;
	}

	/**
	 * Register shortcode.
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'cookies_accepted', array( $this, 'cookies_accepted_shortcode' ) );
		add_shortcode( 'cookies_revoke', array( $this, 'cookies_revoke_shortcode' ) );
		add_shortcode( 'cookies_policy_link', array( $this, 'cookies_policy_link_shortcode' ) );
	}

	/**
	 * Register cookies accepted shortcode.
	 *
	 * @param array $args
	 * @param mixed $content
	 * @return mixed
	 */
	public function cookies_accepted_shortcode( $args, $content ) {
		if ( $this->cookies_accepted() ) {
			$scripts = html_entity_decode( trim( wp_kses( $content, $this->get_allowed_html() ) ) );

			if ( ! empty( $scripts ) ) {
				if ( preg_match_all( '/' . get_shortcode_regex() . '/', $content ) ) {
					$scripts = do_shortcode( $scripts );
				}
				return $scripts;
			}
		}

		return '';
	}

	/**
	 * Register cookies accepted shortcode.
	 *
	 * @param array $args
	 * @param mixed $content
	 * @return mixed
	 */
	public function cookies_revoke_shortcode( $args, $content ) {
		// get options
		$options = $this->options['general'];

		// defaults
		$defaults = array(
			'title'	=> $options['revoke_text'],
			'class'	=> $options['css_class']
		);

		// combine shortcode arguments
		$args = shortcode_atts( $defaults, $args );

		// escape class(es)
		$args['class'] = esc_attr( $args['class'] );
		
		$shortcode = '<a href="#" class="cn-revoke-cookie cn-button cn-revoke-inline' . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $args['class'] !== '' ? ' ' . $args['class'] : '' ) . '" title="' . esc_html( $args['title'] ) . '">' . esc_html( $args['title'] ) . '</a>';

		return $shortcode;
	}

	/**
	 * Register cookies policy link shortcode.
	 *
	 * @param array $args
	 * @param string $content
	 * @return string
	 */
	public function cookies_policy_link_shortcode( $args, $content ) {
		// get options
		$options = $this->options['general'];
		
		// defaults
		$defaults = array(
			'title'	=> esc_html( $options['see_more_opt']['text'] !== '' ? $options['see_more_opt']['text'] : '&#x279c;' ),
			'link'	=> ( $options['see_more_opt']['link_type'] === 'custom' ? $options['see_more_opt']['link'] : get_permalink( $options['see_more_opt']['id'] ) ),
			'class'	=> $options['css_class']
		);
		
		// combine shortcode arguments
		$args = shortcode_atts( $defaults, $args );
		
		$shortcode = '<a href="' . $args['link'] . '" target="' . $options['link_target'] . '" id="cn-more-info" class="cn-privacy-policy-link cn-link' . ( $args['class'] !== '' ? ' ' . $args['class'] : '' ) . '">' . esc_html( $args['title'] ) . '</a>';
		
		return $shortcode;
	}

	/**
	 * Register WPML (>= 3.2) strings if needed.
	 *
	 * @return	void
	 */
	private function register_wpml_strings() {
		global $wpdb;

		// prepare strings
		$strings = array(
			'Message in the notice'	=> $this->options['general']['message_text'],
			'Button text'			=> $this->options['general']['accept_text'],
			'Refuse button text'	=> $this->options['general']['refuse_text'],
			'Revoke message text'	=> $this->options['general']['revoke_message_text'],
			'Revoke button text'	=> $this->options['general']['revoke_text'],
			'Privacy policy text'	=> $this->options['general']['see_more_opt']['text'],
			'Custom link'			=> $this->options['general']['see_more_opt']['link']
		);
		
		// coronabar strings
		foreach ( $this->defaults['general']['coronabar_text_strings'] as $key => $label ) {
			$strings[$key] = $this->options['general']['coronabar_text_strings'];
		}

		// get query results
		$results = $wpdb->get_col( $wpdb->prepare( "SELECT name FROM " . $wpdb->prefix . "icl_strings WHERE context = %s", 'Cookie Notice' ) );

		// check results
		foreach( $strings as $string => $value ) {
			// string does not exist?
			if ( ! in_array( $string, $results, true ) ) {
				// register string
				do_action( 'wpml_register_single_string', 'Cookie Notice', $string, $value );
			}
		}
	}

	/**
	 * Load textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'cookie-notice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add submenu.
	 */
	public function admin_menu_options() {
		add_options_page( __( 'Cookie Notice', 'cookie-notice' ), __( 'Cookie Notice', 'cookie-notice' ), apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ), 'cookie-notice', array( $this, 'options_page' ) );
	}

	/**
	 * Options page output.
	 * 
	 * @return mixed
	 */
	public function options_page() {
		echo '
		<div class="wrap">
			<h2>' . __( 'Cookie Notice', 'cookie-notice' ) . '</h2>
			<div class="cookie-notice-settings">
				<div class="cookie-notice-credits">
					<h3 class="hndle">' . __( 'Cookie Notice', 'cookie-notice' ) . ' ' . $this->defaults['version'] . '</h3>
					<div class="inside">
						<h4 class="inner">' . __( 'Need support?', 'cookie-notice' ) . '</h4>
						<p class="inner">' . sprintf( __( 'If you are having problems with this plugin, please browse it\'s <a href="%s" target="_blank">Documentation</a> or talk about them in the <a href="%s" target="_blank">Support forum</a>', 'cookie-notice' ), 'https://www.dfactory.eu/docs/cookie-notice/?utm_source=cookie-notice-settings&utm_medium=link&utm_campaign=docs', 'https://dfactory.eu/support/?utm_source=cookie-notice-settings&utm_medium=link&utm_campaign=support' ) . '</p>
						<hr />
						<h4 class="inner">' . __( 'Do you like this plugin?', 'cookie-notice' ) . '</h4>
						<p class="inner">' . sprintf( __( '<a href="%s" target="_blank">Rate it 5</a> on WordPress.org', 'cookie-notice' ), 'https://wordpress.org/support/plugin/cookie-notice/reviews/?filter=5' ) . '<br />' .
						sprintf( __( 'Blog about it & link to the <a href="%s" target="_blank">plugin page</a>.', 'cookie-notice' ), 'https://dfactory.eu/plugins/cookie-notice?utm_source=cookie-notice-settings&utm_medium=link&utm_campaign=blog-about' ) . '<br />' .
						sprintf( __( 'Check out our other <a href="%s" target="_blank">WordPress plugins</a>.', 'cookie-notice' ), 'https://dfactory.eu/plugins/?utm_source=cookie-notice-settings&utm_medium=link&utm_campaign=other-plugins' ) . '
						</p>
					</div>
				</div>
				<form action="options.php" method="post">';

		settings_fields( 'cookie_notice_options' );
		do_settings_sections( 'cookie_notice_options' );
		
		echo '
				<p class="submit">';
		submit_button( '', 'primary', 'save_cookie_notice_options', false );
		echo ' ';
		submit_button( __( 'Reset to defaults', 'cookie-notice' ), 'secondary', 'reset_cookie_notice_options', false );
		echo '
				</p>
				</form>
			</div>
			<div class="clear"></div>
		</div>';
	}

	/**
	 * Regiseter plugin settings.
	 */
	public function register_settings() {
		register_setting( 'cookie_notice_options', 'cookie_notice_options', array( $this, 'validate_options' ) );

		// configuration
		add_settings_section( 'cookie_notice_configuration', __( 'Configuration', 'cookie-notice' ), array( $this, 'cn_section_configuration' ), 'cookie_notice_options' );
		add_settings_field( 'cn_message_text', __( 'Message', 'cookie-notice' ), array( $this, 'cn_message_text' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_accept_text', __( 'Button text', 'cookie-notice' ), array( $this, 'cn_accept_text' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_see_more', __( 'Privacy policy', 'cookie-notice' ), array( $this, 'cn_see_more' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_refuse_opt', __( 'Refuse consent', 'cookie-notice' ), array( $this, 'cn_refuse_opt' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_revoke_opt', __( 'Revoke consent', 'cookie-notice' ), array( $this, 'cn_revoke_opt' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_refuse_code', __( 'Script blocking', 'cookie-notice' ), array( $this, 'cn_refuse_code' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_redirection', __( 'Reloading', 'cookie-notice' ), array( $this, 'cn_redirection' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_on_scroll', __( 'On scroll', 'cookie-notice' ), array( $this, 'cn_on_scroll' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_on_click', __( 'On click', 'cookie-notice' ), array( $this, 'cn_on_click' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_time', __( 'Accepted expiry', 'cookie-notice' ), array( $this, 'cn_time' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_time_rejected', __( 'Rejected expiry', 'cookie-notice' ), array( $this, 'cn_time_rejected' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_script_placement', __( 'Script placement', 'cookie-notice' ), array( $this, 'cn_script_placement' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_deactivation_delete', __( 'Deactivation', 'cookie-notice' ), array( $this, 'cn_deactivation_delete' ), 'cookie_notice_options', 'cookie_notice_configuration' );

		// coronabar
		add_settings_section( 'cookie_notice_coronabar', __( 'Corona Banner', 'cookie-notice' ), array( $this, 'cn_section_coronabar' ), 'cookie_notice_options' );
		add_settings_field( 'cn_coronabar', __( 'Display', 'cookie-notice' ), array( $this, 'cn_coronabar' ), 'cookie_notice_options', 'cookie_notice_coronabar' );
		add_settings_field( 'cn_coronabar_cases', __( 'Current cases', 'cookie-notice' ), array( $this, 'cn_coronabar_cases' ), 'cookie_notice_options', 'cookie_notice_coronabar' );
		add_settings_field( 'cn_coronabar_texts', __( 'Text strings', 'cookie-notice' ), array( $this, 'cn_coronabar_texts' ), 'cookie_notice_options', 'cookie_notice_coronabar' );
		
		// design
		add_settings_section( 'cookie_notice_design', __( 'Design', 'cookie-notice' ), array( $this, 'cn_section_design' ), 'cookie_notice_options' );
		add_settings_field( 'cn_position', __( 'Position', 'cookie-notice' ), array( $this, 'cn_position' ), 'cookie_notice_options', 'cookie_notice_design' );
		add_settings_field( 'cn_hide_effect', __( 'Animation', 'cookie-notice' ), array( $this, 'cn_hide_effect' ), 'cookie_notice_options', 'cookie_notice_design' );
		add_settings_field( 'cn_css_style', __( 'Button style', 'cookie-notice' ), array( $this, 'cn_css_style' ), 'cookie_notice_options', 'cookie_notice_design' );
		add_settings_field( 'cn_css_class', __( 'Button class', 'cookie-notice' ), array( $this, 'cn_css_class' ), 'cookie_notice_options', 'cookie_notice_design' );
		add_settings_field( 'cn_colors', __( 'Colors', 'cookie-notice' ), array( $this, 'cn_colors' ), 'cookie_notice_options', 'cookie_notice_design' );
	}

	/**
	 * Section callback: fix for WP < 3.3
	 */
	public function cn_section_configuration() {}
	public function cn_section_design() {}
	public function cn_section_coronabar() {}
	
	/**
	 * Display Corona Banner option.
	 */
	public function cn_coronabar() {
		echo '
		<fieldset>
			<label><input id="cn_coronabar" type="checkbox" name="cookie_notice_options[coronabar]" value="1" ' . checked( true, $this->options['general']['coronabar'], false ) . '/>' . __( 'Enable to display the Corona Banner.', 'cookie-notice' ) . '</label>
			<p class="description">' . __( 'The Corona Banner displays data about Coronavirus pandemia and <strong>five steps recommended by the WHO (World Health Organization)</strong> to help flatten the Coronavirus curve.', 'cookie-notice' ) . '</p>
		</fieldset>';
	}
	
	/**
	 * Display current cases option.
	 */
	public function cn_coronabar_cases() {
		echo '
		<fieldset>
			<label><input id="cn_coronabar_cases" type="checkbox" name="cookie_notice_options[coronabar_cases]" value="1" ' . checked( true, $this->options['general']['coronabar_cases'], false ) . '/>' . __( 'Display information about current cases.', 'cookie-notice' ) . '</label>
			<p class="description">' . __( 'Provides up-to-date data about Coronavirus confirmed and recovered cases.', 'cookie-notice' ) . '</p>
		</fieldset>';
	}
	
	/**
	 * Coronabar text strings.
	 */
	public function cn_coronabar_texts() {
		$descriptions = array(
			'headline' => __( 'Headline message', 'cookie-notice' ),
			'recovered' => __( 'Current cases text strings', 'cookie-notice' ),
			'step_one_desc' => __( 'Recommendation One', 'cookie-notice' ),
			'step_two_desc' => __( 'Recommendation Two', 'cookie-notice' ),
			'step_three_desc' => __( 'Recommendation Three', 'cookie-notice' ),
			'step_four_desc' => __( 'Recommendation Four', 'cookie-notice' ),
			'step_five_desc' => __( 'Recommendation Five', 'cookie-notice' )
		);
		
		echo '
		<fieldset>
			<label><input id="cn_coronabar_texts" type="checkbox" name="cookie_notice_options[coronabar_texts]" value="1" ' . checked( true, $this->options['general']['coronabar_texts'], false ) . ' />' . __( 'Enable if you\'d like to adjust the Corona Banner text strings.', 'cookie-notice' ) . '</label>
			<div id="cn_coronabar_text_strings"' . ( $this->options['general']['coronabar_texts'] === false ? ' style="display: none;"' : '' ) . '>';
				foreach ( $this->defaults['general']['coronabar_text_strings'] as $key => $label ) {
					echo '
					<span class="cn_coronabar_text_string">
						<input type="text" class="regular-text" name="cookie_notice_options[coronabar_text_strings][' . $key .']" value="' . esc_html( $this->options['general']['coronabar_text_strings'][$key] ) . '" />
					</span>';
					
					// display description
					if ( array_key_exists( $key, $descriptions ) ) {
						echo '
					<p class="description">' . $descriptions[$key] . '</p>';
					}
				}
		echo '
			</div>
		</fieldset>';
	}

	/**
	 * Cookie message option.
	 */
	public function cn_message_text() {
		echo '
		<fieldset>
			<div id="cn_message_text">
				<textarea name="cookie_notice_options[message_text]" class="large-text" cols="50" rows="5">' . esc_textarea( $this->options['general']['message_text'] ) . '</textarea>
				<p class="description">' . __( 'Enter the cookie notice message.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Accept cookie label option.
	 */
	public function cn_accept_text() {
		echo '
		<fieldset>
			<div id="cn_accept_text">
				<input type="text" class="regular-text" name="cookie_notice_options[accept_text]" value="' . esc_attr( $this->options['general']['accept_text'] ) . '" />
			<p class="description">' . __( 'The text of the option to accept the notice and make it disappear.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Enable/Disable third party non functional cookies option.
	 */
	public function cn_refuse_opt() {
		echo '
		<fieldset>
			<label><input id="cn_refuse_opt" type="checkbox" name="cookie_notice_options[refuse_opt]" value="1" ' . checked( true, $this->options['general']['refuse_opt'], false ) . ' />' . __( 'Enable to give to the user the possibility to refuse third party non functional cookies.', 'cookie-notice' ) . '</label>
			<div id="cn_refuse_opt_container"' . ( $this->options['general']['refuse_opt'] === false ? ' style="display: none;"' : '' ) . '>
				<div id="cn_refuse_text">
					<input type="text" class="regular-text" name="cookie_notice_options[refuse_text]" value="' . esc_attr( $this->options['general']['refuse_text'] ) . '" />
					<p class="description">' . __( 'The text of the button to refuse the consent.', 'cookie-notice' ) . '</p>
				</div>
			</div>
		</fieldset>';
	}

	/**
	 * Non functional cookies code.
	 */
	public function cn_refuse_code() {
		$allowed_html = $this->get_allowed_html();
		$active = ! empty( $this->options['general']['refuse_code'] ) && empty( $this->options['general']['refuse_code_head'] ) ? 'body' : 'head';

		echo '
		<fieldset>
			<div id="cn_refuse_code">
				<div id="cn_refuse_code_fields">
					<h2 class="nav-tab-wrapper">
						<a id="refuse_head-tab" class="nav-tab' . ( $active === 'head' ? ' nav-tab-active' : '' ) . '" href="#refuse_head">' . __( 'Head', 'cookie-notice' ) . '</a>
						<a id="refuse_body-tab" class="nav-tab' . ( $active === 'body' ? ' nav-tab-active' : '' ) . '" href="#refuse_body">' . __( 'Body', 'cookie-notice' ) . '</a>
					</h2>
					<div id="refuse_head" class="refuse-code-tab' . ( $active === 'head' ? ' active' : '' ) . '">
						<p class="description">' . __( 'The code to be used in your site header, before the closing head tag.', 'cookie-notice' ) . '</p>
						<textarea name="cookie_notice_options[refuse_code_head]" class="large-text" cols="50" rows="8">' . html_entity_decode( trim( wp_kses( $this->options['general']['refuse_code_head'], $allowed_html ) ) ) . '</textarea>
					</div>
					<div id="refuse_body" class="refuse-code-tab' . ( $active === 'body' ? ' active' : '' ) . '">
						<p class="description">' . __( 'The code to be used in your site footer, before the closing body tag.', 'cookie-notice' ) . '</p>
						<textarea name="cookie_notice_options[refuse_code]" class="large-text" cols="50" rows="8">' . html_entity_decode( trim( wp_kses( $this->options['general']['refuse_code'], $allowed_html ) ) ) . '</textarea>
					</div>
				</div>
				<p class="description">' . __( 'Enter non functional cookies Javascript code here (for e.g. Google Analitycs) to be used after the notice is accepted.', 'cookie-notice' ) . '</br>' . __( 'To get the user consent status use the <code>cn_cookies_accepted()</code> function.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Revoke cookies option.
	 */
	public function cn_revoke_opt() {
		echo '
		<fieldset>
			<label><input id="cn_revoke_cookies" type="checkbox" name="cookie_notice_options[revoke_cookies]" value="1" ' . checked( true, $this->options['general']['revoke_cookies'], false ) . ' />' . __( 'Enable to give to the user the possibility to revoke their consent <i>(requires "Refuse consent" option enabled)</i>.', 'cookie-notice' ) . '</label>
			<div id="cn_revoke_opt_container"' . ( $this->options['general']['revoke_cookies'] ? '' : ' style="display: none;"' ) . '>
				<textarea name="cookie_notice_options[revoke_message_text]" class="large-text" cols="50" rows="2">' . esc_textarea( $this->options['general']['revoke_message_text'] ) . '</textarea>
				<p class="description">' . __( 'Enter the revoke message.', 'cookie-notice' ) . '</p>
				<input type="text" class="regular-text" name="cookie_notice_options[revoke_text]" value="' . esc_attr( $this->options['general']['revoke_text'] ) . '" />
				<p class="description">' . __( 'The text of the button to revoke the consent.', 'cookie-notice' ) . '</p>';

		foreach ( $this->revoke_opts as $value => $label ) {
			echo '
				<label><input id="cn_revoke_cookies-' . $value . '" type="radio" name="cookie_notice_options[revoke_cookies_opt]" value="' . $value . '" ' . checked( $value, $this->options['general']['revoke_cookies_opt'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select the method for displaying the revoke button - automatic (in the banner) or manual using <code>[cookies_revoke]</code> shortcode.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Redirection on cookie accept.
	 */
	public function cn_redirection() {
		echo '
		<fieldset>
			<label><input id="cn_redirection" type="checkbox" name="cookie_notice_options[redirection]" value="1" ' . checked( true, $this->options['general']['redirection'], false ) . ' />' . __( 'Enable to reload the page after the notice is accepted.', 'cookie-notice' ) . '</label>
		</fieldset>';
	}

	/**
	 * Privacy policy link option.
	 */
	public function cn_see_more() {
		$pages = get_pages(
			array(
				'sort_order'	=> 'ASC',
				'sort_column'	=> 'post_title',
				'hierarchical'	=> 0,
				'child_of'		=> 0,
				'parent'		=> -1,
				'offset'		=> 0,
				'post_type'		=> 'page',
				'post_status'	=> 'publish'
			)
		);

		echo '
		<fieldset>
			<label><input id="cn_see_more" type="checkbox" name="cookie_notice_options[see_more]" value="1" ' . checked( true, $this->options['general']['see_more'], false ) . ' />' . __( 'Enable privacy policy link.', 'cookie-notice' ) . '</label>
			<div id="cn_see_more_opt"' . ($this->options['general']['see_more'] === false ? ' style="display: none;"' : '') . '>
				<input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][text]" value="' . esc_attr( $this->options['general']['see_more_opt']['text'] ) . '" />
				<p class="description">' . __( 'The text of the privacy policy button.', 'cookie-notice' ) . '</p>
				<div id="cn_see_more_opt_custom_link">';

		foreach ( $this->links as $value => $label ) {
			$value = esc_attr( $value );

			echo '
					<label><input id="cn_see_more_link-' . $value . '" type="radio" name="cookie_notice_options[see_more_opt][link_type]" value="' . $value . '" ' . checked( $value, $this->options['general']['see_more_opt']['link_type'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				</div>
				<p class="description">' . __( 'Select where to redirect user for more information.', 'cookie-notice' ) . '</p>
				<div id="cn_see_more_opt_page"' . ($this->options['general']['see_more_opt']['link_type'] === 'custom' ? ' style="display: none;"' : '') . '>
					<select name="cookie_notice_options[see_more_opt][id]">
						<option value="0" ' . selected( 0, $this->options['general']['see_more_opt']['id'], false ) . '>' . __( '-- select page --', 'cookie-notice' ) . '</option>';

		if ( $pages ) {
			foreach ( $pages as $page ) {
				echo '
						<option value="' . $page->ID . '" ' . selected( $page->ID, $this->options['general']['see_more_opt']['id'], false ) . '>' . esc_html( $page->post_title ) . '</option>';
			}
		}

		echo '
					</select>
					<p class="description">' . __( 'Select from one of your site\'s pages.', 'cookie-notice' ) . '</p>';

		global $wp_version;

		if ( version_compare( $wp_version, '4.9.6', '>=' ) ) {
			echo '
						<label><input id="cn_see_more_opt_sync" type="checkbox" name="cookie_notice_options[see_more_opt][sync]" value="1" ' . checked( true, $this->options['general']['see_more_opt']['sync'], false ) . ' />' . __( 'Synchronize with WordPress Privacy Policy page.', 'cookie-notice' ) . '</label>';
		}

		echo '
				</div>
				<div id="cn_see_more_opt_link"' . ($this->options['general']['see_more_opt']['link_type'] === 'page' ? ' style="display: none;"' : '') . '>
					<input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][link]" value="' . esc_attr( $this->options['general']['see_more_opt']['link'] ) . '" />
					<p class="description">' . __( 'Enter the full URL starting with http(s)://', 'cookie-notice' ) . '</p>
				</div>
				<div id="cn_see_more_link_target">';

		foreach ( $this->link_targets as $target ) {
			echo '
					<label><input id="cn_see_more_link_target-' . $target . '" type="radio" name="cookie_notice_options[link_target]" value="' . $target . '" ' . checked( $target, $this->options['general']['link_target'], false ) . ' />' . $target . '</label>';
		}

		echo '
					<p class="description">' . esc_html__( 'Select the privacy policy link target.', 'cookie-notice' ) . '</p>
				</div>
				<div id="cn_see_more_link_position">';

		foreach ( $this->link_positions as $position => $label ) {
			echo '
					<label><input id="cn_see_more_link_position-' . $position . '" type="radio" name="cookie_notice_options[link_position]" value="' . $position . '" ' . checked( $position, $this->options['general']['link_position'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
					<p class="description">' . esc_html__( 'Select the privacy policy link position.', 'cookie-notice' ) . '</p>
				</div></div>
		</fieldset>';
	}

	/**
	 * Expiration time option.
	 */
	public function cn_time() {
		echo '
		<fieldset>
			<div id="cn_time">
				<select name="cookie_notice_options[time]">';

		foreach ( $this->times as $time => $arr ) {
			$time = esc_attr( $time );

			echo '
					<option value="' . $time . '" ' . selected( $time, $this->options['general']['time'] ) . '>' . esc_html( $arr[0] ) . '</option>';
		}

		echo '
				</select>
				<p class="description">' . __( 'The amount of time that the cookie should be stored for when user accepts the notice.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Expiration time option.
	 */
	public function cn_time_rejected() {
		echo '
		<fieldset>
			<div id="cn_time_rejected">
				<select name="cookie_notice_options[time_rejected]">';

		foreach ( $this->times as $time => $arr ) {
			$time = esc_attr( $time );

			echo '
					<option value="' . $time . '" ' . selected( $time, $this->options['general']['time_rejected'] ) . '>' . esc_html( $arr[0] ) . '</option>';
		}

		echo '
				</select>
				<p class="description">' . __( 'The amount of time that the cookie should be stored for when the user doesn\'t accept the notice.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Script placement option.
	 */
	public function cn_script_placement() {
		echo '
		<fieldset>';

		foreach ( $this->script_placements as $value => $label ) {
			echo '
			<label><input id="cn_script_placement-' . $value . '" type="radio" name="cookie_notice_options[script_placement]" value="' . esc_attr( $value ) . '" ' . checked( $value, $this->options['general']['script_placement'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
			<p class="description">' . __( 'Select where all the plugin scripts should be placed.', 'cookie-notice' ) . '</p>
		</fieldset>';
	}

	/**
	 * Position option.
	 */
	public function cn_position() {
		echo '
		<fieldset>
			<div id="cn_position">';

		foreach ( $this->positions as $value => $label ) {
			$value = esc_attr( $value );

			echo '
				<label><input id="cn_position-' . $value . '" type="radio" name="cookie_notice_options[position]" value="' . $value . '" ' . checked( $value, $this->options['general']['position'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select location for the notice.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Animation effect option.
	 */
	public function cn_hide_effect() {
		echo '
		<fieldset>
			<div id="cn_hide_effect">';

		foreach ( $this->effects as $value => $label ) {
			$value = esc_attr( $value );

			echo '
				<label><input id="cn_hide_effect-' . $value . '" type="radio" name="cookie_notice_options[hide_effect]" value="' . $value . '" ' . checked( $value, $this->options['general']['hide_effect'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select the animation style.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * On scroll option.
	 */
	public function cn_on_scroll() {
		echo '
		<fieldset>
			<label><input id="cn_on_scroll" type="checkbox" name="cookie_notice_options[on_scroll]" value="1" ' . checked( true, $this->options['general']['on_scroll'], false ) . ' />' . __( 'Enable to accept the notice when user scrolls.', 'cookie-notice' ) . '</label>
			<div id="cn_on_scroll_offset"' . ( $this->options['general']['on_scroll'] === false || $this->options['general']['on_scroll'] == false ? ' style="display: none;"' : '' ) . '>
				<input type="text" class="text" name="cookie_notice_options[on_scroll_offset]" value="' . esc_attr( $this->options['general']['on_scroll_offset'] ) . '" /> <span>px</span>
				<p class="description">' . __( 'Number of pixels user has to scroll to accept the notice and make it disappear.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}
	
	/**
	 * On click option.
	 */
	public function cn_on_click() {
		echo '
		<fieldset>
			<label><input id="cn_on_click" type="checkbox" name="cookie_notice_options[on_click]" value="1" ' . checked( true, $this->options['general']['on_click'], false ) . ' />' . __( 'Enable to accept the notice on any click on the page.', 'cookie-notice' ) . '</label>
		</fieldset>';
	}
	
	/**
	 * Delete plugin data on deactivation.
	 */
	public function cn_deactivation_delete() {
		echo '
		<fieldset>
			<label><input id="cn_deactivation_delete" type="checkbox" name="cookie_notice_options[deactivation_delete]" value="1" ' . checked( true, $this->options['general']['deactivation_delete'], false ) . '/>' . __( 'Enable if you want all plugin data to be deleted on deactivation.', 'cookie-notice' ) . '</label>
		</fieldset>';
	}

	/**
	 * CSS style option.
	 */
	public function cn_css_style() {
		echo '
		<fieldset>
			<div id="cn_css_style">';

		foreach ( $this->styles as $value => $label ) {
			$value = esc_attr( $value );

			echo '
				<label><input id="cn_css_style-' . $value . '" type="radio" name="cookie_notice_options[css_style]" value="' . $value . '" ' . checked( $value, $this->options['general']['css_style'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select the buttons style.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * CSS style option.
	 */
	public function cn_css_class() {
		echo '
		<fieldset>
			<div id="cn_css_class">
				<input type="text" class="regular-text" name="cookie_notice_options[css_class]" value="' . esc_attr( $this->options['general']['css_class'] ) . '" />
				<p class="description">' . __( 'Enter additional button CSS classes separated by spaces.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * Colors option.
	 */
	public function cn_colors() {
		echo '
		<fieldset>';
		
		foreach ( $this->colors as $value => $label ) {
			$value = esc_attr( $value );

			echo '
			<div id="cn_colors-' . $value . '"><label>' . esc_html( $label ) . '</label><br />
				<input class="cn_color" type="text" name="cookie_notice_options[colors][' . $value . ']" value="' . esc_attr( $this->options['general']['colors'][$value] ) . '" />' .
			'</div>';
		}
		
		// print_r( $this->options['general']['colors'] );
		
		echo '
			<div id="cn_colors-bar_opacity"><label>' . __( 'Bar opacity', 'cookie-notice' ) . '</label><br />
				<div><input id="cn_colors_bar_opacity_range" class="cn_range" type="range" min="50" max="100" step="1" name="cookie_notice_options[colors][bar_opacity]" value="' . absint( $this->options['general']['colors']['bar_opacity'] ) . '" onchange="cn_colors_bar_opacity_text.value = cn_colors_bar_opacity_range.value" /><input id="cn_colors_bar_opacity_text" class="small-text" type="number" onchange="cn_colors_bar_opacity_range.value = cn_colors_bar_opacity_text.value" min="50" max="100" value="' . absint( $this->options['general']['colors']['bar_opacity'] ) . '" /></div>' .
			'</div>';
		
		echo '
		</fieldset>';
	}

	/**
	 * Validate options.
	 * 
	 * @param array $input
	 * @return array
	 */
	public function validate_options( $input ) {
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return $input;

		if ( isset( $_POST['save_cookie_notice_options'] ) ) {
			// position
			$input['position'] = sanitize_text_field( isset( $input['position'] ) && in_array( $input['position'], array_keys( $this->positions ) ) ? $input['position'] : $this->defaults['general']['position'] );

			// colors
			$input['colors']['text'] = sanitize_text_field( isset( $input['colors']['text'] ) && $input['colors']['text'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['text'] ) === 1 ? $input['colors']['text'] : $this->defaults['general']['colors']['text'] );
			$input['colors']['bar'] = sanitize_text_field( isset( $input['colors']['bar'] ) && $input['colors']['bar'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['bar'] ) === 1 ? $input['colors']['bar'] : $this->defaults['general']['colors']['bar'] );
			$input['colors']['bar_opacity'] = absint( isset( $input['colors']['bar_opacity'] ) && $input['colors']['bar_opacity'] >= 50 ? $input['colors']['bar_opacity'] : $this->defaults['general']['colors']['bar_opacity'] );

			// texts
			$input['message_text'] = wp_kses_post( isset( $input['message_text'] ) && $input['message_text'] !== '' ? $input['message_text'] : $this->defaults['general']['message_text'] );
			$input['accept_text'] = sanitize_text_field( isset( $input['accept_text'] ) && $input['accept_text'] !== '' ? $input['accept_text'] : $this->defaults['general']['accept_text'] );
			$input['refuse_text'] = sanitize_text_field( isset( $input['refuse_text'] ) && $input['refuse_text'] !== '' ? $input['refuse_text'] : $this->defaults['general']['refuse_text'] );
			$input['revoke_message_text'] = wp_kses_post( isset( $input['revoke_message_text'] ) && $input['revoke_message_text'] !== '' ? $input['revoke_message_text'] : $this->defaults['general']['revoke_message_text'] );
			$input['revoke_text'] = sanitize_text_field( isset( $input['revoke_text'] ) && $input['revoke_text'] !== '' ? $input['revoke_text'] : $this->defaults['general']['revoke_text'] );
			$input['refuse_opt'] = (bool) isset( $input['refuse_opt'] );
			$input['revoke_cookies'] = isset( $input['revoke_cookies'] );
			$input['revoke_cookies_opt'] = isset( $input['revoke_cookies_opt'] ) && array_key_exists( $input['revoke_cookies_opt'], $this->revoke_opts ) ? $input['revoke_cookies_opt'] : $this->defaults['general']['revoke_cookies_opt'];

			// get allowed HTML
			$allowed_html = $this->get_allowed_html();

			// body refuse code
			$input['refuse_code'] = wp_kses( isset( $input['refuse_code'] ) && $input['refuse_code'] !== '' ? $input['refuse_code'] : $this->defaults['general']['refuse_code'], $allowed_html );

			// head refuse code
			$input['refuse_code_head'] = wp_kses( isset( $input['refuse_code_head'] ) && $input['refuse_code_head'] !== '' ? $input['refuse_code_head'] : $this->defaults['general']['refuse_code_head'], $allowed_html );

			// css button style
			$input['css_style'] = sanitize_text_field( isset( $input['css_style'] ) && in_array( $input['css_style'], array_keys( $this->styles ) ) ? $input['css_style'] : $this->defaults['general']['css_style'] );

			// css button class
			$input['css_class'] = sanitize_text_field( isset( $input['css_class'] ) ? $input['css_class'] : $this->defaults['general']['css_class'] );

			// link target
			$input['link_target'] = sanitize_text_field( isset( $input['link_target'] ) && in_array( $input['link_target'], array_keys( $this->link_targets ) ) ? $input['link_target'] : $this->defaults['general']['link_target'] );

			// time
			$input['time'] = sanitize_text_field( isset( $input['time'] ) && in_array( $input['time'], array_keys( $this->times ) ) ? $input['time'] : $this->defaults['general']['time'] );
			$input['time_rejected'] = sanitize_text_field( isset( $input['time_rejected'] ) && in_array( $input['time_rejected'], array_keys( $this->times ) ) ? $input['time_rejected'] : $this->defaults['general']['time_rejected'] );

			// script placement
			$input['script_placement'] = sanitize_text_field( isset( $input['script_placement'] ) && in_array( $input['script_placement'], array_keys( $this->script_placements ) ) ? $input['script_placement'] : $this->defaults['general']['script_placement'] );

			// hide effect
			$input['hide_effect'] = sanitize_text_field( isset( $input['hide_effect'] ) && in_array( $input['hide_effect'], array_keys( $this->effects ) ) ? $input['hide_effect'] : $this->defaults['general']['hide_effect'] );
			
			// redirection
			$input['redirection'] = isset( $input['redirection'] );
			
			// on scroll
			$input['on_scroll'] = isset( $input['on_scroll'] );

			// on scroll offset
			$input['on_scroll_offset'] = absint( isset( $input['on_scroll_offset'] ) && $input['on_scroll_offset'] !== '' ? $input['on_scroll_offset'] : $this->defaults['general']['on_scroll_offset'] );
			
			// on click
			$input['on_click'] = isset( $input['on_click'] );

			// deactivation
			$input['deactivation_delete'] = isset( $input['deactivation_delete'] );

			// privacy policy
			$input['see_more'] = isset( $input['see_more'] );
			$input['see_more_opt']['text'] = sanitize_text_field( isset( $input['see_more_opt']['text'] ) && $input['see_more_opt']['text'] !== '' ? $input['see_more_opt']['text'] : $this->defaults['general']['see_more_opt']['text'] );
			$input['see_more_opt']['link_type'] = sanitize_text_field( isset( $input['see_more_opt']['link_type'] ) && in_array( $input['see_more_opt']['link_type'], array_keys( $this->links ) ) ? $input['see_more_opt']['link_type'] : $this->defaults['general']['see_more_opt']['link_type'] );

			if ( $input['see_more_opt']['link_type'] === 'custom' )
				$input['see_more_opt']['link'] = ( $input['see_more'] === true ? esc_url( $input['see_more_opt']['link'] ) : 'empty' );
			elseif ( $input['see_more_opt']['link_type'] === 'page' ) {
				$input['see_more_opt']['id'] = ( $input['see_more'] === true ? (int) $input['see_more_opt']['id'] : 0 );
				$input['see_more_opt']['sync'] = isset( $input['see_more_opt']['sync'] );

				if ( $input['see_more_opt']['sync'] )
					update_option( 'wp_page_for_privacy_policy', $input['see_more_opt']['id'] );
			}
			
			// policy link position
			$input['link_position'] = sanitize_text_field( isset( $input['link_position'] ) && in_array( $input['link_position'], array_keys( $this->link_positions ) ) ? $input['link_position'] : $this->defaults['general']['link_position'] );

			// message link position?
			if ( $input['see_more'] === true && $input['link_position'] === 'message' && strpos( $input['message_text'], '[cookies_policy_link' ) === false )
				$input['message_text'] .= ' [cookies_policy_link]';
			
			// coronabar
			$input['coronabar'] = isset( $input['coronabar'] );
			
			// cases
			$input['coronabar_cases'] = isset( $input['coronabar_cases'] );
			// text adjustments
			$input['coronabar_texts'] = isset( $input['coronabar_texts'] );
			// text strings
			foreach ( $this->defaults['general']['coronabar_text_strings'] as $key => $label ) {
				$input['coronabar_text_strings'][$key] = sanitize_text_field( isset( $input['coronabar_text_strings'][$key] ) && $input['coronabar_text_strings'][$key] !== '' ? $input['coronabar_text_strings'][$key] : $this->defaults['general']['coronabar_text_strings'][$key] );
			}
			
			$input['update_version'] = $this->options['general']['update_version'];
			$input['update_notice'] = $this->options['general']['update_notice'];

			$input['translate'] = false;

			// WPML >= 3.2
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Message in the notice', $input['message_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Button text', $input['accept_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Refuse button text', $input['refuse_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Revoke message text', $input['revoke_message_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Revoke button text', $input['revoke_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Privacy policy text', $input['see_more_opt']['text'] );

				if ( $input['see_more_opt']['link_type'] === 'custom' )
					do_action( 'wpml_register_single_string', 'Cookie Notice', 'Custom link', $input['see_more_opt']['link'] );
			}
		} elseif ( isset( $_POST['reset_cookie_notice_options'] ) ) {
			
			$input = $this->defaults['general'];

			add_settings_error( 'reset_cookie_notice_options', 'reset_cookie_notice_options', __( 'Settings restored to defaults.', 'cookie-notice' ), 'updated' );
			
		}

		return $input;
	}

	/**
	 * Cookie notice output.
	 * 
	 * @return mixed
	 */
	public function add_cookie_notice() {
		// WPML >= 3.2
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
			$this->options['general']['message_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['message_text'], 'Cookie Notice', 'Message in the notice' );
			$this->options['general']['accept_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['accept_text'], 'Cookie Notice', 'Button text' );
			$this->options['general']['refuse_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['refuse_text'], 'Cookie Notice', 'Refuse button text' );
			$this->options['general']['revoke_message_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['revoke_message_text'], 'Cookie Notice', 'Revoke message text' );
			$this->options['general']['revoke_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['revoke_text'], 'Cookie Notice', 'Revoke button text' );
			$this->options['general']['see_more_opt']['text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['see_more_opt']['text'], 'Cookie Notice', 'Privacy policy text' );
			$this->options['general']['see_more_opt']['link'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['see_more_opt']['link'], 'Cookie Notice', 'Custom link' );
		// WPML and Polylang compatibility
		} elseif ( function_exists( 'icl_t' ) ) {
			$this->options['general']['message_text'] = icl_t( 'Cookie Notice', 'Message in the notice', $this->options['general']['message_text'] );
			$this->options['general']['accept_text'] = icl_t( 'Cookie Notice', 'Button text', $this->options['general']['accept_text'] );
			$this->options['general']['refuse_text'] = icl_t( 'Cookie Notice', 'Refuse button text', $this->options['general']['refuse_text'] );
			$this->options['general']['revoke_message_text'] = icl_t( 'Cookie Notice', 'Revoke message text', $this->options['general']['revoke_message_text'] );
			$this->options['general']['revoke_text'] = icl_t( 'Cookie Notice', 'Revoke button text', $this->options['general']['revoke_text'] );
			$this->options['general']['see_more_opt']['text'] = icl_t( 'Cookie Notice', 'Privacy policy text', $this->options['general']['see_more_opt']['text'] );
			$this->options['general']['see_more_opt']['link'] = icl_t( 'Cookie Notice', 'Custom link', $this->options['general']['see_more_opt']['link'] );
		}

		if ( function_exists( 'icl_object_id' ) )
			$this->options['general']['see_more_opt']['id'] = icl_object_id( $this->options['general']['see_more_opt']['id'], 'page', true );

		// get cookie container args
		$options = apply_filters( 'cn_cookie_notice_args', array(
			'position'				=> $this->options['general']['position'],
			'css_style'				=> $this->options['general']['css_style'],
			'css_class'				=> $this->options['general']['css_class'],
			'button_class'			=> 'cn-button',
			'colors'				=> $this->options['general']['colors'],
			'message_text'			=> $this->options['general']['message_text'],
			'accept_text'			=> $this->options['general']['accept_text'],
			'refuse_text'			=> $this->options['general']['refuse_text'],
			'revoke_message_text'	=> $this->options['general']['revoke_message_text'],
			'revoke_text'			=> $this->options['general']['revoke_text'],
			'refuse_opt'			=> $this->options['general']['refuse_opt'],
			'revoke_cookies'		=> $this->options['general']['revoke_cookies'],
			'see_more'				=> $this->options['general']['see_more'],
			'see_more_opt'			=> $this->options['general']['see_more_opt'],
			'link_target'			=> $this->options['general']['link_target'],
			'link_position'			=> $this->options['general']['link_position'],
			'aria_label'			=> __( 'Cookie Notice', 'cookie-notice' )
		) );

		// check legacy parameters
		$options = $this->check_legacy_params( $options, array( 'refuse_opt', 'see_more' ) );

		if ( $options['see_more'] === true )
			$options['message_text'] = do_shortcode( wp_kses_post( $options['message_text'] ) );
		else
			$options['message_text'] = wp_kses_post( $options['message_text'] );

		$options['css_class'] = esc_attr( $options['css_class'] );

		// message output
		$output = '
		<!-- Cookie Notice plugin v' . $this->defaults['version'] . ' by Digital Factory https://dfactory.eu/ -->
		<div id="cookie-notice" role="banner" class="cookie-notice-hidden cookie-revoke-hidden cn-position-' . $options['position'] . '" aria-label="' . $options['aria_label'] . '" style="background-color: rgba(' . implode( ',', $this->hex2rgb( $options['colors']['bar'] ) ) . ',' . $options['colors']['bar_opacity'] * 0.01 . ');">'
			. '<div class="cookie-notice-container" style="color: ' . $options['colors']['text'] . ';">'
			. '<span id="cn-notice-text" class="cn-text-container">'. $options['message_text'] . '</span>'
			. '<span id="cn-notice-buttons" class="cn-buttons-container"><a href="#" id="cn-accept-cookie" data-cookie-set="accept" class="cn-set-cookie ' . $options['button_class'] . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $options['css_class'] !== '' ? ' ' . $options['css_class'] : '' ) . '" aria-label="' . $options['accept_text'] . '">' . $options['accept_text'] . '</a>'
			. ( $options['refuse_opt'] === true ? '<a href="#" id="cn-refuse-cookie" data-cookie-set="refuse" class="cn-set-cookie ' . $options['button_class'] . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $options['css_class'] !== '' ? ' ' . $options['css_class'] : '' ) . '" aria-label="' . $options['refuse_text'] . '">' . $options['refuse_text'] . '</a>' : '' )
			. ( $options['see_more'] === true && $options['link_position'] === 'banner' ? '<a href="' . ( $options['see_more_opt']['link_type'] === 'custom' ? $options['see_more_opt']['link'] : get_permalink( $options['see_more_opt']['id'] ) ) . '" target="' . $options['link_target'] . '" id="cn-more-info" class="cn-more-info ' . $options['button_class'] . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $options['css_class'] !== '' ? ' ' . $options['css_class'] : '' ) . '" aria-label="' . $options['see_more_opt']['text'] . '">' . $options['see_more_opt']['text'] . '</a>' : '' ) 
			. '</span><a href="javascript:void(0);" id="cn-close-notice" data-cookie-set="accept" class="cn-close-icon" aria-label="' . $options['accept_text'] . '"></a>'
			. '</div>
			' . ( $options['refuse_opt'] === true && $options['revoke_cookies'] == true ? 
			'<div class="cookie-revoke-container" style="color: ' . $options['colors']['text'] . ';">'
			. ( ! empty( $options['revoke_message_text'] ) ? '<span id="cn-revoke-text" class="cn-text-container">'. $options['revoke_message_text'] . '</span>' : '' )
			. '<span id="cn-revoke-buttons" class="cn-buttons-container"><a href="#" class="cn-revoke-cookie ' . $options['button_class'] . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $options['css_class'] !== '' ? ' ' . $options['css_class'] : '' ) . '" aria-label="' . $options['revoke_text'] . '">' . esc_html( $options['revoke_text'] ) . '</a></span>
			</div>' : '' ) . '
		</div>
		<!-- / Cookie Notice plugin -->';

		echo apply_filters( 'cn_cookie_notice_output', $output, $options );
	}

	/**
	 * Check if cookies are accepted.
	 * 
	 * @return bool
	 */
	public static function cookies_accepted() {
		return apply_filters( 'cn_is_cookie_accepted', isset( $_COOKIE['cookie_notice_accepted'] ) && $_COOKIE['cookie_notice_accepted'] === 'true' );
	}

	/**
	 * Check if cookies are set.
	 *
	 * @return boolean Whether cookies are set
	 */
	public function cookies_set() {
		return apply_filters( 'cn_is_cookie_set', isset( $_COOKIE['cookie_notice_accepted'] ) );
	}
	
	/**
	 * Add WP Super Cache cookie.
	 */
	public function wpsc_add_cookie() {
		do_action( 'wpsc_add_cookie', 'cookie_notice_accepted' );
	}
	
	/**
	 * Delete WP Super Cache cookie.
	 */
	public function wpsc_delete_cookie() {
		do_action( 'wpsc_delete_cookie', 'cookie_notice_accepted' );
	}
		
	/**
     * Get default settings.
     */
    public function get_defaults() {
        return $this->defaults;
    }

	/**
	 * Add links to support forum.
	 * 
	 * @param array $links
	 * @param string $file
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return $links;

		if ( $file == plugin_basename( __FILE__ ) )
			return array_merge( $links, array( sprintf( '<a href="http://www.dfactory.eu/support/forum/cookie-notice/" target="_blank">%s</a>', __( 'Support', 'cookie-notice' ) ) ) );

		return $links;
	}

	/**
	 * Add links to settings page.
	 * 
	 * @param array $links
	 * @param string $file
	 * @return array
	 */
	public function plugin_action_links( $links, $file ) {
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return $links;

		if ( $file == plugin_basename( __FILE__ ) )
			array_unshift( $links, sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=cookie-notice' ), __( 'Settings', 'cookie-notice' ) ) );

		return $links;
	}

	/**
	 * Activate the plugin.
	 */
	public function activation() {
		add_option( 'cookie_notice_options', $this->defaults['general'], '', 'no' );
		add_option( 'cookie_notice_version', $this->defaults['version'], '', 'no' );
	}

	/**
	 * Deactivate the plugin.
	 */
	public function deactivation() {
		if ( $this->options['general']['deactivation_delete'] === true ) {
			delete_option( 'cookie_notice_options' );
			delete_option( 'cookie_notice_version' );
			// delete if set
			delete_option( 'cookie_notice_coronadata' );
		}
		
		// remove WP Super Cache cookie
		$this->wpsc_delete_cookie();
	}

	/**
	 * Get allowed script blocking HTML.
	 *
	 * @return array
	 */
	public function get_allowed_html() {
		return apply_filters(
			'cn_refuse_code_allowed_html',
			array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'script' => array(
						'type' => array(),
						'src' => array(),
						'charset' => array(),
						'async' => array()
					),
					'noscript' => array(),
					'style' => array(
						'type' => array()
					),
					'iframe' => array(
						'src' => array(),
						'height' => array(),
						'width' => array(),
						'frameborder' => array(),
						'allowfullscreen' => array()
					)
				)
			)
		);
	}

	/**
	 * Load scripts and styles - admin.
	 */
	public function admin_enqueue_scripts( $page ) {
		if ( $page !== 'settings_page_cookie-notice' )
			return;

		wp_enqueue_script(
			'cookie-notice-admin', plugins_url( 'js/admin' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), $this->defaults['version']
		);
		
		wp_localize_script(
			'cookie-notice-admin', 'cnArgs', array(
				'resetToDefaults'	=> __( 'Are you sure you want to reset these settings to defaults?', 'cookie-notice' )
			)
		);

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'cookie-notice-admin', plugins_url( 'css/admin' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.css', __FILE__ ) );
	}

	/**
	 * Load scripts and styles - frontend.
	 */
	public function wp_enqueue_scripts() {
		wp_enqueue_script( 'cookie-notice-front', plugins_url( 'js/front' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', __FILE__ ), array(), $this->defaults['version'], isset( $this->options['general']['script_placement'] ) && $this->options['general']['script_placement'] === 'footer' );

		wp_localize_script(
			'cookie-notice-front',
			'cnArgs',
			array(
				'ajaxUrl'				=> admin_url( 'admin-ajax.php' ),
				'nonce'					=> wp_create_nonce( 'cn_save_cases' ),
				'hideEffect'			=> $this->options['general']['hide_effect'],
				'position'				=> $this->options['general']['position'],
				'onScroll'				=> (int) $this->options['general']['on_scroll'],
				'onScrollOffset'		=> (int) $this->options['general']['on_scroll_offset'],
				'onClick'				=> (int) $this->options['general']['on_click'],
				'cookieName'			=> 'cookie_notice_accepted',
				'cookieTime'			=> $this->times[$this->options['general']['time']][1],
				'cookieTimeRejected'	=> $this->times[$this->options['general']['time_rejected']][1],
				'cookiePath'			=> ( defined( 'COOKIEPATH' ) ? (string) COOKIEPATH : '' ),
				'cookieDomain'			=> ( defined( 'COOKIE_DOMAIN' ) ? (string) COOKIE_DOMAIN : '' ),
				'redirection'			=> (int) $this->options['general']['redirection'],
				'cache'					=> (int) ( defined( 'WP_CACHE' ) && WP_CACHE ),
				'refuse'				=> (int) $this->options['general']['refuse_opt'],
				'revokeCookies'			=> (int) $this->options['general']['revoke_cookies'],
				'revokeCookiesOpt'		=> $this->options['general']['revoke_cookies_opt'],
				'secure'				=> (int) is_ssl(),
				'coronabarActive'		=> (int) $this->options['general']['coronabar'],
			)
		);

		wp_enqueue_style( 'cookie-notice-front', plugins_url( 'css/front' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.css', __FILE__ ) );
	}
	
	/**
	 * Print Coronabar scripts.
	 *
	 * @return mixed
	 */
	public function wp_footer_corona() {
		// coronabar
		if ( $this->options['general']['coronabar'] ) {

			$options = array(
				'position' => $this->options['general']['position'],
				// 'backgroundOpacity' => absint( $this->options['general']['colors']['bar_opacity'] ),
				'displayCases' => $this->options['general']['coronabar_cases'],
			);
			
			// text strings
			foreach ( $this->defaults['general']['coronabar_text_strings'] as $key => $label ) {
				$key_updated = $this->underscores_to_camelcase( 'text_' . $key );
				
				$options[$key_updated] = $this->options['general']['coronabar_text_strings'][$key];
			}
			
			// get cached data
			$cached_data = get_transient( 'cookie_notice_coronadata' );

			// if exists, send to js
			if ( ! empty( $cached_data ) ) {
				$options['cachedData'] = $cached_data;
			}
			
			echo '
			<!-- Corona Banner -->
			<script type="text/javascript">
				var CoronaBarOptions = ' . json_encode( $options ) . ';
			</script>
			<script type="text/javascript" src="//coronabar-53eb.kxcdn.com/coronabar.min.js" async></script>';
		}
	}

	/**
	 * Print non functional JavaScript in body.
	 *
	 * @return mixed
	 */
	public function wp_print_footer_scripts() {
		if ( $this->cookies_accepted() ) {
			$scripts = apply_filters( 'cn_refuse_code_scripts_html', html_entity_decode( trim( wp_kses( $this->options['general']['refuse_code'], $this->get_allowed_html() ) ) ) );

			if ( ! empty( $scripts ) )
				echo $scripts;
		}
	}

	/**
	 * Print non functional JavaScript in header.
	 *
	 * @return mixed
	 */
	public function wp_print_header_scripts() {
		if ( $this->cookies_accepted() ) {
			$scripts = apply_filters( 'cn_refuse_code_scripts_html', html_entity_decode( trim( wp_kses( $this->options['general']['refuse_code_head'], $this->get_allowed_html() ) ) ) );

			if ( ! empty( $scripts ) )
				echo $scripts;
		}	
	}
	
	/**
	 * Helper: convert hex color to rgb color.
	 * 
	 * @param type $color
	 * @return array
	 */
	public function hex2rgb( $color ) {
		if ( $color[0] == '#' )
			$color = substr( $color, 1 );

		if ( strlen( $color ) == 6 )
			list( $r, $g, $b ) = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		elseif ( strlen( $color ) == 3 )
			list( $r, $g, $b ) = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		else
			return false;

		$r = hexdec( $r );
		$g = hexdec( $g );
		$b = hexdec( $b );

		return array( $r, $g, $b );
	}
	
	/**
	 * Helper: Convert undersocores to CamelCase/
	 * 
	 * @param type $string
	 * @param bool $capitalize_first_char
	 * @return string
	 */
	public function underscores_to_camelcase( $string, $capitalize_first_char = false ) {
		$str = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $string ) ) );

		if ( ! $capitalize_first_char ) {
			$str[0] = strtolower( $str[0] );
		}

		return $str;
	}
	
	/**
	 * Check legacy parameters that were yes/no strings.
	 *
	 * @param array $options
	 * @param array $params
	 * @return array
	 */
	public function check_legacy_params( $options, $params ) {
		foreach ( $params as $param ) {
			if ( array_key_exists( $param, $options ) && ! is_bool( $options[$param] ) )
				$options[$param] = $options[$param] === 'yes';
		}

		return $options;
	}

	/**
	 * Merge multidimensional associative arrays.
	 * Works only with strings, integers and arrays as keys. Values can be any type but they have to have same type to be kept in the final array.
	 * Every array should have the same type of elements. Only keys from $defaults array will be kept in the final array unless $siblings are not empty.
	 * $siblings examples: array( '=>', 'only_first_level', 'first_level=>second_level', 'first_key=>next_key=>sibling' ) and so on.
	 * Single '=>' means that all siblings of the highest level will be kept in the final array.
	 *
	 * @param array	$default Array with defaults values
	 * @param array	$array Array to merge
	 * @param boolean|array	$siblings Whether to allow "string" siblings to copy from $array if they do not exist in $defaults, false otherwise
	 * @return array Merged arrays
	 */
	public function multi_array_merge( $defaults, $array, $siblings = false ) {
		// make a copy for better performance and to prevent $default override in foreach
		$copy = $defaults;

		// prepare siblings for recursive deeper level
		$new_siblings = array();

		// allow siblings?
		if ( ! empty( $siblings ) && is_array( $siblings ) ) {
			foreach ( $siblings as $sibling ) {
				// highest level siblings
				if ( $sibling === '=>' ) {
					// copy all non-existent string siblings
					foreach( $array as $key => $value ) {
						if ( is_string( $key ) && ! array_key_exists( $key, $defaults ) ) {
							$defaults[$key] = null;
						}
					}
				// sublevel siblings
				} else {
					// explode siblings
					$ex = explode( '=>', $sibling );

					// copy all non-existent siblings
					foreach ( array_keys( $array[$ex[0]] ) as $key ) {
						if ( ! array_key_exists( $key, $defaults[$ex[0]] ) )
							$defaults[$ex[0]][$key] = null;
					}

					// more than one sibling child?
					if ( count( $ex ) > 1 )
						$new_siblings[$ex[0]] = array( substr_replace( $sibling, '', 0, strlen( $ex[0] . '=>' ) ) );
					// no more sibling children
					else
						$new_siblings[$ex[0]] = false;
				}
			}
		}

		// loop through first array
		foreach ( $defaults as $key => $value ) {
			// integer key?
			if ( is_int( $key ) ) {
				$copy = array_unique( array_merge( $defaults, $array ), SORT_REGULAR );

				break;
			// string key?
			} elseif ( is_string( $key ) && isset( $array[$key] ) ) {
				// string, boolean, integer or null values?
				if ( ( is_string( $value ) && is_string( $array[$key] ) ) || ( is_bool( $value ) && is_bool( $array[$key] ) ) || ( is_int( $value ) && is_int( $array[$key] ) ) || is_null( $value ) )
					$copy[$key] = $array[$key];
				// arrays
				elseif ( is_array( $value ) && isset( $array[$key] ) && is_array( $array[$key] ) ) {
					if ( empty( $value ) )
						$copy[$key] = $array[$key];
					else
						$copy[$key] = $this->multi_array_merge( $defaults[$key], $array[$key], ( isset( $new_siblings[$key] ) ? $new_siblings[$key] : false ) );
				}
			}
		}

		return $copy;
	}

	/**
	 * Indicate if current page is the Cookie Policy page
	 *
	 * @return bool
	 */
	public function is_cookie_policy_page() {
		$see_more = $this->options['general']['see_more_opt'];
		
		if ( $see_more['link_type'] !== 'page' )
			return false;

		$cp_id = $see_more['id'];
		$cp_slug = get_post_field( 'post_name', $cp_id );

		$current_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );

		return $current_page->post_name === $cp_slug;
	}

}

/**
 * Initialize Cookie Notice.
 */
function Cookie_Notice() {
	static $instance;

	// first call to instance() initializes the plugin
	if ( $instance === null || ! ( $instance instanceof Cookie_Notice ) )
		$instance = Cookie_Notice::instance();

	return $instance;
}

$cookie_notice = Cookie_Notice();
