<?php
/*
Plugin Name: Cookie Notice
Description: Cookie Notice allows you to elegantly inform users that your site uses cookies and to comply with the EU cookie law regulations.
Version: 1.2.37
Author: dFactory
Author URI: http://www.dfactory.eu/
Plugin URI: http://www.dfactory.eu/plugins/cookie-notice/
License: MIT License
License URI: http://opensource.org/licenses/MIT
Text Domain: cookie-notice
Domain Path: /languages

Cookie Notice
Copyright (C) 2013-2016, Digital Factory - info@digitalfactory.pl

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

// set plugin instance
$cookie_notice = new Cookie_Notice();

include_once( plugin_dir_path( __FILE__ ) . 'includes/upgrade.php' );

/**
 * Cookie Notice class.
 *
 * @class Cookie_Notice
 * @version	1.2.37
 */
class Cookie_Notice {

	/**
	 * @var $defaults
	 */
	private $defaults = array(
		'general' => array(
			'position'						=> 'bottom',
			'message_text'					=> '',
			'css_style'						=> 'bootstrap',
			'accept_text'					=> '',
			'refuse_text'					=> '',
			'refuse_opt'					=> 'no',
			'refuse_code'					=> '',
			'see_more'						=> 'no',
			'link_target'					=> '_blank',
			'time'							=> 'month',
			'hide_effect'					=> 'fade',
			'on_scroll'						=> false,
			'on_scroll_offset'				=> 100,
			'colors' => array(
				'text'							=> '#fff',
				'bar'							=> '#000',
			),
			'see_more_opt' => array(
				'text'						=> '',
				'link_type'					=> 'custom',
				'id'						=> 'empty',
				'link'						=> ''
			),
			'script_placement'				=> 'header',
			'translate'						=> true,
			'deactivation_delete'			=> 'no'
		),
		'version'							=> '1.2.37'
	);
	private $positions 			= array();
	private $styles 			= array();
	private $choices 			= array();
	private $links 				= array();
	private $link_target 		= array();
	private $colors 			= array();
	private $options 			= array();
	private $effects 			= array();
	private $times 				= array();
	private $script_placements 	= array();

	/**
	 * @var $cookie, holds cookie name
	 */
	private static $cookie = array(
		'name'	 => 'cookie_notice_accepted',
		'value'	 => 'TRUE'
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		// settings
		$this->options = array(
			'general' => array_merge( $this->defaults['general'], get_option( 'cookie_notice_options', $this->defaults['general'] ) )
		);

		// actions
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu_options' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'after_setup_theme', array( $this, 'load_defaults' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_load_scripts_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'front_load_scripts_styles' ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'wp_print_footer_scripts' ) );
		add_action( 'wp_footer', array( $this, 'add_cookie_notice' ), 1000 );

		// filters
		add_filter( 'plugin_row_meta', array( $this, 'plugin_extend_links' ), 10, 2 );
		add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
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
			'wp-default' 		=> __( 'WordPress', 'cookie-notice' ),
			'bootstrap'	 		=> __( 'Bootstrap', 'cookie-notice' )
		);

		$this->links = array(
			'custom' 			=> __( 'Custom link', 'cookie-notice' ),
			'page'	 			=> __( 'Page link', 'cookie-notice' )
		);

		$this->link_target = array(
			'_blank',
			'_self'
		);

		$this->colors = array(
			'text'	 			=> __( 'Text color', 'cookie-notice' ),
			'bar'	 			=> __( 'Bar color', 'cookie-notice' ),
		);

