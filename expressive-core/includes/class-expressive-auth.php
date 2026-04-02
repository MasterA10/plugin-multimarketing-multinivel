<?php

class Expressive_Auth {

	public function register_hooks() {
		add_action( 'wp_login_failed', array( $this, 'handle_login_failed' ) );
		add_filter( 'authenticate', array( $this, 'handle_empty_login' ), 1, 3 );
	}

	/**
	 * Redirect failed login attempts back to custom login page.
	 */
	public function handle_login_failed( $username ) {
		$referrer = wp_get_referer();

		// If login from custom page, redirect back there with failed status
		if ( ! empty( $referrer ) && strpos( $referrer, 'wp-login' ) === false ) {
			wp_safe_redirect( home_url( '/login/?login=failed' ) );
			exit;
		}
	}

	/**
	 * Redirect if username or password is empty.
	 */
	public function handle_empty_login( $user, $username, $password ) {
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		if ( empty( $username ) || empty( $password ) ) {
			if ( strpos( wp_get_referer(), 'wp-login' ) === false ) {
				wp_safe_redirect( home_url( '/login/?login=failed' ) );
				exit;
			}
		}

		return $user;
	}

}
