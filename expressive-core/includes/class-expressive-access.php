<?php

class Expressive_Access {

	/**
	 * Check if a user has an active subscription via WooCommerce Subscriptions.
	 */
	public function has_active_subscription( $user_id = 0 ) {
		if ( ! $user_id && is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		// Check if WooCommerce Subscriptions is active
		if ( function_exists( 'wcs_user_has_subscription' ) ) {
			return wcs_user_has_subscription( $user_id, '', 'active' );
		}

		// Fallback for administrators (always has access)
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Middleware to protect restricted content.
	 */
	public function protect_content_middleware() {
		// Only run on single post pages for our custom types
		if ( ! is_singular( array( 'lms_lesson', 'lms_course', 'lms_live' ) ) ) {
			return;
		}

		$post_type = get_post_type();
		
		// Administrators always pass
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		// 1. Mandatory Login for any restricted content
		if ( ! is_user_logged_in() ) {
			wp_redirect( site_url( '/login/' ) );
			exit;
		}

		// 2. Subscription Check for Lessons and Lives
		if ( in_array( $post_type, array( 'lms_lesson', 'lms_live' ) ) ) {
			if ( ! $this->has_active_subscription() ) {
				// Redirect to area-de-membros which will show the "Upgrade/Join" message
				wp_redirect( site_url( '/area-de-membros/?restricted=1' ) );
				exit;
			}
		}

		// 3. Courses are visible (Showroom), but child lessons check will happen if they try to enter.
	}

}