		$this->times = array(
			'day'		 		=> array( __( '1 day', 'cookie-notice' ), 86400 ),
			'week'		 		=> array( __( '1 week', 'cookie-notice' ), 604800 ),
			'month'		 		=> array( __( '1 month', 'cookie-notice' ), 2592000 ),
			'3months'	 		=> array( __( '3 months', 'cookie-notice' ), 7862400 ),
			'6months'	 		=> array( __( '6 months', 'cookie-notice' ), 15811200 ),
			'year'		 		=> array( __( '1 year', 'cookie-notice' ), 31536000 ),
			'infinity'	 		=> array( __( 'infinity', 'cookie-notice' ), 31337313373 )
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

		if ( $this->options['general']['translate'] === true ) {
			$this->options['general']['translate'] = false;

			$this->options['general']['message_text'] = __( 'We use cookies to ensure that we give you the best experience on our website. If you continue to use this site we will assume that you are happy with it.', 'cookie-notice' );
			$this->options['general']['accept_text'] = __( 'Ok', 'cookie-notice' );
			$this->options['general']['refuse_text'] = __( 'No', 'cookie-notice' );
			$this->options['general']['see_more_opt']['text'] = __( 'Read more', 'cookie-notice' );

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
			icl_register_string( 'Cookie Notice', 'Read more text', $this->options['general']['see_more_opt']['text'] );
			icl_register_string( 'Cookie Notice', 'Custom link', $this->options['general']['see_more_opt']['link'] );
		}
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
			'Read more text'		=> $this->options['general']['see_more_opt']['text'],
			'Custom link'			=> $this->options['general']['see_more_opt']['link']
		);

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
		add_options_page(
			__( 'Cookie Notice', 'cookie-notice' ), __( 'Cookie Notice', 'cookie-notice' ), apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ), 'cookie-notice', array( $this, 'options_page' )
		);
	}

	/**
	 * Options page output.
	 * 
	 * @return mixed
	 */
	public function options_page() {
		echo '
		<div class="wrap">' . screen_icon() . '
			<h2>' . __( 'Cookie Notice', 'cookie-notice' ) . '</h2>
			<div class="cookie-notice-settings">
				<div class="df-credits">
					<h3 class="hndle">' . __( 'Cookie Notice', 'cookie-notice' ) . ' ' . $this->defaults['version'] . '</h3>
					<div class="inside">
						<h4 class="inner">' . __( 'Need support?', 'cookie-notice' ) . '</h4>
						<p class="inner">' . __( 'If you are having problems with this plugin, please talk about them in the', 'cookie-notice' ) . ' <a href="http://dfactory.eu/support/" target="_blank" title="' . __( 'Support forum', 'cookie-notice' ) . '">' . __( 'Support forum', 'cookie-notice' ) . '</a></p>
						<hr />
						<h4 class="inner">' . __( 'Do you like this plugin?', 'cookie-notice' ) . '</h4>
						<p class="inner"><a href="http://wordpress.org/support/view/plugin-reviews/cookie-notice" target="_blank" title="' . __( 'Rate it 5', 'cookie-notice' ) . '">' . __( 'Rate it 5', 'cookie-notice' ) . '</a> ' . __( 'on WordPress.org', 'cookie-notice' ) . '<br />' .
		__( 'Blog about it & link to the', 'cookie-notice' ) . ' <a href="http://dfactory.eu/plugins/cookie-notice/" target="_blank" title="' . __( 'plugin page', 'cookie-notice' ) . '">' . __( 'plugin page', 'cookie-notice' ) . '</a><br />' .
		__( 'Check out our other', 'cookie-notice' ) . ' <a href="http://dfactory.eu/plugins/" target="_blank" title="' . __( 'WordPress plugins', 'cookie-notice' ) . '">' . __( 'WordPress plugins', 'cookie-notice' ) . '</a>
						</p>    
						<hr />
						<p class="df-link inner">Created by <a href="http://www.dfactory.eu" target="_blank" title="dFactory - Quality plugins for WordPress"><img src="' . plugins_url( '/images/logo-dfactory.png', __FILE__ ) . '" title="dFactory - Quality plugins for WordPress" alt="dFactory - Quality plugins for WordPress" /></a></p>
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
		add_settings_field( 'cn_see_more', __( 'More info link', 'cookie-notice' ), array( $this, 'cn_see_more' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_link_target', __( 'Link target', 'cookie-notice' ), array( $this, 'cn_link_target' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_refuse_opt', __( 'Refuse button', 'cookie-notice' ), array( $this, 'cn_refuse_opt' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_on_scroll', __( 'On scroll', 'cookie-notice' ), array( $this, 'cn_on_scroll' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_time', __( 'Cookie expiry', 'cookie-notice' ), array( $this, 'cn_time' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_script_placement', __( 'Script placement', 'cookie-notice' ), array( $this, 'cn_script_placement' ), 'cookie_notice_options', 'cookie_notice_configuration' );
		add_settings_field( 'cn_deactivation_delete', __( 'Deactivation', 'cookie-notice' ), array( $this, 'cn_deactivation_delete' ), 'cookie_notice_options', 'cookie_notice_configuration' );

		// design
		add_settings_section( 'cookie_notice_design', __( 'Design', 'cookie-notice' ), array( $this, 'cn_section_design' ), 'cookie_notice_options' );
		add_settings_field( 'cn_position', __( 'Position', 'cookie-notice' ), array( $this, 'cn_position' ), 'cookie_notice_options', 'cookie_notice_design' );
		add_settings_field( 'cn_hide_effect', __( 'Animation', 'cookie-notice' ), array( $this, 'cn_hide_effect' ), 'cookie_notice_options', 'cookie_notice_design' );
		add_settings_field( 'cn_css_style', __( 'Button style', 'cookie-notice' ), array( $this, 'cn_css_style' ), 'cookie_notice_options', 'cookie_notice_design' );
		add_settings_field( 'cn_colors', __( 'Colors', 'cookie-notice' ), array( $this, 'cn_colors' ), 'cookie_notice_options', 'cookie_notice_design' );
	}

	/**
	 * Section callback: fix for WP < 3.3
	 */
	public function cn_section_configuration() {}
	public function cn_section_design() {}

	/**
	 * Delete plugin data on deactivation.
	 */
	public function cn_deactivation_delete() {
		echo '
		<label><input id="cn_deactivation_delete" type="checkbox" name="cookie_notice_options[deactivation_delete]" value="1" ' . checked( 'yes', $this->options['general']['deactivation_delete'], false ) . '/>' . __( 'Enable if you want all plugin data to be deleted on deactivation.', 'cookie-notice' ) . '</label>';
	}

	/**
	 * Cookie message option.
	 */
	public function cn_message_text() {
		echo '
		<div id="cn_message_text">
			<textarea name="cookie_notice_options[message_text]" class="large-text" cols="50" rows="5">' . esc_textarea( $this->options['general']['message_text'] ) . '</textarea>
			<p class="description">' . __( 'Enter the cookie notice message.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Accept cookie label option.
	 */
	public function cn_accept_text() {
		echo '
		<div id="cn_accept_text">
			<input type="text" class="regular-text" name="cookie_notice_options[accept_text]" value="' . esc_attr( $this->options['general']['accept_text'] ) . '" />
			<p class="description">' . __( 'The text of the option to accept the usage of the cookies and make the notification disappear.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Enable/Disable third party non functional cookies option.
	 */
	public function cn_refuse_opt() {
		echo '
		<fieldset>
			<label><input id="cn_refuse_opt" type="checkbox" name="cookie_notice_options[refuse_opt]" value="1" ' . checked( 'yes', $this->options['general']['refuse_opt'], false ) . ' />' . __( 'Give to the user the possibility to refuse third party non functional cookies.', 'cookie-notice' ) . '</label>';
		echo '<div id="cn_refuse_opt_container"' . ($this->options['general']['refuse_opt'] === 'no' ? ' style="display: none;"' : '') . '>';
		echo '
				<div id="cn_refuse_text">
					<input type="text" class="regular-text" name="cookie_notice_options[refuse_text]" value="' . esc_attr( $this->options['general']['refuse_text'] ) . '" />
					<p class="description">' . __( 'The text of the option to refuse the usage of the cookies. To get the cookie notice status use <code>cn_cookies_accepted()</code> function.', 'cookie-notice' ) . '</p>
				</div>';
		echo '
				<div id="cn_refuse_code">
					<textarea name="cookie_notice_options[refuse_code]" class="large-text" cols="50" rows="5">' . esc_textarea( $this->options['general']['refuse_code'] ) . '</textarea>
					<p class="description">' . __( 'Enter non functional cookies Javascript code here (for e.g. Google Analitycs). It will be used after cookies are accepted.', 'cookie-notice' ) . '</p>
				</div>';
		echo '
			</div>
		</fieldset>';
	}

	/**
	 * Read more link option.
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
			<label><input id="cn_see_more" type="checkbox" name="cookie_notice_options[see_more]" value="1" ' . checked( 'yes', $this->options['general']['see_more'], false ) . ' />' . __( 'Enable Read more link.', 'cookie-notice' ) . '</label>
			<p class="description">' . sprintf( __( 'Need a Cookie Policy? Generate one with <a href="%s" target="_blank" title="iubenda">iubenda</a>', 'cookie-notice' ), 'http://iubenda.refr.cc/MXRWXMP' ) . '</p>';

		echo '
		<fieldset>
		<div id="cn_see_more_opt"' . ($this->options['general']['see_more'] === 'no' ? ' style="display: none;"' : '') . '>
			<input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][text]" value="' . esc_attr( $this->options['general']['see_more_opt']['text'] ) . '" />
			<p class="description">' . __( 'The text of the more info button.', 'cookie-notice' ) . '</p>
			<div id="cn_see_more_opt_custom_link">';

		if ( $pages ) {
			foreach ( $this->links as $value => $label ) {
				$value = esc_attr( $value );

				echo '
					<label><input id="cn_see_more_link-' . $value . '" type="radio" name="cookie_notice_options[see_more_opt][link_type]" value="' . $value . '" ' . checked( $value, $this->options['general']['see_more_opt']['link_type'], false ) . ' />' . esc_html( $label ) . '</label>';
			}
		}

		echo '
			</div>
			<p class="description">' . __( 'Select where to redirect user for more information about cookies.', 'cookie-notice' ) . '</p>
			<div id="cn_see_more_opt_page"' . ($this->options['general']['see_more_opt']['link_type'] === 'custom' ? ' style="display: none;"' : '') . '>
				<select name="cookie_notice_options[see_more_opt][id]">
					<option value="empty" ' . selected( 'empty', $this->options['general']['see_more_opt']['id'], false ) . '>' . __( '-- select page --', 'cookie-notice' ) . '</option>';

		if ( $pages ) {
			foreach ( $pages as $page ) {
				echo '
						<option value="' . $page->ID . '" ' . selected( $page->ID, $this->options['general']['see_more_opt']['id'], false ) . '>' . esc_html( $page->post_title ) . '</option>';
			}
		}

		echo '
				</select>
				<p class="description">' . __( 'Select from one of your site\'s pages', 'cookie-notice' ) . '</p>
			</div>
			<div id="cn_see_more_opt_link"' . ($this->options['general']['see_more_opt']['link_type'] === 'page' ? ' style="display: none;"' : '') . '>
				<input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][link]" value="' . esc_attr( $this->options['general']['see_more_opt']['link'] ) . '" />
				<p class="description">' . __( 'Enter the full URL starting with http://', 'cookie-notice' ) . '</p>
			</div>
		</div>
		</fieldset>';
	}

	/**
	 * Link target option.
	 */
	public function cn_link_target() {
		echo '
		<div id="cn_link_target">
			<select name="cookie_notice_options[link_target]">';

		foreach ( $this->link_target as $target ) {
			echo '<option value="' . $target . '" ' . selected( $target, $this->options['general']['link_target'] ) . '>' . esc_html( $target ) . '</option>';
		}

		echo '
			</select>
			<p class="description">' . __( 'Select the link target for more info page.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Expiration time option.
	 */
	public function cn_time() {
		echo '
		<div id="cn_time">
			<select name="cookie_notice_options[time]">';

		foreach ( $this->times as $time => $arr ) {
			$time = esc_attr( $time );

			echo '<option value="' . $time . '" ' . selected( $time, $this->options['general']['time'] ) . '>' . esc_html( $arr[0] ) . '</option>';
		}

		echo '
			</select>
			<p class="description">' . __( 'The ammount of time that cookie should be stored for.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Script placement option.
	 */
	public function cn_script_placement() {
		foreach ( $this->script_placements as $value => $label ) {
			echo '
			<label><input id="cn_script_placement-' . $value . '" type="radio" name="cookie_notice_options[script_placement]" value="' . esc_attr( $value ) . '" ' . checked( $value, $this->options['general']['script_placement'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
			<p class="description">' . __( 'Select where all the plugin scripts should be placed.', 'cookie-notice' ) . '</p>';
	}

	/**
	 * Position option.
	 */
	public function cn_position() {
		echo '
		<div id="cn_position">';

		foreach ( $this->positions as $value => $label ) {
			$value = esc_attr( $value );

			echo '
			<label><input id="cn_position-' . $value . '" type="radio" name="cookie_notice_options[position]" value="' . $value . '" ' . checked( $value, $this->options['general']['position'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
			<p class="description">' . __( 'Select location for your cookie notice.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Animation effect option.
	 */
	public function cn_hide_effect() {
		echo '
		<div id="cn_hide_effect">';

		foreach ( $this->effects as $value => $label ) {
			$value = esc_attr( $value );

			echo '
			<label><input id="cn_hide_effect-' . $value . '" type="radio" name="cookie_notice_options[hide_effect]" value="' . $value . '" ' . checked( $value, $this->options['general']['hide_effect'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
			<p class="description">' . __( 'Cookie notice acceptance animation.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * On scroll option.
	 */
	public function cn_on_scroll() {
		echo '
		<fieldset>
			<label><input id="cn_on_scroll" type="checkbox" name="cookie_notice_options[on_scroll]" value="1" ' . checked( 'yes', $this->options['general']['on_scroll'], false ) . ' />' . __( 'Enable cookie notice acceptance when users scroll.', 'cookie-notice' ) . '</label>';
		echo '
			<div id="cn_on_scroll_offset"' . ( $this->options['general']['on_scroll'] === 'no' || $this->options['general']['on_scroll'] == false ? ' style="display: none;"' : '' ) . '>
				<input type="text" class="text" name="cookie_notice_options[on_scroll_offset]" value="' . esc_attr( $this->options['general']['on_scroll_offset'] ) . '" /> <span>px</span>
				<p class="description">' . __( 'Number of pixels user has to scroll to accept the usage of the cookies and make the notification disappear.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * CSS style option.
	 */
	public function cn_css_style() {
		echo '
		<div id="cn_css_style">';

		foreach ( $this->styles as $value => $label ) {
			$value = esc_attr( $value );

			echo '
			<label><input id="cn_css_style-' . $value . '" type="radio" name="cookie_notice_options[css_style]" value="' . $value . '" ' . checked( $value, $this->options['general']['css_style'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
			<p class="description">' . __( 'Choose buttons style.', 'cookie-notice' ) . '</p>
		</div>';
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

		if ( ! check_admin_referer( 'cookie_notice_options-options') )
			return $input;
		
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return $input;

		if ( isset( $_POST['save_cookie_notice_options'] ) ) {

			// position
			$input['position'] = sanitize_text_field( isset( $input['position'] ) && in_array( $input['position'], array_keys( $this->positions ) ) ? $input['position'] : $this->defaults['general']['position'] );

			// colors
			$input['colors']['text'] = sanitize_text_field( isset( $input['colors']['text'] ) && $input['colors']['text'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['text'] ) === 1 ? $input['colors']['text'] : $this->defaults['general']['colors']['text'] );
			$input['colors']['bar'] = sanitize_text_field( isset( $input['colors']['bar'] ) && $input['colors']['bar'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['bar'] ) === 1 ? $input['colors']['bar'] : $this->defaults['general']['colors']['bar'] );

			// texts
			$input['message_text'] = wp_kses_post( isset( $input['message_text'] ) && $input['message_text'] !== '' ? $input['message_text'] : $this->defaults['general']['message_text'] );
			$input['accept_text'] = sanitize_text_field( isset( $input['accept_text'] ) && $input['accept_text'] !== '' ? $input['accept_text'] : $this->defaults['general']['accept_text'] );
			$input['refuse_text'] = sanitize_text_field( isset( $input['refuse_text'] ) && $input['refuse_text'] !== '' ? $input['refuse_text'] : $this->defaults['general']['refuse_text'] );
			$input['refuse_opt'] = (bool) isset( $input['refuse_opt'] ) ? 'yes' : 'no';
			$input['refuse_code'] = wp_kses_post( isset( $input['refuse_code'] ) && $input['refuse_code'] !== '' ? $input['refuse_code'] : $this->defaults['general']['refuse_code'] );

			// css
			$input['css_style'] = sanitize_text_field( isset( $input['css_style'] ) && in_array( $input['css_style'], array_keys( $this->styles ) ) ? $input['css_style'] : $this->defaults['general']['css_style'] );

			// link target
			$input['link_target'] = sanitize_text_field( isset( $input['link_target'] ) && in_array( $input['link_target'], array_keys( $this->link_target ) ) ? $input['link_target'] : $this->defaults['general']['link_target'] );

			// time
			$input['time'] = sanitize_text_field( isset( $input['time'] ) && in_array( $input['time'], array_keys( $this->times ) ) ? $input['time'] : $this->defaults['general']['time'] );

			// script placement
			$input['script_placement'] = sanitize_text_field( isset( $input['script_placement'] ) && in_array( $input['script_placement'], array_keys( $this->script_placements ) ) ? $input['script_placement'] : $this->defaults['general']['script_placement'] );

			// hide effect
			$input['hide_effect'] = sanitize_text_field( isset( $input['hide_effect'] ) && in_array( $input['hide_effect'], array_keys( $this->effects ) ) ? $input['hide_effect'] : $this->defaults['general']['hide_effect'] );
			
			// on scroll
			$input['on_scroll'] = (bool) isset( $input['on_scroll'] ) ? 'yes' : 'no';
			
			// on scroll offset
			$input['on_scroll_offset'] = absint( isset( $input['on_scroll_offset'] ) && $input['on_scroll_offset'] !== '' ? $input['on_scroll_offset'] : $this->defaults['general']['on_scroll_offset'] );

			// deactivation
			$input['deactivation_delete'] = (bool) isset( $input['deactivation_delete'] ) ? 'yes' : 'no';

			// read more
			$input['see_more'] = (bool) isset( $input['see_more'] ) ? 'yes' : 'no';
			$input['see_more_opt']['text'] = sanitize_text_field( isset( $input['see_more_opt']['text'] ) && $input['see_more_opt']['text'] !== '' ? $input['see_more_opt']['text'] : $this->defaults['general']['see_more_opt']['text'] );
			$input['see_more_opt']['link_type'] = sanitize_text_field( isset( $input['see_more_opt']['link_type'] ) && in_array( $input['see_more_opt']['link_type'], array_keys( $this->links ) ) ? $input['see_more_opt']['link_type'] : $this->defaults['general']['see_more_opt']['link_type'] );

			if ( $input['see_more_opt']['link_type'] === 'custom' )
				$input['see_more_opt']['link'] = esc_url( $input['see_more'] === 'yes' ? $input['see_more_opt']['link'] : 'empty' );
			elseif ( $input['see_more_opt']['link_type'] === 'page' )
				$input['see_more_opt']['id'] = ( $input['see_more'] === 'yes' ? (int) $input['see_more_opt']['id'] : 'empty' );

			$input['translate'] = false;

			// WPML >= 3.2
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Message in the notice', $input['message_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Button text', $input['accept_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Refuse button text', $input['refuse_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Read more text', $input['see_more_opt']['text'] );

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
		if ( ! $this->cookie_setted() ) {
			// WPML >= 3.2
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
				$this->options['general']['message_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['message_text'], 'Cookie Notice', 'Message in the notice' );
				$this->options['general']['accept_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['accept_text'], 'Cookie Notice', 'Button text' );
				$this->options['general']['refuse_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['refuse_text'], 'Cookie Notice', 'Refuse button text' );
				$this->options['general']['see_more_opt']['text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['see_more_opt']['text'], 'Cookie Notice', 'Read more text' );
				$this->options['general']['see_more_opt']['link'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['see_more_opt']['link'], 'Cookie Notice', 'Custom link' );
			// WPML and Polylang compatibility
			} elseif ( function_exists( 'icl_t' ) ) {
				$this->options['general']['message_text'] = icl_t( 'Cookie Notice', 'Message in the notice', $this->options['general']['message_text'] );
				$this->options['general']['accept_text'] = icl_t( 'Cookie Notice', 'Button text', $this->options['general']['accept_text'] );
				$this->options['general']['refuse_text'] = icl_t( 'Cookie Notice', 'Refuse button text', $this->options['general']['refuse_text'] );
				$this->options['general']['see_more_opt']['text'] = icl_t( 'Cookie Notice', 'Read more text', $this->options['general']['see_more_opt']['text'] );
				$this->options['general']['see_more_opt']['link'] = icl_t( 'Cookie Notice', 'Custom link', $this->options['general']['see_more_opt']['link'] );
			}

			if ( function_exists( 'icl_object_id' ) )
				$this->options['general']['see_more_opt']['id'] = icl_object_id( $this->options['general']['see_more_opt']['id'], 'page', true );

			// get cookie container args
			$options = apply_filters( 'cn_cookie_notice_args', array(
				'position'			=> $this->options['general']['position'],
				'css_style'			=> $this->options['general']['css_style'],
				'button_class'		=> 'button',
				'colors'			=> $this->options['general']['colors'],
				'message_text'		=> $this->options['general']['message_text'],
				'accept_text'		=> $this->options['general']['accept_text'],
				'refuse_text'		=> $this->options['general']['refuse_text'],
				'refuse_opt'		=> $this->options['general']['refuse_opt'],
				'see_more'			=> $this->options['general']['see_more'],
				'see_more_opt'		=> $this->options['general']['see_more_opt'],
				'link_target'		=> $this->options['general']['link_target'],
			) );

			// message output
			$output = '
			<div id="cookie-notice" role="banner" class="cn-' . ($options['position']) . ($options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '') . '" style="color: ' . $options['colors']['text'] . '; background-color: ' . $options['colors']['bar'] . ';">'
				. '<div class="cookie-notice-container"><span id="cn-notice-text">'. $options['message_text'] .'</span>'
				. '<a href="#" id="cn-accept-cookie" data-cookie-set="accept" class="cn-set-cookie ' . $options['button_class'] . ($options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '') . '">' . $options['accept_text'] . '</a>'
				. ($options['refuse_opt'] === 'yes' ? '<a href="#" id="cn-refuse-cookie" data-cookie-set="refuse" class="cn-set-cookie ' . $options['button_class'] . ($options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '') . '">' . $options['refuse_text'] . '</a>' : '' )
				. ($options['see_more'] === 'yes' ? '<a href="' . ($options['see_more_opt']['link_type'] === 'custom' ? $options['see_more_opt']['link'] : get_permalink( $options['see_more_opt']['id'] )) . '" target="' . $options['link_target'] . '" id="cn-more-info" class="' . $options['button_class'] . ($options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '') . '">' . $options['see_more_opt']['text'] . '</a>' : '') . '
				</div>
			</div>';

			echo apply_filters( 'cn_cookie_notice_output', $output, $options );
		}
	}

	/**
	 * Checks if cookie is setted
	 * 
	 * @return bool
	 */
	public function cookie_setted() {
		return isset( $_COOKIE[self::$cookie['name']] );
	}

	/**
	 * Checks if third party non functional cookies are accepted
	 * 
	 * @return bool
	 */
	public static function cookies_accepted() {
		return ( isset( $_COOKIE[self::$cookie['name']] ) && strtoupper( $_COOKIE[self::$cookie['name']] ) === self::$cookie['value'] );
	}

	/**
	 * Get default settings.
	 */
	public function get_defaults() {
		return $this->defaults;
	}
	
	/**
	 * Add links to Support Forum.
	 * 
	 * @param array $links
	 * @param string $file
	 * @return array
	 */
	public function plugin_extend_links( $links, $file ) {
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return $links;

		$plugin = plugin_basename( __FILE__ );

		if ( $file == $plugin )
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
	public function plugin_settings_link( $links, $file ) {
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return $links;

		$plugin = plugin_basename( __FILE__ );

		if ( $file == $plugin )
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
		if ( $this->options['general']['deactivation_delete'] === 'yes' )
			delete_option( 'cookie_notice_options' );
	}

	/**
	 * Load scripts and styles - admin.
	 */
	public function admin_load_scripts_styles( $page ) {
		if ( $page !== 'settings_page_cookie-notice' )
			return;

		wp_enqueue_script(
			'cookie-notice-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), $this->defaults['version']
		);
		
		wp_localize_script(
			'cookie-notice-admin', 'cnArgs', array(
				'resetToDefaults'	=> __( 'Are you sure you want to reset these settings to defaults?', 'cookie-notice' )
			)
		);

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'cookie-notice-admin', plugins_url( 'css/admin.css', __FILE__ ) );
	}

	/**
	 * Load scripts and styles - frontend.
	 */
	public function front_load_scripts_styles() {
		if ( ! $this->cookie_setted() ) {
			wp_enqueue_script(
				'cookie-notice-front', plugins_url( 'js/front.js', __FILE__ ), array( 'jquery' ), $this->defaults['version'], isset( $this->options['general']['script_placement'] ) && $this->options['general']['script_placement'] === 'footer' ? true : false
			);

			wp_localize_script(
				'cookie-notice-front', 'cnArgs', array(
					'ajaxurl'				=> admin_url( 'admin-ajax.php' ),
					'hideEffect'			=> $this->options['general']['hide_effect'],
					'onScroll'				=> $this->options['general']['on_scroll'],
					'onScrollOffset'		=> $this->options['general']['on_scroll_offset'],
					'cookieName'			=> self::$cookie['name'],
					'cookieValue'			=> self::$cookie['value'],
					'cookieTime'			=> $this->times[$this->options['general']['time']][1],
					'cookiePath'			=> ( defined( 'COOKIEPATH' ) ? COOKIEPATH : '' ),
					'cookieDomain'			=> ( defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '' )
				)
			);

			wp_enqueue_style( 'cookie-notice-front', plugins_url( 'css/front.css', __FILE__ ) );
		}
	}
	
	/**
	 * Print non functional javascript.
	 * 
	 * @return mixed
	 */
	public function wp_print_footer_scripts() {
		$scripts = html_entity_decode( trim( wp_kses_post( $this->options['general']['refuse_code'] ) ) );
		
		if ( $this->cookie_setted() && ! empty( $scripts ) ) {
			?>
			<script type='text/javascript'>
				<?php echo $scripts; ?>
			</script>
			<?php
		}
	}
	
}

/**
 * Get the cookie notice status
 * 
 * @return boolean
 */
function cn_cookies_accepted() {
	return (bool) Cookie_Notice::cookies_accepted();
}
