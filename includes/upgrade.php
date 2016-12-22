<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

new Cookie_Notice_Upgrade( $cookie_notice );

class Cookie_Notice_Upgrade {

	private $defaults;

	public function __construct( $cookie_notice ) {
		// attributes
		$this->defaults = $cookie_notice->get_defaults();

		// actions
		add_action( 'init', array( $this, 'check_upgrade' ) );
	}

	public function check_upgrade() {
		if ( ! current_user_can( 'manage_options' ) )
			return;

		// gets current database version
		$current_db_version = get_option( 'cookie_notice_version', '1.0.0' );

		// new version?
		if ( version_compare( $current_db_version, $this->defaults['version'], '<' ) ) {
			// updates plugin version
			update_option( 'cookie_notice_version', $this->defaults['version'], false );
		}
	}

}