<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Check if cookies are accepted.
 *
 * @return bool
 */
if ( ! function_exists( 'cn_cookies_accepted' ) ) {
	function cn_cookies_accepted() {
		return (bool) Cookie_Notice::cookies_accepted();
	}
}

/**
 * Check if cookies are set.
 *
 * @return bool
 */
if ( ! function_exists( 'cn_cookies_set' ) ) {
	function cn_cookies_set() {
		return (bool) Cookie_Notice::cookies_set();
	}
}